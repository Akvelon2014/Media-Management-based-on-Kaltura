<?php
/**
 * @package Scheduler
 * @subpackage Conversion
 */

/**
 * Will close almost done conversions that sent to remote systems and store the files in the file system.
 * The state machine of the job is as follows:
 * 	 	get almost done conversions 
 * 		check the convert status
 * 		download the converted file
 * 		save recovery file in case of crash
 * 		move the file to the archive
 *
 * @package Scheduler
 * @subpackage Conversion
 */
class KAsyncConvertCloser extends KJobCloserWorker
{
	private $localTempPath;
	private $sharedTempPath;

	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::CONVERT;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		return $this->closeConvert($job, $job->data);
	}
	
	public function __construct($taskConfig = null)
	{
		parent::__construct($taskConfig);
		
		// creates a temp file path
		$this->localTempPath = $this->taskConfig->params->localTempPath;
		$this->sharedTempPath = $this->taskConfig->params->sharedTempPath;
		
	}
	
	public function run($jobs = null)
	{
		$res = self::createDir( $this->localTempPath );
		if ( !$res ) 
		{
			KalturaLog::err( "Cannot continue conversion without temp local directory");
			return null;
		}
		
		$res = self::createDir( $this->sharedTempPath );
		if ( !$res ) 
		{
			KalturaLog::err( "Cannot continue conversion without temp shared directory");
			return null;
		}
		
		return parent::run($jobs);
	}
	
	private function closeConvert(KalturaBatchJob $job, KalturaConvertJobData $data)
	{
		KalturaLog::debug("fetchStatus($job->id)");
		
		if(($job->queueTime + $this->taskConfig->params->maxTimeBeforeFail) < time())
			return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::CLOSER_TIMEOUT, 'Timed out', KalturaBatchJobStatus::FAILED);
		
		if($job->jobSubType == KalturaConversionEngineType::ENCODING_COM)
		{
			$parseEngine = new KParseEngineEncodingCom($this->taskConfig);
			$errMessage = null;
			$status = $parseEngine->parse($data, $errMessage);
					
			if($errMessage == $job->message)
				$errMessage = null;
				
			$log = $parseEngine->getLogData();
			//removing unsuported XML chars 
			$log  = preg_replace('/[^\t\n\r\x{20}-\x{d7ff}\x{e000}-\x{fffd}\x{10000}-\x{10ffff}]/u','',$log);
			if($log && strlen($log))
			{
				try
				{
					$this->kClient->batch->logConversion($data->flavorAssetId, $log);
				}
				catch(Exception $e)
				{
					KalturaLog::err("Log conversion: " . $e->getMessage());
				}
			}
				
			if($status == KalturaBatchJobStatus::FINISHED)
			{
				$updateData = new KalturaConvertJobData();
				$updateData->destFileSyncRemoteUrl = $data->destFileSyncRemoteUrl;
				$this->updateJob($job, $errMessage, KalturaBatchJobStatus::ALMOST_DONE, 90, $updateData);
			}
			else
			{
				return $this->closeJob($job, null, null, $errMessage, $status);
			}
		}
	
		if($job->executionAttempts > 1) // is a retry
		{
			if(strlen($data->destFileSyncLocalPath) && file_exists($data->destFileSyncLocalPath))
			{
				return $this->moveFile($job, $data);
			}
		}
		
		// creates a temp file path
		$uniqid = uniqid('convert_');
		$data->destFileSyncLocalPath = "{$this->localTempPath}/$uniqid";
	
		$err = null;
		if(!$this->fetchFile($data->destFileSyncRemoteUrl, $data->destFileSyncLocalPath, $err))
		{
			return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::REMOTE_DOWNLOAD_FAILED, $err, KalturaBatchJobStatus::ALMOST_DONE);
		}
		$this->fetchFile($data->logFileSyncRemoteUrl, $data->logFileSyncLocalPath);
		
		return $this->moveFile($job, $data);
	}
	
	private function moveFile(KalturaBatchJob $job, KalturaConvertJobData $data)
	{
		KalturaLog::debug("moveFile($job->id, $data->destFileSyncLocalPath)");
		
		$uniqid = uniqid('convert_');
		$sharedFile = "{$this->sharedTempPath}/$uniqid";
		
		try
		{
			rename($data->logFileSyncLocalPath, "$sharedFile.log");
		}
		catch(Exception $ex)
		{
			KalturaLog::debug("move log file error: " . $ex->getMessage());
		}
		
		clearstatcache();
		$fileSize = kFile::fileSize($data->destFileSyncLocalPath);
		rename($data->destFileSyncLocalPath, $sharedFile);
		if(!file_exists($sharedFile) || kFile::fileSize($sharedFile) != $fileSize)
		{
			KalturaLog::err("Error: moving file failed");
			die();
		}
		
		@chmod($sharedFile, 0777);
		@chmod("$sharedFile.log", 0777);
		$data->destFileSyncLocalPath = $sharedFile;
		$data->logFileSyncLocalPath = "$sharedFile.log";
		
		if($this->checkFileExists($sharedFile, $fileSize))
		{
			$job->status = KalturaBatchJobStatus::FINISHED;
			$job->message = "File moved to shared";
		}
		else
		{
			$job->status = KalturaBatchJobStatus::ALMOST_DONE; // retry
			$job->message = "File not moved correctly";
		}
		$updateData = new KalturaConvertJobData();
		$updateData->destFileSyncLocalPath = $data->destFileSyncLocalPath;
		$updateData->logFileSyncLocalPath = $data->logFileSyncLocalPath;
		return $this->closeJob($job, null, null, $job->message, $job->status, $updateData);
	}
	
	/**
	 * @param string $srcFileSyncRemoteUrl
	 * @param string $srcFileSyncLocalPath
	 * @param string $errDescription
	 * @return string
	 */
	private function fetchFile($srcFileSyncRemoteUrl, $srcFileSyncLocalPath, &$errDescription = null)
	{
		KalturaLog::debug("fetchFile($srcFileSyncRemoteUrl, $srcFileSyncLocalPath)");
		
		try
		{
			$curlWrapper = new KCurlWrapper($srcFileSyncRemoteUrl);
			$curlHeaderResponse = $curlWrapper->getHeader(true);
			if(!$curlHeaderResponse || $curlWrapper->getError())
			{
				$errDescription = "Error: " . $curlWrapper->getError();
				return false;
			}
			
			if($curlHeaderResponse->code != KCurlHeaderResponse::HTTP_STATUS_OK)
			{
				$errDescription = "HTTP Error: " . $curlHeaderResponse->code . " " . $curlHeaderResponse->codeName;
				return false;
			}
			$fileSize = null;
			if(isset($curlHeaderResponse->headers['content-length']))
				$fileSize = $curlHeaderResponse->headers['content-length'];
			$curlWrapper->close();
				
			KalturaLog::debug("Executing curl");
			$curlWrapper = new KCurlWrapper($srcFileSyncRemoteUrl);
			$res = $curlWrapper->exec($srcFileSyncLocalPath);
			KalturaLog::debug("Curl results: $res");
		
			if(!$res || $curlWrapper->getError())
			{
				$errDescription = "Error: " . $curlWrapper->getError();
				$curlWrapper->close();
				return false;
			}
			$curlWrapper->close();
			
			if(!file_exists($srcFileSyncLocalPath))
			{
				$errDescription = "Error: output file doesn't exist";
				return false;
			}
				
			if($fileSize)
			{
				clearstatcache();
				if(kFile::fileSize($srcFileSyncLocalPath) != $fileSize)
				{
					$errDescription = "Error: output file have a wrong size";
					return false;
				}
			}
		}
		catch(Exception $ex)
		{
			$errDescription = "Error: " . $ex->getMessage();
			return false;
		}
		
		return true;
	}
}
