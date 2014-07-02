<?php
/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Modified by Akvelon Inc.
* 2014-06-30
* http://www.akvelon.com/contact-us
*/

/**
 * @package Scheduler
 * @subpackage Import
 */

/**
 * Will import a single URL and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	parse URL	(youTube is a special case) 
 * 		fetch heraders (to calculate the size of the file)
 * 		fetch file (update the job's progress - 100% is when the whole file as appeared in the header)
 * 		move the file to the archive
 * 		set the entry's new status and file details  (check if FLV) 
 *
 * @package Scheduler
 * @subpackage Import
 */
class KAsyncImport extends KJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::IMPORT;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		return $this->fetchFile($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	/*
	 * Will take a single KalturaBatchJob and fetch the URL to the job's destFile 
	 */
	private function fetchFile(KalturaBatchJob $job, KalturaImportJobData $data)
	{
		KalturaLog::debug("fetchFile($job->id)");
		
		$jobSubType = $job->jobSubType;
		if (in_array($jobSubType, array(kFileTransferMgrType::SCP, kFileTransferMgrType::SFTP)))
		{
		    // use SSH file transfer manager for SFTP/SCP
            return $this->fetchFileSsh($job, $data);
		}
		
		try
		{
			$sourceUrl = $data->srcFileUrl;
			KalturaLog::debug("sourceUrl [$sourceUrl]");
			
			$this->updateJob($job, 'Downloading file header', KalturaBatchJobStatus::QUEUED, 1);
			$fileSize = null;
			$resumeOffset = 0;
			if ($data->destFileLocalPath && file_exists($data->destFileLocalPath) )
			{ 
    			$curlWrapper = new KCurlWrapper($sourceUrl, $this->taskConfig->params->curlVerbose);
    			$useNoBody = ($job->executionAttempts > 1); // if the process crashed first time, tries with no body instead of range 0-0
    			$curlHeaderResponse = $curlWrapper->getHeader($useNoBody);
    			if(!$curlHeaderResponse || !count($curlHeaderResponse->headers))
    			{
    				$this->closeJob($job, KalturaBatchJobErrorTypes::CURL, $curlWrapper->getErrorNumber(), "Error: " . $curlWrapper->getError(), KalturaBatchJobStatus::FAILED);
    				return $job;
    			}
    			
    			if($curlWrapper->getError())
    			{
    				KalturaLog::err("Headers error: " . $curlWrapper->getError());
    				KalturaLog::err("Headers error number: " . $curlWrapper->getErrorNumber());
    				$curlWrapper->close();
    				
    				$curlWrapper = new KCurlWrapper($sourceUrl, $this->taskConfig->params->curlVerbose);
    			}
    			
    			if(!$curlHeaderResponse->isGoodCode())
    			{
    				$this->closeJob($job, KalturaBatchJobErrorTypes::HTTP, $curlHeaderResponse->code, "HTTP Error: " . $curlHeaderResponse->code . " " . $curlHeaderResponse->codeName, KalturaBatchJobStatus::FAILED);
    				return $job;
    			}
    			
    			if(isset($curlHeaderResponse->headers['content-length']))
    				$fileSize = $curlHeaderResponse->headers['content-length'];
    			$curlWrapper->close();
    			
    			if( $fileSize )
    			{
    				clearstatcache();
    				$actualFileSize = kFile::fileSize($data->destFileLocalPath);
    				if($actualFileSize >= $fileSize)
    				{
    					return $this->moveFile($job, $data->destFileLocalPath, $fileSize);
    				}
    				else
    				{
    					$resumeOffset = $actualFileSize;
    				}
    			}
			}
			$curlWrapper = new KCurlWrapper($sourceUrl, $this->taskConfig->params->curlVerbose);
			$curlWrapper->setTimeout($this->taskConfig->params->curlTimeout);			
				
			if($resumeOffset)
			{
				$curlWrapper->setResumeOffset($resumeOffset);
			}
			else
			{
				// creates a temp file path
				$destFile = $this->getTempFilePath($sourceUrl);			
				KalturaLog::debug("destFile [$destFile]");
				$data->destFileLocalPath = $destFile;
				$this->updateJob($job, "Downloading file, size: $fileSize", KalturaBatchJobStatus::PROCESSING, 2, $data);
			}
			
			KalturaLog::debug("Executing curl");
			$res = $curlWrapper->exec($data->destFileLocalPath);
			KalturaLog::debug("Curl results: $res");
		
			if(!$res || $curlWrapper->getError())
			{
				$errNumber = $curlWrapper->getErrorNumber();
				if($errNumber != CURLE_OPERATION_TIMEOUTED)
				{
					$this->closeJob($job, KalturaBatchJobErrorTypes::CURL, $errNumber, "Error: " . $curlWrapper->getError(), KalturaBatchJobStatus::RETRY);
					$curlWrapper->close();
					return $job;
				}
				else
				{
					clearstatcache();
					$actualFileSize = kFile::fileSize($data->destFileLocalPath);
					if($actualFileSize == $resumeOffset)
					{
						$this->closeJob($job, KalturaBatchJobErrorTypes::CURL, $errNumber, "Error: " . $curlWrapper->getError(), KalturaBatchJobStatus::RETRY);
						$curlWrapper->close();
						return $job;
					}
				}
			}
			$curlWrapper->close();
			
			if(!file_exists($data->destFileLocalPath))
			{
				$this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::OUTPUT_FILE_DOESNT_EXIST, "Error: output file doesn't exist", KalturaBatchJobStatus::RETRY);
				return $job;
			}
				
			// check the file size only if its first or second retry
			// in case it failed few times, taks the file as is
			if($fileSize)
			{
				clearstatcache();
				$actualFileSize = kFile::fileSize($data->destFileLocalPath);
				if($actualFileSize < $fileSize)
				{
					$percent = floor($actualFileSize * 100 / $fileSize);
					$this->updateJob($job, "Downloaded size: $actualFileSize($percent%)", KalturaBatchJobStatus::PROCESSING, $percent, $data);
					$this->kClient->batch->resetJobExecutionAttempts($job->id, $this->getExclusiveLockKey(), $job->jobType);
//					$this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::OUTPUT_FILE_WRONG_SIZE, "Expected file size[$fileSize] actual file size[$actualFileSize]", KalturaBatchJobStatus::RETRY);
					return $job;
				}
			}
			
			
			$this->updateJob($job, 'File imported, copy to shared folder', KalturaBatchJobStatus::PROCESSED, 90);
			
			$job = $this->moveFile($job, $data->destFileLocalPath, $fileSize);
		}
		catch(Exception $ex)
		{
			$this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), KalturaBatchJobStatus::FAILED);
		}
		return $job;
	}
	
	
	/*
	 * Will take a single KalturaBatchJob and fetch the URL to the job's destFile 
	 */
	private function fetchFileSsh(KalturaBatchJob $job, KalturaSshImportJobData $data)
	{
		KalturaLog::debug("fetchFile($job->id)");
		
		try
		{
			$sourceUrl = $data->srcFileUrl;
			KalturaLog::debug("sourceUrl [$sourceUrl]");
			
            // extract information from URL and job data
			$parsedUrl = parse_url($sourceUrl);
			
			$host = isset($parsedUrl['host']) ? $parsedUrl['host'] : null;
			$remotePath = isset($parsedUrl['path']) ? $parsedUrl['path'] : null;
			$username = isset($parsedUrl['user']) ? $parsedUrl['user'] : null;
			$password = isset($parsedUrl['pass']) ? $parsedUrl['pass'] : null;
			
			$privateKey = isset($data->privateKey) ? $data->privateKey : null;
			$publicKey  = isset($data->publicKey) ? $data->publicKey : null;
			$passPhrase = isset($data->passPhrase) ? $data->passPhrase : null;
			
			KalturaLog::debug("host [$host] remotePath [$remotePath] username [$username] password [$password]");
			if ($privateKey || $publicKey) {
			    KalturaLog::debug("Private Key: $privateKey");
			    KalturaLog::debug("Public Key: $publicKey");
			}
			
			if (!$host) {
			    $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::MISSING_PARAMETERS, 'Error: missing host', KalturaBatchJobStatus::FAILED);
			    return $job;
			}
			if (!$remotePath) {
			    $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::MISSING_PARAMETERS, 'Error: missing host', KalturaBatchJobStatus::FAILED);
			    return $job;
			}
			
			// create suitable file transfer manager object
			$subType = $job->jobSubType;
			$fileTransferMgr = kFileTransferMgr::getInstance($subType);
			
			if (!$fileTransferMgr) {
			    $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::ENGINE_NOT_FOUND, "Error: file transfer manager not found for type [$subType]", KalturaBatchJobStatus::FAILED);
			    return $job;
			}
			
			// login to server
			if (!$privateKey || !$publicKey) {
			    $fileTransferMgr->login($host, $username, $password);
			}
			else {
			    $privateKeyFile = $this->getFileLocationForSshKey($privateKey, 'privateKey');
			    $publicKeyFile = $this->getFileLocationForSshKey($publicKey, 'publicKey');
			    $fileTransferMgr->loginPubKey($host, $username, $publicKeyFile, $privateKeyFile, $passPhrase);
			}
			
			// check if file exists
			$fileExists = $fileTransferMgr->fileExists($remotePath);
			if (!$fileExists) {
			    $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::MISSING_PARAMETERS, "Error: remote file [$remotePath] does not exist", KalturaBatchJobStatus::FAILED);
			    return $job;
			}
			
			// get file size
			$fileSize = $fileTransferMgr->fileSize($remotePath);
			
            // create a temp file path 				
			$destFile = $this->getTempFilePath($remotePath);				
			$data->destFileLocalPath = $destFile;
			KalturaLog::debug("destFile [$destFile]");
			
			// download file - overwrite local if exists
			$this->updateJob($job, "Downloading file, size: $fileSize", KalturaBatchJobStatus::PROCESSING, 2, $data);
			KalturaLog::debug("Downloading remote file [$remotePath] to local path [$destFile]");
			$res = $fileTransferMgr->getFile($remotePath, $destFile);
			
			if(!file_exists($data->destFileLocalPath))
			{
				$this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::OUTPUT_FILE_DOESNT_EXIST, "Error: output file doesn't exist", KalturaBatchJobStatus::RETRY);
				return $job;
			}
				
			// check the file size only if its first or second retry
			// in case it failed few times, taks the file as is
			if($fileSize)
			{
				clearstatcache();
				$actualFileSize = kFile::fileSize($data->destFileLocalPath);
				if($actualFileSize < $fileSize)
				{
					$percent = floor($actualFileSize * 100 / $fileSize);
					$job = $this->updateJob($job, "Downloaded size: $actualFileSize($percent%)", KalturaBatchJobStatus::PROCESSING, $percent, $data);
					$this->kClient->batch->resetJobExecutionAttempts($job->id, $this->getExclusiveLockKey(), $job->jobType);
					return $job;
				}
			}
			
			$this->updateJob($job, 'File imported, copy to shared folder', KalturaBatchJobStatus::PROCESSED, 90);
			
			$job = $this->moveFile($job, $data->destFileLocalPath, $fileSize);
		}
		catch(Exception $ex)
		{
			$this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), KalturaBatchJobStatus::FAILED);
		}
		return $job;
	}
	
	/**
	 * @param KalturaBatchJob $job
	 * @param string $destFile
	 * @param int $fileSize
	 * @return KalturaBatchJob
	 */
	private function moveFile(KalturaBatchJob $job, $destFile, $fileSize = null)
	{
		KalturaLog::debug("moveFile($job->id, $destFile, $fileSize)");
		
		try
		{
			// creates a shared file path 
			$rootPath = $this->taskConfig->params->sharedTempPath;
			
			$res = self::createDir( $rootPath );
			if ( !$res ) 
			{
				KalturaLog::err( "Cannot continue import without shared directory");
				die(); 
			}
			$uniqid = uniqid('import_');
			$sharedFile = realpath($rootPath) . "/$uniqid";
			
			$ext = pathinfo($destFile, PATHINFO_EXTENSION);
			if(strlen($ext))
				$sharedFile .= ".$ext";
			
			KalturaLog::debug("rename('$destFile', '$sharedFile')");
			rename($destFile, $sharedFile);
			if(!file_exists($sharedFile))
			{
				KalturaLog::err("Error: renamed file doesn't exist");
				die();
			}
				
			clearstatcache();
			if($fileSize)
			{
				if(kFile::fileSize($sharedFile) != $fileSize)
				{
					KalturaLog::err("Error: renamed file have a wrong size");
					die();
				}
			}
			else
			{
				$fileSize = kFile::fileSize($sharedFile);
			}
			
			@chmod($sharedFile, 0777);
			
			$data = $job->data;
			$data->destFileLocalPath = $sharedFile;
			
			if($this->checkFileExists($sharedFile, $fileSize))
			{
				$this->closeJob($job, null, null, 'Succesfully moved file', KalturaBatchJobStatus::FINISHED, $data);
			}
			else
			{
				$this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'File not moved correctly', KalturaBatchJobStatus::RETRY);
			}
		}
		catch(Exception $ex)
		{
			$this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), KalturaBatchJobStatus::FAILED);
		}
		return $job;
	}
	
	/*
	 * Lazy saving of the key to a temporary path, the key will exist in this location until the temp files are purged 
	 */
	protected function getFileLocationForSshKey($keyContent, $prefix = 'key') 
	{
		$tempDirectory = sys_get_temp_dir();
		$fileLocation = tempnam($tempDirectory, $prefix);		
		file_put_contents($fileLocation, $keyContent);
		return $fileLocation;
	}
	
	
	protected function getTempFilePath($remotePath)
	{
		// create a temp file path
		$origRemotePath = $remotePath;
		$rootPath = $this->taskConfig->params->localTempPath;
			
		$res = self::createDir( $rootPath );
		if ( !$res ) 
		{
			KalturaLog::err( "Cannot continue import without temp directory");
			die(); 
		}
			
		$uniqid = uniqid('import_');
		$destFile = realpath($rootPath) . "/$uniqid";
		
		// in case the url has added arguments, remove them (and reveal the real URL path)
		// in order to find the file extension
		$urlPathEndIndex = strpos($remotePath, "?");
		if ($urlPathEndIndex !== false)
			$remotePath = substr($remotePath, 0, $urlPathEndIndex);
			
		$ext = pathinfo($remotePath, PATHINFO_EXTENSION);

		$ext = strtolower($ext);
		if (!in_array($ext, kWAMS::getSupportedFormats())) {
			$remoteExt = kWAMS::getFileExtFromURL($origRemotePath);
			if (!empty($remoteExt)) {
				$ext = $remoteExt;
			}
		}

		if (strlen($ext)) {
			$destFile .= ".$ext";
		}

		return $destFile;
	}
}
?>