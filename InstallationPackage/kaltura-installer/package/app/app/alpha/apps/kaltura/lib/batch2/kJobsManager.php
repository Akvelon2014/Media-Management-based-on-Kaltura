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
 * 
 * Manages the jobs add, get status and abort
 * 
 * @package Core
 * @subpackage Batch
 *
 */
class kJobsManager
{
	
	// helper function for setting the error description and status of a batchJob
	public static function failBatchJob(BatchJob $batchJob, $errDescription)
	{
		$batchJob->setMessage($errDescription);
		$description = $batchJob->getDescription() . "\n$errDescription";
		$batchJob->setDescription($description);
		return self::updateBatchJob($batchJob, BatchJob::BATCHJOB_STATUS_FAILED);
	}
	
	/**
	 * @param BatchJob $batchJob
	 * @param int $status
	 * @param BatchJob $twinJob
	 * @return BatchJob
	 */
	public static function updateBatchJob(BatchJob $batchJob, $status, BatchJob $twinJob = null)
	{
		$batchJob->setStatus($status);
		$changed = $batchJob->save();
		
		if(!$changed)
			return $batchJob;
		
		$event = new kBatchJobStatusEvent($batchJob, $twinJob);
		kEventsManager::raiseEvent($event);
		$batchJob->reload();
		return $batchJob;
	}
	
	public static function abortEntryJobs($entryId)
	{
		$dbBatchJobs = BatchJobPeer::retrieveByEntryId($entryId);
		
		foreach($dbBatchJobs as $dbBatchJob)
			self::abortDbBatchJob($dbBatchJob);
	}
	
	public static function abortJob($jobId, $jobType, $force = false)
	{
		$dbBatchJob = BatchJobPeer::retrieveByPK($jobId);
		if($dbBatchJob->getJobType() != $jobType)
			throw new APIException(APIErrors::GET_EXCLUSIVE_JOB_WRONG_TYPE, $jobType, $dbBatchJob->getId());
			
		return self::abortDbBatchJob($dbBatchJob, $force);
	}
	
	public static function deleteJob($jobId, $jobType)
	{
		$dbBatchJob = BatchJobPeer::retrieveByPK($jobId);
		if($dbBatchJob->getJobType() != $jobType)
			throw new APIException(APIErrors::GET_EXCLUSIVE_JOB_WRONG_TYPE, $jobType, $dbBatchJob->getId());
			
		$dbBatchJob->setDeletedAt(time());
		$dbBatchJob->save();
		
		return $dbBatchJob;
	}
	
	public static function abortDbBatchJob(BatchJob $dbBatchJob, $force = false)
	{
		if(!$force && in_array($dbBatchJob->getStatus(), BatchJobPeer::getClosedStatusList()))
			return $dbBatchJob;
			
		$dbBatchJob->setAbort(1); // 1 = true
		
		// if not currently locked
		if(!$dbBatchJob->getSchedulerId())
		{
			$dbBatchJob = self::updateBatchJob($dbBatchJob, BatchJob::BATCHJOB_STATUS_ABORTED);
		}
		else
		{
			$dbBatchJob->save();
		}
		
		// aborts all child jobs
		self::abortChildJobs($dbBatchJob);
			
		return $dbBatchJob;
	}
	
	/**
	 * @param BatchJob $dbBatchJob
	 */
	public static function abortChildJobs(BatchJob $dbBatchJob)
	{
		// aborts all child jobs
		$dbChildJobs = $dbBatchJob->getChildJobs();
		foreach($dbChildJobs as $dbChildJob)
			if($dbChildJob->getId() != $dbBatchJob->getId())
				self::abortDbBatchJob($dbChildJob);
	}
	
	/**
	 * @param int $jobId
	 * @param int $jobType
	 * @param bool $force - forces retry even if the job is locked.
	 * @return BatchJob
	 */
	public static function retryJob($jobId, $jobType, $force = false)
	{
		$dbBatchJob = BatchJobPeer::retrieveByPK($jobId);
		if($dbBatchJob->getJobType() != $jobType)
			throw new APIException(APIErrors::GET_EXCLUSIVE_JOB_WRONG_TYPE, $jobType, $dbBatchJob->getId());
			
		$dbBatchJob->setAbort(false);
		
		// if not currently locked
		if(!$dbBatchJob->getSchedulerId())
		{
			$dbBatchJob->setExecutionAttempts(0);
			$dbBatchJob = self::updateBatchJob($dbBatchJob, BatchJob::BATCHJOB_STATUS_RETRY);
		}
		elseif($force)
		{
			$dbBatchJob->setExecutionAttempts(0);
			$dbBatchJob->setStatus(BatchJob::BATCHJOB_STATUS_RETRY);
			$dbBatchJob->setStatus(BatchJob::BATCHJOB_STATUS_RETRY);
			$dbBatchJob->setCheckAgainTimeout(time() + BatchJobPeer::getCheckAgainTimeout($jobType));
			$dbBatchJob->save();
		}
			
		return $dbBatchJob;
	}
	
