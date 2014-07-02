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
 * @subpackage Post-Convert
 */

/**
 * Will convert a single flavor and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	get the flavor 
 * 		convert using the right method
 * 		save recovery file in case of crash
 * 		move the file to the archive
 * 		set the entry's new status and file details 
 *
 * 
 * @package Scheduler
 * @subpackage Post-Convert
 */
class KAsyncPostConvert extends KJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::POSTCONVERT;
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
		return $this->postConvert($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}

	protected function checkMediaInfoExists($flavorAssetId) {
		$mediaInfo = mediaInfoPeer::retrieveByFlavorAssetId($flavorAssetId);
		return !empty($mediaInfo);
	}

	/**
	 * @param KalturaBatchJob $job
	 * @param KalturaPostConvertJobData $data
	 * @return KalturaBatchJob
	 */
	private function postConvert(KalturaBatchJob $job, KalturaPostConvertJobData $data)
	{
		DbManager::setConfig(kConf::getDB());
		DbManager::initialize();

		$flavorAsset = assetPeer::retrieveByPK($data->flavorAssetId);
		if (!empty($flavorAsset) && $this->checkMediaInfoExists($data->flavorAssetId) && $flavorAsset->getIsOriginal() && ($flavorAsset->getSize() !== 0)) {
			$data->createThumb = false;
			return $this->closeJob($job, null, null, 'Media info already exists', KalturaBatchJobStatus::FINISHED, $data);
		}

		if($data->flavorParamsOutputId)
			$data->flavorParamsOutput = $this->kClient->flavorParamsOutput->get($data->flavorParamsOutputId);
		
		try
		{
			$mediaFile = trim($data->srcFileSyncLocalPath);
			$mediaFileExt = pathinfo($mediaFile, PATHINFO_EXTENSION);

			if (!is_null($data->srcFileSyncWamsAssetId)) {
				$tempFile = kWAMS::getInstance($job->partnerId)->createTempFileForAssetId($data->srcFileSyncWamsAssetId, $mediaFileExt);
				if (file_exists($tempFile)) {
					$mediaFile = $tempFile;
				}
			}
			
			if(!$data->flavorParamsOutput || !$data->flavorParamsOutput->sourceRemoteStorageProfileId)
			{
				if(!$this->pollingFileExists($mediaFile))
					return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, "Source file $mediaFile does not exist", KalturaBatchJobStatus::RETRY);
				
				if(!is_file($mediaFile))
					return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, "Source file $mediaFile is not a file", KalturaBatchJobStatus::FAILED);
			}
			
			KalturaLog::debug("mediaFile [$mediaFile]");
			$this->updateJob($job,"Extracting file media info on $mediaFile", KalturaBatchJobStatus::QUEUED, 1);
		}
		catch(Exception $ex)
		{
			return $this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), KalturaBatchJobStatus::FAILED);
		}
		
		$mediaInfo = null;
		try
		{
			$engine = KBaseMediaParser::getParser($job->jobSubType, realpath($mediaFile), $this->taskConfig, $job, $data->srcFileSyncWamsAssetId);

			if($engine)
			{
				KalturaLog::info("Media info engine [" . get_class($engine) . "]");
				$mediaInfo = $engine->getMediaInfo();
				if (!empty($data->srcFileSyncWamsAssetId)) {
					kWAMS::getInstance($job->partnerId)->deleteTempFileForAssetId($data->srcFileSyncWamsAssetId, $mediaFileExt);
				}
			}
			else
			{
				$err = "Media info engine not found for job subtype [".$job->jobSubType."]";
				KalturaLog::info($err);
				return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::ENGINE_NOT_FOUND, $err, KalturaBatchJobStatus::FAILED);
			}
		}
		catch(Exception $ex)
		{
			KalturaLog::err("Error: " . $ex->getMessage());
			$mediaInfo = null;
		}
		
		/* @var $mediaInfo KalturaMediaInfo */
		if(is_null($mediaInfo))
			return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::EXTRACT_MEDIA_FAILED, "Failed to extract media info: $mediaFile", KalturaBatchJobStatus::FAILED);
		
		try
		{
			$mediaInfo->flavorAssetId = $data->flavorAssetId;
			$createdMediaInfo = $this->getClient()->batch->addMediaInfo($mediaInfo);

			if ($createdMediaInfo instanceof KalturaMediaInfo) {
				$mediaInfo = $createdMediaInfo;
			}

			// must save the mediaInfoId before reporting that the task is finished
			$this->updateJob($job, "Saving media info id $createdMediaInfo->id", KalturaBatchJobStatus::PROCESSED, 50, $data);
			
			$data->thumbPath = null;
			if(!$data->createThumb)
				return $this->closeJob($job, null, null, "Media info id $createdMediaInfo->id saved", KalturaBatchJobStatus::FINISHED, $data);
			
			// creates a temp file path 
			$rootPath = $this->taskConfig->params->localTempPath;
			$this->createDir($rootPath);
				
			// creates the path
			$uniqid = uniqid('thumb_');
			$thumbPath = realpath($rootPath) . "/$uniqid";

			$videoDurationSec = floor($mediaInfo->videoDuration / 1000);
			$data->thumbOffset = max(0 ,min($data->thumbOffset, $videoDurationSec));
			
			if($mediaInfo->videoHeight)
				$data->thumbHeight = $mediaInfo->videoHeight;
			
			if($mediaInfo->videoBitRate)
				$data->thumbBitrate = $mediaInfo->videoBitRate;
					
			// generates the thumbnail
			if (!is_null($data->srcFileSyncWamsAssetId)) {
				$thumbMaker = new KWAMSThumbnailMaker($data->srcFileSyncWamsAssetId, $thumbPath);
				$created = $thumbMaker->createThumbnail($data->thumbOffset, $mediaInfo->videoWidth, $mediaInfo->videoHeight, $mediaInfo->videoDar);
			}
			else {
				$thumbMaker = new KFFMpegThumbnailMaker($mediaFile, $thumbPath, $this->taskConfig->params->FFMpegCmd);
				$created = $thumbMaker->createThumnail($data->thumbOffset, $mediaInfo->videoWidth, $mediaInfo->videoHeight, null, null, $mediaInfo->videoDar);
			}

			if(!$created || !file_exists($thumbPath))
			{
				$data->createThumb = false;
				return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::THUMBNAIL_NOT_CREATED, 'Thumbnail not created', KalturaBatchJobStatus::FINISHED, $data);
			}
			$data->thumbPath = $thumbPath;
			
			$job = $this->moveFile($job, $data);
			
			if($this->checkFileExists($job->data->thumbPath))
				return $this->closeJob($job, null, null, null, KalturaBatchJobStatus::FINISHED, $data);
			
			$data->createThumb = false;
			return $this->closeJob($job, KalturaBatchJobErrorTypes::APP, KalturaBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'File not moved correctly', KalturaBatchJobStatus::FINISHED, $data);
		}
		catch(Exception $ex)
		{
			return $this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), KalturaBatchJobStatus::FAILED);
		}
	}
	
	/**
	 * @param KalturaBatchJob $job
	 * @param KalturaPostConvertJobData $data
	 * @return KalturaBatchJob
	 */
	private function moveFile(KalturaBatchJob $job, KalturaPostConvertJobData $data)
	{
		KalturaLog::debug("moveFile($job->id, $data->thumbPath)");
		
		// creates a temp file path 
		$rootPath = $this->taskConfig->params->sharedTempPath;
		if(! is_dir($rootPath))
		{
			if(! file_exists($rootPath))
			{
				KalturaLog::info("Creating temp thumbnail directory [$rootPath]");
				mkdir($rootPath);
			}
			else
			{
				// already exists but not a directory 
				$err = "Cannot create temp thumbnail directory [$rootPath] due to an error. Please fix and restart";
				throw new Exception($err, -1);
			}
		}
		
		$uniqid = uniqid('thumb_');
		$sharedFile = realpath($rootPath) . "/$uniqid";
		
		clearstatcache();
		$fileSize = kFile::fileSize($data->thumbPath);
		rename($data->thumbPath, $sharedFile);
		if(!file_exists($sharedFile) || kFile::fileSize($sharedFile) != $fileSize)
		{
			$err = 'moving file failed';
			throw new Exception($err, -1);
		}
		
		@chmod($sharedFile, 0777);
		$data->thumbPath = $sharedFile;
		$job->data = $data;
		return $job;
	}
}