	public static function addMailJob(BatchJob $parentJob = null, $entryId, $partnerId, $mailType, $mailPriority, $fromEmail, $fromName, $toEmail, array $bodyParams = array(), array $subjectParams = array(), $toName = null, $toId = null, $camaignId = null, $templatePath = null)
	{
	  	$jobData = new kMailJobData();
		$jobData->setMailPriority($mailPriority);
	 	$jobData->setMailType($mailType);
	 	
	 	$jobData->setFromEmail($fromEmail);
	 	$jobData->setFromName($fromName);
	 	
	 	$jobData->setBodyParamsArray($bodyParams);
		$jobData->setSubjectParamsArray($subjectParams);
		
		$jobData->setRecipientEmail($toEmail);
		$jobData->setRecipientName($toName);
		$jobData->setRecipientId($toId);
		
		$jobData->setCampaignId($camaignId);		
		$jobData->setCampaignId($camaignId);
	 	$jobData->setTemplatePath($templatePath);
	
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($partnerId);
		}
		return self::addJob($batchJob, $jobData, BatchJobType::MAIL, $mailType);
	}
	
	public static function addProvisionDeleteJob(BatchJob $parentJob = null, entry $entry)
	{
 		$jobData = new kProvisionJobData();
 		$jobData->setStreamID($entry->getStreamRemoteId());
 		
 		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entry->getId());
			$batchJob->setPartnerId($entry->getPartnerId());
		}
		
		$subType = $entry->getSource();
		return self::addJob($batchJob, $jobData, BatchJobType::PROVISION_DELETE, $subType);
	}
	
	public static function addProvisionProvideJob(BatchJob $parentJob = null, entry $entry)
	{
		$subType = $entry->getSource();
		if ($subType == entry::ENTRY_MEDIA_SOURCE_AKAMAI_LIVE)
		{
			$partner = $entry->getPartner();
			if (!is_null($partner))
			{
				$jobData = new kAkamaiProvisionJobData();
				$akamaiLiveParams = $partner->getAkamaiLiveParams();
				if ($akamaiLiveParams)
				{
					$jobData->setWsdlUsername($akamaiLiveParams->getAkamaiLiveWsdlUsername());
					$jobData->setWsdlPassword($akamaiLiveParams->getAkamaiLiveWsdlPassword());
					$jobData->setCpcode($akamaiLiveParams->getAkamaiLiveCpcode());
					$jobData->setEmailId($akamaiLiveParams->getAkamaiLiveEmailId());
					$jobData->setPrimaryContact($akamaiLiveParams->getAkamaiLivePrimaryContact());
					$jobData->setSecondaryContact($akamaiLiveParams->getAkamaiLiveSecondaryContact());		
				}		
			}
		}
		else
		{
			$jobData = new kProvisionJobData();
		}
 		$jobData->setEncoderIP($entry->getEncodingIP1());
 		$jobData->setBackupEncoderIP($entry->getEncodingIP2());
 		$jobData->setEncoderPassword($entry->getStreamPassword());
 		$jobData->setEncoderUsername($entry->getStreamUsername());
 		$jobData->setEndDate($entry->getEndDate(null));
 		$jobData->setMediaType($entry->getMediaType()); 		
 		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entry->getId());
			$batchJob->setPartnerId($entry->getPartnerId());
		}
				
		return self::addJob($batchJob, $jobData, BatchJobType::PROVISION_PROVIDE, $subType);
	}

	/**
	 * addConvertIsmCollectionJob creates a convert collection job 
	 * 
	 * @param string $tag 
	 * @param FileSyncKey $srcSyncKey
	 * @param entry $entry
	 * @param BatchJob $parentJob
	 * @param array<flavorParamsOutput> $flavorParamsOutputs
	 * @return BatchJob
	 */
	public static function addConvertIsmCollectionJob($tag, FileSyncKey $srcSyncKey, entry $entry, BatchJob $parentJob = null, array $flavorParamsOutputs, $dbConvertCollectionJob = null)
	{		
		list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
		
		$localPath = null;
		$remoteUrl = null;
		if($fileSync)
		{
			if($fileSync->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_URL)			
				$localPath = $fileSync->getFullPath();
			$remoteUrl = $fileSync->getExternalUrl($entry->getId());
		}
		
		// increment entry version
		$ismVersion = $entry->incrementIsmVersion();
		$entry->save();
		
		$fileName = $entry->generateFileName(0, $ismVersion);
		// creates convert data
		$convertCollectionData = new kConvertCollectionJobData();
		$convertCollectionData->setSrcFileSyncLocalPath($localPath);
		$convertCollectionData->setSrcFileSyncRemoteUrl($remoteUrl);
		$convertCollectionData->setDestFileName($fileName);
		
		$clipOffset = null;
		$clipDuration = null;
		
		// look for clipping params
		foreach($flavorParamsOutputs as $flavorParamsOutput){
			$clipOffset = $flavorParamsOutput->getClipOffset();
			$clipDuration = $flavorParamsOutput->getClipDuration();
			if(isset($clipOffset) || isset($clipDuration)){
				KalturaLog::log("Found clipping params: clipOffset($clipOffset),clipDuration($clipDuration)");
				break;
			}
		}

		$bitrates = array();
		$finalFlavorParamsOutputs = array();
	
		// check bitrates duplications & update clipping params
		foreach($flavorParamsOutputs as $flavorParamsOutputIndex => $flavorParamsOutput)
		{
			if(!isset($bitrates[$flavorParamsOutput->getVideoBitrate()]))
				$bitrates[$flavorParamsOutput->getVideoBitrate()] = array();

			// if one of clip params exsits - update the object and db
			if(isset($clipOffset)){
				$flavorParamsOutputs[$flavorParamsOutputIndex]->setClipOffset($clipOffset);
			}
			if(isset($clipDuration)){
				$flavorParamsOutputs[$flavorParamsOutputIndex]->setClipDuration($clipDuration);
			}
			if(isset($clipOffset) || isset($clipDuration)){
				$flavorParamsOutputs[$flavorParamsOutputIndex]->save();
			}
			$bitrates[$flavorParamsOutput->getVideoBitrate()][] = $flavorParamsOutput->getId();
			$finalFlavorParamsOutputs[$flavorParamsOutput->getId()] = $flavorParamsOutput;
		}
		
		foreach($bitrates as $bitrate => $flavorParamsOutputIds)
		{
			if(count($flavorParamsOutputIds) == 1) // no bitrate dupliaction
				continue;
				
			$tempFlavorParamsOutputs = array();
			foreach($flavorParamsOutputIds as $index => $flavorParamsOutputId)
				$tempFlavorParamsOutputs[] = $finalFlavorParamsOutputs[$flavorParamsOutputId];
				
			// sort the flavors by height
			usort($tempFlavorParamsOutputs, array('kBusinessConvertDL', 'compareFlavorsByHeight'));
				
			// increment the bitrate so it will be a bit different for each flavor
			$index = 0;
			foreach($tempFlavorParamsOutputs as $flavorParamsOutput)
				$finalFlavorParamsOutputs[$flavorParamsOutput->getId()]->setVideoBitrate($bitrate + ($index++));
		}
		
		foreach($finalFlavorParamsOutputs as $flavorParamsOutput)
		{
			$convertCollectionFlavorData = new kConvertCollectionFlavorData();
			$convertCollectionFlavorData->setFlavorAssetId($flavorParamsOutput->getFlavorAssetId());
			$convertCollectionFlavorData->setFlavorParamsOutputId($flavorParamsOutput->getId());
			$convertCollectionFlavorData->setReadyBehavior($flavorParamsOutput->getReadyBehavior());
			$convertCollectionFlavorData->setVideoBitrate($flavorParamsOutput->getVideoBitrate());
			$convertCollectionFlavorData->setAudioBitrate($flavorParamsOutput->getAudioBitrate());
			$convertCollectionFlavorData->setAudioBitrate($flavorParamsOutput->getAudioBitrate());
			
			$convertCollectionData->addFlavor($convertCollectionFlavorData);
		}
		
		$currentConversionEngine = conversionEngineType::EXPRESSION_ENCODER3;
		KalturaLog::log("Using conversion engine [$currentConversionEngine]");
		
		if(!$dbConvertCollectionJob)
		{
			// creats a child convert job
			if($parentJob)
			{
				$dbConvertCollectionJob = $parentJob->createChild();
				KalturaLog::log("Created from parent convert job with entry id [" . $dbConvertCollectionJob->getEntryId() . "]");
			}
			else
			{
				$dbConvertCollectionJob = new BatchJob();
				$dbConvertCollectionJob->setEntryId($entry->getId());
				$dbConvertCollectionJob->setPartnerId($entry->getPartnerId());
				$dbConvertCollectionJob->save();
				KalturaLog::log("Created from convert collection job with entry id [" . $dbConvertCollectionJob->getEntryId() . "]");
			}
		}
		
		KalturaLog::log("Calling CDLProceessFlavorsForCollection with [" . count($finalFlavorParamsOutputs) . "] flavor params");
		$xml = KDLWrap::CDLProceessFlavorsForCollection($finalFlavorParamsOutputs);
		$xml = str_replace(KDLCmdlinePlaceholders::OutFileName, $fileName, $xml);
		
		$syncKey = $dbConvertCollectionJob->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_CONFIG);
		kFileSyncUtils::file_put_contents($syncKey, $xml);
		
		$fileSync = kFileSyncUtils::getLocalFileSyncForKey($syncKey);
		$remoteUrl = $fileSync->getExternalUrl($entry->getId());
		$localPath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
		
		$commandLines = array(
			conversionEngineType::EXPRESSION_ENCODER3 => KDLCmdlinePlaceholders::InFileName . ' ' . KDLCmdlinePlaceholders::ConfigFileName,
		);
		$commandLinesStr = flavorParamsOutput::buildCommandLinesStr($commandLines);
		
		$convertCollectionData->setInputXmlLocalPath($localPath);
		$convertCollectionData->setInputXmlRemoteUrl($remoteUrl);
		$convertCollectionData->setCommandLinesStr($commandLinesStr);
		
		$dbConvertCollectionJob->setFileSize(kFile::fileSize($convertCollectionData->getSrcFileSyncLocalPath()));
		
		return kJobsManager::addJob($dbConvertCollectionJob, $convertCollectionData, BatchJobType::CONVERT_COLLECTION, $currentConversionEngine);
	}
	
	
	/**
	 * addFlavorConvertJob adds a single flavor conversion 
	 * 
	 * @param FileSyncKey $srcSyncKey
	 * @param flavorParamsOutput $flavor
	 * @param int $flavorAssetId
	 * @param int $mediaInfoId
	 * @param BatchJob $parentJob
	 * @param int $lastEngineType  
	 * @param BatchJob $dbConvertFlavorJob
	 * @return BatchJob 
	 */
	public static function addFlavorConvertJob(FileSyncKey $srcSyncKey, flavorParamsOutput $flavor, $flavorAssetId, $mediaInfoId = null, BatchJob $parentJob = null, $lastEngineType = null, BatchJob $dbConvertFlavorJob = null)
	{
		$localPath = null;
		$remoteUrl = null;
		
		$flavorAsset = assetPeer::retrieveById($flavorAssetId);
		if(!$flavorAsset)
		{
			KalturaLog::err("No flavor asset found for id [$flavorAssetId]");
			return null;
		}
		
		if($flavor->getSourceRemoteStorageProfileId() == StorageProfile::STORAGE_KALTURA_DC)
		{
			list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
			
			$partner = PartnerPeer::retrieveByPK($flavorAsset->getPartnerId());
			
			if(!$fileSync)
			{
				kBatchManager::updateEntry($flavorAsset->getEntryId(), entryStatus::ERROR_CONVERTING);
				
				$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
				$flavorAsset->setDescription("Source file sync not found: $srcSyncKey");
				$flavorAsset->save();
				
				KalturaLog::err("Source file sync not found: $srcSyncKey");
				return null;
			}
			
			if(!$local)
			{
				if($fileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL && $partner && $partner->getImportRemoteSourceForConvert())
				{
					KalturaLog::debug("Creates import job for remote file sync");
					
					$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_WAIT_FOR_CONVERT);
					$flavorAsset->setDescription("Source file sync is importing: $srcSyncKey");
					$flavorAsset->save();
					
					$originalFlavorAsset = assetPeer::retrieveOriginalByEntryId($flavorAsset->getEntryId());
					$url = $fileSync->getExternalUrl($flavorAsset->getEntryId());
					return kJobsManager::addImportJob($parentJob, $flavorAsset->getEntryId(), $partner->getId(), $url, $originalFlavorAsset, null, null, true);
				}
				
				throw new kCoreException("Source file not found for flavor conversion [$flavorAssetId]", kCoreException::SOURCE_FILE_NOT_FOUND);
			}
			
			if($fileSync->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_URL)			
				$localPath = $fileSync->getFullPath();
			$remoteUrl = $fileSync->getExternalUrl($flavorAsset->getEntryId());
		}
		else
		{
			$fileSync = kFileSyncUtils::getReadyExternalFileSyncForKey($srcSyncKey, $flavor->getSourceRemoteStorageProfileId());
			if(!$fileSync)
			{
				kBatchManager::updateEntry($flavorAsset->getEntryId(), entryStatus::ERROR_CONVERTING);
				
				$description = "Remote source file sync not found $srcSyncKey, storage profile id [" . $flavor->getSourceRemoteStorageProfileId() . "]";
				KalturaLog::err($description);
				
				$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
				$flavorAsset->setDescription($description);
				$flavorAsset->save();

				return null;
			}
			
			$localPath = $fileSync->getFilePath();
			$remoteUrl = $fileSync->getExternalUrl($flavorAsset->getEntryId());
		}

		$wamsAssetId = $fileSync->getWamsAssetId();

		// creates convert data
		$convertData = new kConvertJobData();
		$convertData->setSrcFileSyncLocalPath($localPath);
		$convertData->setSrcFileSyncRemoteUrl($remoteUrl);
		$convertData->setMediaInfoId($mediaInfoId);
		$convertData->setFlavorParamsOutputId($flavor->getId());
		$convertData->setFlavorAssetId($flavorAssetId);
		$convertData->setSrcFileSyncWamsAssetId($wamsAssetId);
		
		KalturaLog::log("Conversion engines string: '" . $flavor->getConversionEngines() . "'");
		
		$currentConversionEngine = null;
		
		// TODO remove after all old version flavors migrated
		// parse supported engine types
		$conversionEngines = array();
		if(!$flavor->getEngineVersion()) // uses the old engine version
		{
			$conversionEngines = explode(',', $flavor->getConversionEngines());
			KalturaLog::log(count($conversionEngines) . " conversion engines found for the flavor");
			$currentConversionEngine = reset($conversionEngines); // gets the first engine type
		}
		// remove until here
		
		
		if(is_null($lastEngineType))
		{
			KalturaLog::log("Last Engine Type is null, engine version [" . $flavor->getEngineVersion() . "]");
			if($flavor->getEngineVersion()) // uses the new engine version
			{
				$operatorSet = new kOperatorSets();
				$operatorSet->setSerialized(/*stripslashes*/($flavor->getOperators()));
				$nextOperator = $operatorSet->getOperator();
				if(!$nextOperator)
				{
					KalturaLog::err("First operator is invalid");
					return null;
				}
				
				KalturaLog::log("Set first operator in first set");
				$currentConversionEngine = $nextOperator->id;
			}
		}
		else
		{
			if(
				$parentJob && 
				$flavor->getEngineVersion() &&
				(
					$parentJob->getJobType() == BatchJobType::CONVERT
					||
					$parentJob->getJobType() == BatchJobType::POSTCONVERT
				)
			) // uses the new engine version
			{
				// using next oprator
				KalturaLog::log("Adding next conversion operator");
				
				$parentData = $parentJob->getData();
				if(!$parentData || !($parentData instanceof kConvartableJobData))
				{
					KalturaLog::err("Parent job data is invalid");
					return null;
				}
				
				$operatorSet = new kOperatorSets();
				$operatorSet->setSerialized(/*stripslashes*/($flavor->getOperators()));
				$nextOperatorSet = $parentData->getCurrentOperationSet();
				$nextOperatorIndex = $parentData->getCurrentOperationIndex() + 1;
				$nextOperator = $operatorSet->getOperator($nextOperatorSet, $nextOperatorIndex);
				if(!$nextOperator)
				{
					KalturaLog::err("Next operator is invalid");
					return null;
				}
				
				KalturaLog::log("Moving to next operator [$nextOperatorIndex] in set [$nextOperatorSet]");
				$convertData->setCurrentOperationSet($nextOperatorSet);
				$convertData->setCurrentOperationIndex($nextOperatorIndex);
				
				$currentConversionEngine = $nextOperator->id;
			}
			else
			{
				// TODO remove after all old version flavors migrated
				
				KalturaLog::log("Last used conversion engine is [$lastEngineType]");
				// searching for $lastEngineType in the list
				while($lastEngineType != $currentConversionEngine && next($conversionEngines))
					$currentConversionEngine = current($conversionEngines);
					
				// takes the next engine
				$currentConversionEngine = next($conversionEngines);
				if(! $currentConversionEngine)
				{
					KalturaLog::err("There is no other conversion engine to use");
					return null;
				}
			}
		}

		if (!is_null($fileSync->getWamsAssetId())) {
			$currentConversionEngine = conversionEngineType::WAMS;
		}

		KalturaLog::log("Using conversion engine [$currentConversionEngine]");
		
		// creats a child convert job
		if(is_null($dbConvertFlavorJob))
		{
			if($parentJob)
			{
				$dbConvertFlavorJob = $parentJob->createChild();
				KalturaLog::log("Created from parent convert job with entry id [" . $dbConvertFlavorJob->getEntryId() . "]");
			}
			else
			{
				$dbConvertFlavorJob = new BatchJob();
				$dbConvertFlavorJob->setEntryId($flavor->getEntryId());
				$dbConvertFlavorJob->setPartnerId($flavor->getPartnerId());
				$dbConvertFlavorJob->save();
				KalturaLog::log("Created from flavor convert job with entry id [" . $dbConvertFlavorJob->getEntryId() . "]");
			}
		}
		if (!is_null($fileSync->getWamsAssetId())) {
			$srcFileSize = kWAMS::getInstance($flavor->getPartnerId())->getFileSizeForAssetId($fileSync->getWamsAssetId());
		}else {
			$srcFileSize = kFile::fileSize($convertData->getSrcFileSyncLocalPath());
		}
		$dbConvertFlavorJob->setFileSize($srcFileSize);

		// TODO remove after all old version flavors migrated
		if(in_array(conversionEngineType::ENCODING_COM, $conversionEngines))
			$dbConvertFlavorJob->setOnStressDivertTo(conversionEngineType::ENCODING_COM);
		// remove until here
		
		/*
			// Remarked by Dor until Tantan's return.
			// Code is supposed to get a configuration file from the engine and attach it to the batch job.
			// Was added for document conversion and is not used for now because of a bug of PDFCreator.

		KalturaLog::log("Calling CDLProceessFlavor with flavor params output[" . $flavor->getId() . "]");
		$config = KDLWrap::CDLProceessFlavor($flavor);
		if($config)
		{
			$syncKey = $dbConvertFlavorJob->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_CONFIG);
			kFileSyncUtils::file_put_contents($syncKey, $config);
			
			$fileSync = kFileSyncUtils::getLocalFileSyncForKey($syncKey);
			$remoteUrl = $fileSync->getExternalUrl($flavor->getEntryId());
			$localPath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
			
			$convertData->setConfigLocalPath($localPath);
			$convertData->setConfigRemoteUrl($remoteUrl);
		}
		*/
		$dbCurrentConversionEngine = kPluginableEnumsManager::apiToCore('conversionEngineType', $currentConversionEngine);
		return kJobsManager::addJob($dbConvertFlavorJob, $convertData, BatchJobType::CONVERT, $dbCurrentConversionEngine);
	}
	
	
	/**
	 * @param BatchJob $parentJob
	 * @param int $partnerId
	 * @param string $entryId
	 * @param string $thumbAssetId
	 * @param FileSyncKey $srcSyncKey
	 * @param string $srcAssetId
	 * @param int $srcAssetType enum of assetType
	 * @param thumbParamsOutput $thumbParams
	 * @return BatchJob
	 */
	public static function addCapturaThumbJob(BatchJob $parentJob = null, $partnerId, $entryId, $thumbAssetId, FileSyncKey $srcSyncKey, $srcAssetId, $srcAssetType, thumbParamsOutput $thumbParams = null)
	{
		$thumbAsset = assetPeer::retrieveById($thumbAssetId);
		if(!$thumbAsset)
		{
			KalturaLog::err("No thumbnail asset found for id [$thumbAssetId]");
			return null;
		}
		
		$partner = PartnerPeer::retrieveByPK($thumbAsset->getPartnerId());
		
		list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, true, false);
		if(!$fileSync)
		{
			$thumbAsset->setStatus(asset::ASSET_STATUS_ERROR);
			$thumbAsset->setDescription("Source file sync not found: $srcSyncKey");
			$thumbAsset->save();
			
			KalturaLog::err("Source file sync not found: $srcSyncKey");
			return null;
		}
		
		if(!$local)
		{
			if($fileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL && $partner && $partner->getImportRemoteSourceForConvert())
			{
				$url = $fileSync->getExternalUrl($entryId);
				$originalAsset = kFileSyncUtils::retrieveObjectForSyncKey($srcSyncKey);
				if($originalAsset instanceof flavorAsset)
				{
					KalturaLog::debug("Creates import job for remote file sync [$url]");
					
					if($thumbParams)
					{
						$thumbParams->setSourceParamsId($originalAsset->getFlavorParamsId());
						$thumbParams->save();
					}
					
					$thumbAsset->setStatus(asset::ASSET_STATUS_WAIT_FOR_CONVERT);
					$thumbAsset->setDescription("Source file sync is importing: $srcSyncKey");
					$thumbAsset->save();
					
					return kJobsManager::addImportJob($parentJob, $thumbAsset->getEntryId(), $partner->getId(), $url, $originalAsset, null, null, true);
				}
				
				KalturaLog::debug("Downloading remote file sync [$url]");
				$downloadPath = myContentStorage::getFSUploadsPath() . '/' . $thumbAsset->getId() . '.jpg';
				if (kFile::downloadUrlToFile($url, $downloadPath))
				{
					kFileSyncUtils::moveFromFile($downloadPath, $srcSyncKey);
					list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($srcSyncKey, false, false);
					if(!$fileSync)
						throw new kCoreException("Source file not found for thumbnail capture [$thumbAssetId]", kCoreException::SOURCE_FILE_NOT_FOUND);
				}
			}
			else
			{
				throw new kCoreException("Source file not found for thumbnail capture [$thumbAssetId]", kCoreException::SOURCE_FILE_NOT_FOUND);
			}
		}
		$localPath = $fileSync->getFullPath();
		$remoteUrl = $fileSync->getExternalUrl($entryId);
		$srcWamsAssetId = $fileSync->getWamsAssetId();
		
		// creates convert data
		$data = new kCaptureThumbJobData();
		$data->setThumbAssetId($thumbAssetId);
		$data->setSrcAssetId($srcAssetId);
		$data->setSrcAssetType($srcAssetType);
		$data->setSrcFileSyncLocalPath($localPath);
		$data->setSrcFileSyncRemoteUrl($remoteUrl);
		$data->setThumbParamsOutputId($thumbParams->getId());
		$data->setSrcWamsAssetId($srcWamsAssetId);
	
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($partnerId);
		}
		
		return kJobsManager::addJob($batchJob, $data, BatchJobType::CAPTURE_THUMB);
	}
	
	/**
	 * @param BatchJob $parentJob
	 * @param int $postConvertAssetType
	 * @param string $srcFileSyncLocalPath
	 * @param int $flavorAssetId
	 * @param int $flavorParamsOutputId
	 * @param bool $createThumb
	 * @param int $thumbOffset
	 * @param string $customData
	 * @return BatchJob
	 */
	public static function addPostConvertJob(BatchJob $parentJob = null, $postConvertAssetType, $srcFileSyncLocalPath, $flavorAssetId, $flavorParamsOutputId, $createThumb = false, $thumbOffset = 3, $customData=null, $srcFileSyncWamsAssetId = null)
	{
		$postConvertData = new kPostConvertJobData();
		$postConvertData->setPostConvertAssetType($postConvertAssetType);
		$postConvertData->setSrcFileSyncLocalPath($srcFileSyncLocalPath);
		$postConvertData->setFlavorParamsOutputId($flavorParamsOutputId);
		$postConvertData->setFlavorAssetId($flavorAssetId);
		$postConvertData->setThumbOffset($thumbOffset);
		$postConvertData->setCreateThumb($createThumb);
		$postConvertData->setSrcFileSyncWamsAssetId($srcFileSyncWamsAssetId);

		if(isset($customData)) $postConvertData->setCustomData($customData);
		
		if($parentJob)
		{
			$parentData = $parentJob->getData();
			if($parentData instanceof kConvartableJobData)
			{
				$postConvertData->setCurrentOperationSet($parentData->getCurrentOperationSet());
				$postConvertData->setCurrentOperationIndex($parentData->getCurrentOperationIndex());
			}
		}
		
		$flavorAsset = assetPeer::retrieveById($flavorAssetId);
		if($createThumb)
		{
			$flavorParamsOutput = assetParamsOutputPeer::retrieveByPK($flavorParamsOutputId);
			if(!$flavorParamsOutput)
			{
				if($flavorAsset)
				{
					$postConvertData->setThumbHeight($flavorAsset->getHeight());
					$postConvertData->setThumbBitrate($flavorAsset->getBitrate());
				}
				else
				{
					$postConvertData->setCreateThumb(false);
				}
			}
			elseif(!$flavorParamsOutput->getVideoBitrate()) // audio only
			{
				$postConvertData->setCreateThumb(false);
			}
			elseif($flavorParamsOutput->getSourceRemoteStorageProfileId() != StorageProfile::STORAGE_KALTURA_DC)
			{
				$postConvertData->setCreateThumb(false);
			}
			elseif($flavorAsset)
			{
				$entry = $flavorAsset->getentry();
				if($entry)
				{
					$thisFlavorHeight = $flavorParamsOutput->getHeight();
					$thisFlavorBitrate = $flavorParamsOutput->getVideoBitrate();
					
					$createThumb = false;
					if($entry->getThumbBitrate() < $thisFlavorBitrate)
					{
						$createThumb = true;
					}
					elseif($entry->getThumbBitrate() == $thisFlavorBitrate && $entry->getThumbHeight() < $thisFlavorHeight)
					{
						$createThumb = true;
					}
					
					if($createThumb)
					{
						$postConvertData->setCreateThumb(true);
						$postConvertData->setThumbHeight($thisFlavorHeight);
						$postConvertData->setThumbBitrate($thisFlavorBitrate);
					}
				}
			}
		}
	
		$batchJob = null;
		if($parentJob)
		{
			//Job will be created with parent job as his root job
			$useSameRoot = true;
			if($parentJob->getJobType() == BatchJobType::CONVERT_PROFILE)
				$useSameRoot = false;
				
			$batchJob = $parentJob->createChild($useSameRoot); 
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($flavorAsset->getEntryId());
			$batchJob->setPartnerId($flavorAsset->getPartnerId());
		}
		
		KalturaLog::log("Post Convert created with file: " . $postConvertData->getSrcFileSyncLocalPath());
		
		$mediaParserType = ($flavorParamsOutput ? $flavorParamsOutput->getMediaParserType() : mediaParserType::MEDIAINFO);
		return kJobsManager::addJob($batchJob, $postConvertData, BatchJobType::POSTCONVERT, $mediaParserType);
	}
	
	public static function addImportJob(BatchJob $parentJob = null, $entryId, $partnerId, $entryUrl, asset $asset = null, $subType = null, kImportJobData $jobData = null, $keepCurrentVersion = false)
	{
		$entryUrl = str_replace('//', '/', $entryUrl);
		$entryUrl = preg_replace('/^((https?)|(ftp)|(scp)|(sftp)):\//', '$1://', $entryUrl);
		
		if (is_null($subType)) {
    		if (stripos($entryUrl, 'sftp:') === 0) {
    		    $subType = kFileTransferMgrType::SFTP;
    		}
    	    if (stripos($entryUrl, 'scp:') === 0) {
    		    $subType = kFileTransferMgrType::SCP;
    		}
		}
		
		if (!$jobData) {
 		    $jobData = new kImportJobData();
		}
 		$jobData->setSrcFileUrl($entryUrl);
 		
 		if($asset)
 		{
 			if($keepCurrentVersion)
 			{
 				if(!$asset->isLocalReadyStatus())
	 				$asset->setStatus(asset::FLAVOR_ASSET_STATUS_IMPORTING);
 			}
 			else 
 			{
 				$asset->incrementVersion();
	 			$asset->setStatus(asset::FLAVOR_ASSET_STATUS_IMPORTING);
 			}
	 		$asset->save();
 			
 			$jobData->setFlavorAssetId($asset->getId());
 		}
 			
 		$entry = entryPeer::retrieveByPK($entryId);
 		if($entry)
 		{
 			$higherStatuses = array(
 				entryStatus::PRECONVERT,
 				entryStatus::READY,
 			);
 			
 			if(!in_array($entry->getStatus(), $higherStatuses))
 			{
	 			$entry->setStatus(entryStatus::IMPORT);
	 			$entry->save();
 			}
 		}
 		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($partnerId);
		}
		return self::addJob($batchJob, $jobData, BatchJobType::IMPORT, $subType);
	}
	
	/**
	 * @param int $partnerId
	 * @param int $objectType of enum IndexObjectType
	 * @param baseObjectFilter $filter The filter should return the list of objects that need to be reindexed
	 * @param bool $shouldUpdate Indicates that the object columns and attributes values should be recalculated before reindexed
	 * @param array $featureStatusesToRemove - kFeatureStatus to remove when job is finished.
	 * @return BatchJob
	 */
	public static function addIndexJob($partnerId, $objectType, baseObjectFilter $filter, $shouldUpdate, $featureStatusesToRemove = array())
	{
	    $jobData = new kIndexJobData();
 		$jobData->setFilter($filter);
 		$jobData->setShouldUpdate($shouldUpdate);
 		$jobData->setFeatureStatusesToRemove($featureStatusesToRemove);
 		
		$batchJob = new BatchJob();
		$batchJob->setPartnerId($partnerId);
		
		return self::addJob($batchJob, $jobData, BatchJobType::INDEX, $objectType);
	}
	
	/**
	 * @param int $partnerId
	 * @param int $objectType of enum CopyObjectType
	 * @param baseObjectFilter $filter The filter should return the list of objects that need to be copied.
	 * @param BaseObject $templateObject Template object to overwrite attributes on the copied object
	 * @return BatchJob
	 */
	public static function addCopyJob($partnerId, $objectType, baseObjectFilter $filter, BaseObject $templateObject)
	{
	    $jobData = new kCopyJobData();
 		$jobData->setFilter($filter);
 		$jobData->setTemplateObject($templateObject);
 		
		$batchJob = new BatchJob();
		$batchJob->setPartnerId($partnerId);
		
		return self::addJob($batchJob, $jobData, BatchJobType::COPY, $objectType);
	}
	
	/**
	 * @param int $partnerId
	 * @param int $objectType of enum DeleteObjectType
	 * @param baseObjectFilter $filter The filter should return the list of objects that need to be deleted
	 * @return BatchJob
	 */
	public static function addDeleteJob($partnerId, $objectType, baseObjectFilter $filter)
	{
	    $jobData = new kDeleteJobData();
 		$jobData->setFilter($filter);
 		
		$batchJob = new BatchJob();
		$batchJob->setPartnerId($partnerId);
		
		return self::addJob($batchJob, $jobData, BatchJobType::DELETE, $objectType);
	}
	
	public static function addBulkDownloadJob($partnerId, $puserId, $entryIds, $flavorParamsId)
	{
		$entryIds = explode(",", $entryIds);
		foreach($entryIds as $entryId)
		{
			$dbEntry = entryPeer::retrieveByPK($entryId);
			if (!$dbEntry)
				throw new APIException(APIErrors::INVALID_ENTRY_ID, $entryId);
		}
		
		$jobDb = new BatchJob();
		$jobDb->setPartnerId($partnerId);
		$data = new kBulkDownloadJobData();
		
		$data->setEntryIds(implode(",", $entryIds));
		$data->setFlavorParamsId($flavorParamsId);
		$data->setPuserId($puserId);
		
		return self::addJob($jobDb, $data, BatchJobType::BULKDOWNLOAD);
	}
	
	/**
	 * @param BatchJob $batchJob
	 * @param entry $entry
	 * @param string $flavorAssetId
	 * @param string $inputFileSyncLocalPath
	 * @return BatchJob
	 */
	public static function addConvertProfileJob(BatchJob $parentJob = null, entry $entry, $flavorAssetId, $inputFileSyncLocalPath, $inputFileSyncWamsAssetId = null)
	{	
		KalturaLog::debug("Parent job [" . ($parentJob ? $parentJob->getId() : 'none') . "] entry [" . $entry->getId() . "] flavor asset [$flavorAssetId] input file [$inputFileSyncLocalPath]");
		if($entry->getConversionQuality() == conversionProfile2::CONVERSION_PROFILE_NONE)
		{
			$entry->setStatus(entryStatus::PENDING);
			$entry->save();
			
			KalturaLog::notice('Entry should not be converted');
			return null;
		}

		if (is_null($inputFileSyncWamsAssetId)) {
			// if file size is 0, do not create conversion profile and set entry status as error converting
			if (!file_exists($inputFileSyncLocalPath) || kFile::fileSize($inputFileSyncLocalPath) == 0) {
				KalturaLog::debug("Input file [$inputFileSyncLocalPath] does not exist");

				$partner = $entry->getPartner();

				$conversionProfile = myPartnerUtils::getConversionProfile2ForEntry($entry->getId());

				// load the asset params to the instance pool
				$flavorIds = flavorParamsConversionProfilePeer::getFlavorIdsByProfileId($conversionProfile->getId());
				assetParamsPeer::retrieveByPKs($flavorIds);

				$conversionRequired = false;
				$sourceFileRequiredStorages = array();
				$sourceIncludedInProfile = false;
				$flavorAsset = assetPeer::retrieveById($flavorAssetId);
				$flavors = flavorParamsConversionProfilePeer::retrieveByConversionProfile($conversionProfile->getId());
				KalturaLog::debug("Found flavors [" . count($flavors) . "] in conversion profile [" . $conversionProfile->getId() . "]");
				foreach ($flavors as $flavor) {
					/* @var $flavor flavorParamsConversionProfile */

					if ($flavor->getFlavorParamsId() == $flavorAsset->getFlavorParamsId()) {
						KalturaLog::debug("Flavor [" . $flavor->getFlavorParamsId() . "] is ingested source");
						$sourceIncludedInProfile = true;
						continue;
					}

					if ($flavor->getOrigin() == assetParamsOrigin::INGEST) {
						KalturaLog::debug("Flavor [" . $flavor->getFlavorParamsId() . "] should be ingested");
						continue;
					}

					if ($flavor->getOrigin() == assetParamsOrigin::CONVERT_WHEN_MISSING) {
						$siblingFlavorAsset = assetPeer::retrieveByEntryIdAndParams($entry->getId(), $flavor->getFlavorParamsId());
						if ($siblingFlavorAsset) {
							KalturaLog::debug("Flavor [" . $flavor->getFlavorParamsId() . "] already ingested");
							continue;
						}
					}

					$flavorParams = assetParamsPeer::retrieveByPK($flavor->getFlavorParamsId());
					$sourceFileRequiredStorages[] = $flavorParams->getSourceRemoteStorageProfileId();

					$conversionRequired = true;
					break;
				}

				if ($conversionRequired) {
					foreach ($sourceFileRequiredStorages as $storageId) {
						if ($storageId == StorageProfile::STORAGE_KALTURA_DC) {
							$key = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
							list($syncFile, $local) = kFileSyncUtils::getReadyFileSyncForKey($key, true, false);
							if ($syncFile && $syncFile->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL && $partner && $partner->getImportRemoteSourceForConvert()) {
								KalturaLog::debug("Creates import job for remote file sync");
								$url = $syncFile->getExternalUrl($entry->getId());
								kJobsManager::addImportJob($parentJob, $entry->getId(), $partner->getId(), $url, $flavorAsset, null, null, true);
								continue;
							}
						}
						elseif ($flavorAsset->getExternalUrl($storageId)) {
							continue;
						}

						kBatchManager::updateEntry($entry->getId(), entryStatus::ERROR_CONVERTING);

						$flavorAsset = assetPeer::retrieveById($flavorAssetId);
						$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
						$flavorAsset->setDescription('Entry of size 0 should not be converted');
						$flavorAsset->save();

						KalturaLog::err('Entry of size 0 should not be converted');
						return null;
					}
				}
				else {
					if ($flavorAsset->getStatus() == asset::FLAVOR_ASSET_STATUS_QUEUED) {
						if ($sourceIncludedInProfile) {
							$flavorAsset->setStatusLocalReady();
						}
						else {
							$flavorAsset->setStatus(asset::FLAVOR_ASSET_STATUS_DELETED);
							$flavorAsset->setDeletedAt(time());
						}

						$flavorAsset->save();

						if ($sourceIncludedInProfile) {
							kBusinessPostConvertDL::handleConvertFinished(null, $flavorAsset);
						}
					}
					return null;
				}
			}
		}

		if ($entry->getStatus() != entryStatus::READY)
		{
			$entry->setStatus(entryStatus::PRECONVERT);
		}
		
		$jobData = new kConvertProfileJobData();
		$jobData->setFlavorAssetId($flavorAssetId);
		$jobData->setInputFileSyncLocalPath($inputFileSyncLocalPath);
		$jobData->setInputFileSyncWamsAssetId($inputFileSyncWamsAssetId);
		$jobData->setExtractMedia(true);
		
		if($entry->getType() != entryType::MEDIA_CLIP)
		{
			$jobData->setExtractMedia(false);
			$entry->setCreateThumb(false);
		}
		$entry->save();
 		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entry->getId());
			$batchJob->setPartnerId($entry->getPartnerId());
			$batchJob->setUseNewRoot(true);
		}
		return self::addJob($batchJob, $jobData, BatchJobType::CONVERT_PROFILE);
	}
	
	/**
	 * @param BatchJob $parentJob
	 * @param string $entryId
	 * @param int $partnerId
	 * @param StorageProfile $externalStorage
	 * @param SyncFile $fileSync
	 * @param string $srcFileSyncLocalPath
	 * @param bool $force
	 * 
	 * @return BatchJob
	 */
	public static function addStorageExportJob(BatchJob $parentJob = null, $entryId, $partnerId, StorageProfile $externalStorage, FileSync $fileSync, $srcFileSyncLocalPath, $force = false, $dc = null)
	{
		KalturaLog::debug("entryId[$entryId], partnerId[$partnerId], externalStorage id[" . $externalStorage->getId() . "], fileSync id[" . $fileSync->getId() . "], srcFileSyncLocalPath[$srcFileSyncLocalPath]");
		
		$netStorageExportData = new kStorageExportJobData();
	    $netStorageExportData->setServerUrl($externalStorage->getStorageUrl()); 
	    $netStorageExportData->setServerUsername($externalStorage->getStorageUsername()); 
	    $netStorageExportData->setServerPassword($externalStorage->getStoragePassword());
	    $netStorageExportData->setFtpPassiveMode($externalStorage->getStorageFtpPassiveMode());
	    $netStorageExportData->setSrcFileSyncLocalPath($srcFileSyncLocalPath);
		$netStorageExportData->setSrcFileSyncId($fileSync->getId());
		$netStorageExportData->setForce($force);
		$netStorageExportData->setDestFileSyncStoredPath($externalStorage->getStorageBaseDir() . '/' . $fileSync->getFilePath());
		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild(false);
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($partnerId);
		}
		
		$batchJob->setDc($dc);
		KalturaLog::log("Creating Storage export job, with source file: " . $netStorageExportData->getSrcFileSyncLocalPath()); 
		return self::addJob($batchJob, $netStorageExportData, BatchJobType::STORAGE_EXPORT, $externalStorage->getProtocol());
	}
	
	/**
	 * @param BatchJob $parentJob
	 * @param int $partnerId
	 * @param int $srcCategoryId the source category id
	 * @param int $destCategoryId the destination category id
	 * @param bool $moveFromChildren indicates that all entries from all child categories should be moved as well
	 * @param bool $copyOnly indicates that the entries shouldn't be deleted from the source entry
	 * @return BatchJob
	 */
	public static function addMoveCategoryEntriesJob(BatchJob $parentJob = null, $partnerId, $srcCategoryId, $destCategoryId, $moveFromChildren = false, $copyOnly = false)
	{
		$moveCategoryEntriesData = new kMoveCategoryEntriesJobData();
	    $moveCategoryEntriesData->setSrcCategoryId($srcCategoryId);
	    $moveCategoryEntriesData->setDestCategoryId($destCategoryId);
	    $moveCategoryEntriesData->setMoveFromChildren($moveFromChildren);
	    $moveCategoryEntriesData->setCopyOnly($copyOnly);
		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild(false);
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setPartnerId($partnerId);
		}
		
		return self::addJob($batchJob, $moveCategoryEntriesData, BatchJobType::MOVE_CATEGORY_ENTRIES);
	}
	
	public static function addStorageDeleteJob(BatchJob $parentJob = null, $entryId = null, StorageProfile $storage, FileSync $fileSync)
	{
		$netStorageDeleteData = new kStorageDeleteJobData();
	    $netStorageDeleteData->setServerUrl($storage->getStorageUrl()); 
	    $netStorageDeleteData->setServerUsername($storage->getStorageUsername()); 
	    $netStorageDeleteData->setServerPassword($storage->getStoragePassword());
	    $netStorageDeleteData->setFtpPassiveMode($storage->getStorageFtpPassiveMode());

	    $netStorageDeleteData->setSrcFileSyncId($fileSync->getId());
		$netStorageDeleteData->setDestFileSyncStoredPath($storage->getStorageBaseDir() . '/' . $fileSync->getFilePath());
		if ($parentJob)
		{
			$batchJob = $parentJob->createChild(false);
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($storage->getPartnerId());
		}
		
		KalturaLog::log("Creating Net-Storage Delete job, with source file: " . $netStorageDeleteData->getSrcFileSyncLocalPath()); 
		return self::addJob($batchJob, $netStorageDeleteData, BatchJobType::STORAGE_DELETE, $storage->getProtocol());
	}
	
	public static function addFutureDeletionJob(BatchJob $parentJob = null, $entryId = null, Partner $partner, $syncKey, $localFileSyncPath, $dc)
	{
		$deleteFileData = new kDeleteFileJobData();
		
		$deleteFileData->setLocalFileSyncPath($localFileSyncPath);
		
		$deleteFileData->setSyncKey($syncKey);
		
		if ($parentJob)
		{
			$batchJob = $parentJob->createChild(false);
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($partner->getId());
		}
		
		$batchJob->setStatus(BatchJob::BATCHJOB_STATUS_RETRY);
		$batchJob->setCheckAgainTimeout(12*60*60);
		$batchJob->setDc($dc);
		
		KalturaLog::log("Creating File Delete job, from data center id: ". $deleteFileData->getDC() ." with source file: " . $deleteFileData->getLocalFileSyncPath()); 
		return self::addJob($batchJob, $deleteFileData, BatchJobType::DELETE_FILE );
		
	}
	
	
	public static function addExtractMediaJob(BatchJob $parentJob, $inputFileSyncLocalPath, $flavorAssetId, $inputFileSyncWamsAssetId = null)
	{
		$profile = null;
		try{
			$profile = myPartnerUtils::getConversionProfile2ForEntry($parentJob->getEntryId());
			KalturaLog::debug("profile [" . $profile->getId() . "]");
		}
		catch(Exception $e)
		{
			KalturaLog::err($e->getMessage());
		}
		
		$mediaInfoEngine = mediaParserType::MEDIAINFO;
		if($profile)
			$mediaInfoEngine = $profile->getMediaParserType();
		              
		$extractMediaData = new kExtractMediaJobData();
		$extractMediaData->setSrcFileSyncLocalPath($inputFileSyncLocalPath);
		$extractMediaData->setFlavorAssetId($flavorAssetId);
		$extractMediaData->setSrcFileSyncWamsAssetId($inputFileSyncWamsAssetId);
		
		$batchJob = $parentJob->createChild(false);
		
		KalturaLog::log("Creating Extract Media job, with source file: " . $extractMediaData->getSrcFileSyncLocalPath()); 
		return self::addJob($batchJob, $extractMediaData, BatchJobType::EXTRACT_MEDIA, $mediaInfoEngine);
	}
	
	public static function addNotificationJob(BatchJob $parentJob = null, $entryId, $partnerId, $notificationType, $sendType, $puserId, $objectId, $notificationData)
	{
		$jobData = new kNotificationJobData();
		$jobData->setType($notificationType);
		$jobData->setSendType($sendType);
		$jobData->setUserId($puserId);
		$jobData->setObjectId($objectId);
		$jobData->setData($notificationData);
			
 		
		$batchJob = null;
		if($parentJob)
		{
			$batchJob = $parentJob->createChild();
		}
		else
		{
			$batchJob = new BatchJob();
			$batchJob->setEntryId($entryId);
			$batchJob->setPartnerId($partnerId);
		}
			
		$batchJob = self::addJob($batchJob, $jobData, BatchJobType::NOTIFICATION, $notificationType);
		
		if($sendType == kNotificationJobData::NOTIFICATION_MGR_NO_SEND || $sendType == kNotificationJobData::NOTIFICATION_MGR_SEND_SYNCH)
			$batchJob = self::updateBatchJob($batchJob, BatchJob::BATCHJOB_STATUS_DONT_PROCESS);
			
		return $batchJob;
	}
	
	
	/**
	 * @param BatchJob $batchJob
	 * @param $data
	 * @param int $type
	 * @param int $subType
	 * @return BatchJob
	 */
	public static function addJob(BatchJob $batchJob, $data, $type, $subType = null)
	{
		$batchJob->setJobType($type);
		$batchJob->setJobSubType($subType);
		$batchJob->setData($data);
		
		if(!$batchJob->getParentJobId() && $batchJob->getEntryId())
		{
			$currentJob = kBatchManager::getCurrentUpdatingJob();
			if($currentJob && $currentJob->getEntryId() == $batchJob->getEntryId())
			{
				$batchJob->setParentJobId($currentJob->getId());
				$batchJob->setBulkJobId($currentJob->getBulkJobId());
				$batchJob->setRootJobId($currentJob->getRootJobId());
			}
			else
			{
				$entry = entryPeer::retrieveByPKNoFilter($batchJob->getEntryId()); // some jobs could be on deleted entry
				if($entry)
				{	
					$batchJob->setRootJobId($entry->getBulkUploadId());
					$batchJob->setBulkJobId($entry->getBulkUploadId());		
				}
			}
		}
			
		// validate partner id
		$partnerId = $batchJob->getPartnerId();
//		if(!$partnerId)
//			throw new APIException(APIErrors::PARTNER_NOT_SET);
			
		// validate that partner exists
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
		{
			KalturaLog::err("Invalid partner id [$partnerId]");
			throw new APIException(APIErrors::INVALID_PARTNER_ID, $partnerId);
		}
		
		// set the priority and work group
		$batchJob->setPriority($partner->getPriority($batchJob->getBulkJobId()));
		
		$batchJob = self::updateBatchJob($batchJob, BatchJob::BATCHJOB_STATUS_PENDING);
		
		// look for identical jobs
		$twinJobs = BatchJobPeer::retrieveDuplicated($type, $data);
		$twinJob = null;
		
		if(count($twinJobs))
			foreach($twinJobs as $currentTwinJob)
				if($currentTwinJob->getId() != $batchJob->getId())
					$twinJob = reset($twinJobs);
					
		if(!is_null($twinJob))
		{
			$batchJob->setTwinJobId($twinJob->getId());
			
			if(!kConf::get("batch_ignore_duplication"))
			{
				$batchJob = self::updateBatchJob($batchJob, $twinJob->getStatus(), $twinJob);
			}
			else
			{
				$batchJob->save();
			}
		}
		
		return $batchJob;		
	}

	/**
	 * Function adds bulk upload job to the queue
	 * @param Partner $partner
	 * @param kBulkUploadJobData $jobData
	 * @param string $bulkUploadType
	 * @throws APIException
	 * @return BatchJob
	 */
	public static function addBulkUploadJob(Partner $partner, kBulkUploadJobData $jobData, $bulkUploadType = null)
	{
		$job = new BatchJob();
		$job->setPartnerId($partner->getId());
		$job->setJobType(BatchJobType::BULKUPLOAD);
		$job->setJobSubType($bulkUploadType);
		$job->save();

		$syncKey = $job->getSyncKey(BatchJob::FILE_SYNC_BATCHJOB_SUB_TYPE_BULKUPLOAD);
//		kFileSyncUtils::file_put_contents($syncKey, file_get_contents($csvFileData["tmp_name"]));
		try{
			kFileSyncUtils::moveFromFile($jobData->getFilePath(), $syncKey, true);
		}
		catch(Exception $e)
		{
			throw new APIException(APIErrors::BULK_UPLOAD_CREATE_CSV_FILE_SYNC_ERROR);
		}
		
		$filePath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
		
		if(is_null($jobData))
		{
			throw new APIException(APIErrors::BULK_UPLOAD_BULK_UPLOAD_TYPE_NOT_VALID, $bulkUploadType);
		}
		if (!$jobData->getBulkUploadObjectType())
		{
		    $jobData->setBulkUploadObjectType(BulkUploadObjectType::ENTRY);
		}
		
		$jobData->setFilePath($filePath);
		
		if ($jobData->getBulkUploadObjectType() == BulkUploadObjectType::ENTRY && !$jobData->getObjectData()->getConversionProfileId())
		{
			$jobData->setConversionProfileId($partner->getDefaultConversionProfileId());
			$kmcVersion = $partner->getKmcVersion();
		    $check = null;
			if($kmcVersion < 2)
    		{
    			$check = ConversionProfilePeer::retrieveByPK($jobData->getConversionProfileId());
    		}
    		else
    		{
    			$check = conversionProfile2Peer::retrieveByPK($jobData->getConversionProfileId());
    		}
    		if(!$check)
    			throw new APIException(APIErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $jobData->getConversionProfileId());
    	}

		return kJobsManager::addJob($job, $jobData, BatchJobType::BULKUPLOAD, kPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadType));
	}
}