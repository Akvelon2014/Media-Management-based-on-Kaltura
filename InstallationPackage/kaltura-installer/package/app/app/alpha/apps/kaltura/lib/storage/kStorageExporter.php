<?php
/**
 * @package Core
 * @subpackage storage
 */
class kStorageExporter implements kObjectChangedEventConsumer, kBatchJobStatusEventConsumer, kObjectDeletedEventConsumer
{
	/* (non-PHPdoc)
	 * @see kObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		// if changed object is entry 
		if($object instanceof entry && in_array(entryPeer::MODERATION_STATUS, $modifiedColumns) && $object->getModerationStatus() == entry::ENTRY_MODERATION_STATUS_APPROVED)
			return true;
		
		// if changed object is flavor asset
		if($object instanceof flavorAsset && !$object->getIsOriginal() && in_array(assetPeer::STATUS, $modifiedColumns) && $object->isLocalReadyStatus())
			return true;
			
		return false;		
	}
	
	/* (non-PHPdoc)
	 * @see kObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{
		// if changed object is entry 
		if($object instanceof entry && in_array(entryPeer::MODERATION_STATUS, $modifiedColumns) && $object->getModerationStatus() == entry::ENTRY_MODERATION_STATUS_APPROVED)
		{
			$externalStorages = StorageProfilePeer::retrieveAutomaticByPartnerId($object->getPartnerId());
			foreach($externalStorages as $externalStorage)
			{
				if($externalStorage->getTrigger() == StorageProfile::STORAGE_TEMP_TRIGGER_MODERATION_APPROVED)
					self::exportEntry($object, $externalStorage);
			}
		}
		
		// if changed object is flavor asset
		if($object instanceof flavorAsset && !$object->getIsOriginal() && in_array(assetPeer::STATUS, $modifiedColumns) && $object->isLocalReadyStatus())
		{
			$entry = $object->getentry();
			
			$externalStorages = StorageProfilePeer::retrieveAutomaticByPartnerId($object->getPartnerId());
			foreach($externalStorages as $externalStorage)
			{
				if ($externalStorage->triggerFitsReadyAsset($entry->getId()))
				{
					self::exportFlavorAsset($object, $externalStorage);
				}
			}
		}
		return true;
	}

	/**
	 * @param flavorAsset $flavor
	 * @param StorageProfile $externalStorage
	 */
	static public function exportFlavorAsset(flavorAsset $flavor, StorageProfile $externalStorage)
	{
	    if (!$externalStorage->shouldExportFlavorAsset($flavor)) {
		    return;
		}
			
		$key = $flavor->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$exporting = self::export($flavor->getentry(), $externalStorage, $key, !$flavor->getIsOriginal());
				
		return $exporting;
	}
	
	/**
	 * @param entry $entry
	 * @return array<FileSyncKey>
	 */
	static protected function getEntrySyncKeys(entry $entry, StorageProfile $externalStorage)
	{
		$exportFileSyncsKeys = array();
		
		$exportFileSyncsKeys[] = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
		$exportFileSyncsKeys[] = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_ISM);
		$exportFileSyncsKeys[] = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_ISMC);
		
		$flavorAssets = array();
		$flavorParamsIds = $externalStorage->getFlavorParamsIds();
		KalturaLog::log(__METHOD__ . " flavorParamsIds [$flavorParamsIds]");
		$relevantStatuses = array(asset::ASSET_STATUS_READY, asset::ASSET_STATUS_EXPORTING);
		if(is_null($flavorParamsIds) || !strlen(trim($flavorParamsIds)))
		{
			$flavorAssets = assetPeer::retrieveFlavorsByEntryIdAndStatus($entry->getId(), null, $relevantStatuses);
		}
		else
		{
			$flavorParamsArr = explode(',', $flavorParamsIds);
			KalturaLog::log(__METHOD__ . " flavorParamsIds count [" . count($flavorParamsArr) . "]");
			$flavorAssets = assetPeer::retrieveFlavorsByEntryIdAndStatus($entry->getId(), $flavorParamsArr, $relevantStatuses);
		}
		
		foreach($flavorAssets as $flavorAsset) {
			if ($externalStorage->shouldExportFlavorAsset($flavorAsset)) {
				$exportFileSyncsKeys[] = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			}
			else {
				KalturaLog::log('Flavor asset id ['.$flavorAsset->getId().'] should not be exported');
			}
		}
		
		return $exportFileSyncsKeys;
	}
	
	/**
	 * @param entry $entry
	 * @param FileSyncKey $key
	 */
	static protected function export(entry $entry, StorageProfile $externalStorage, FileSyncKey $key, $force = false)
	{			
		$externalFileSync = kFileSyncUtils::createPendingExternalSyncFileForKey($key, $externalStorage);
		/* @var $fileSync FileSync */
		list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($key,true,false);
		if (!$fileSync || $fileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL) {
			KalturaLog::err("no ready fileSync was found for key [$key]");
			return;
		}
		$parent_file_sync = kFileSyncUtils::resolve($fileSync);
		$srcFileSyncPath = $parent_file_sync->getFileRoot() . $parent_file_sync->getFilePath();
		kJobsManager::addStorageExportJob(null, $entry->getId(), $entry->getPartnerId(), $externalStorage, $externalFileSync, $srcFileSyncPath, $force, $fileSync->getDc());
		return true;
	}
	
	/**
	 * @param FileSyncKey $key
	 * @return bool
	 */
	static public function shouldExport(FileSyncKey $key, StorageProfile $externalStorage)
	{
		KalturaLog::log(__METHOD__ . " - key [$key], externalStorage id[" . $externalStorage->getId() . "]");
		
		list($kalturaFileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($key, false, false);
		if(!$kalturaFileSync) // no local copy to export from
		{
			KalturaLog::log(__METHOD__ . " key [$key] not found localy");
			return false;
		}
		
		KalturaLog::log(__METHOD__ . " validating file size [" . $kalturaFileSync->getFileSize() . "] is between min [" . $externalStorage->getMinFileSize() . "] and max [" . $externalStorage->getMaxFileSize() . "]");
		if($externalStorage->getMaxFileSize() && $kalturaFileSync->getFileSize() > $externalStorage->getMaxFileSize()) // too big
		{
			KalturaLog::log(__METHOD__ . " key [$key] file too big");
			return false;
		}
			
		if($externalStorage->getMinFileSize() && $kalturaFileSync->getFileSize() < $externalStorage->getMinFileSize()) // too small
		{
			KalturaLog::log(__METHOD__ . " key [$key] file too small");
			return false;
		}
		
		$storageFileSync = kFileSyncUtils::getReadyPendingExternalFileSyncForKey($key, $externalStorage->getId());

		if($storageFileSync) // already exported or currently being exported
		{
			KalturaLog::log(__METHOD__ . " key [$key] already exported or being exported");
			return false;
		}
			
		return true;
	}
	
	/**
	 * @param entry $entry
	 * @param StorageProfile $externalStorage
	 */
	public static function exportEntry(entry $entry, StorageProfile $externalStorage, &$exportedKeys = array(), &$nonExportedKeys = array())
	{
		$checkFileSyncsKeys = self::getEntrySyncKeys($entry, $externalStorage);
		foreach($checkFileSyncsKeys as $key)
		{
    		if (self::shouldExport($key, $externalStorage)) {
    			$exported = self::export($entry, $externalStorage, $key);
    			if ($exported) {
    			    $exportedKeys[] = $key;
    			}
    			else {
    			    $nonExportedKeys[] = $key;
    			}
    		}
    		else {
    		    $nonExportedKeys[] = $key;
    		    KalturaLog::log("no need to export key [$key] to externalStorage id[" . $externalStorage->getId() . "]");
    		}
			
		}
	}
	
	/* (non-PHPdoc)
	 * @see kBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if($dbBatchJob->getStatus() != BatchJob::BATCHJOB_STATUS_FINISHED)
			return false;
						
		// convert collection finished - export ism and ismc files
		if($dbBatchJob->getJobType() == BatchJobType::CONVERT_COLLECTION && $dbBatchJob->getJobSubType() == conversionEngineType::EXPRESSION_ENCODER3)
			return true;
		
		if($dbBatchJob->getJobType() == BatchJobType::CONVERT_PROFILE)
			return true;
		
		return false;
	}
	
	public static function exportSourceAssetFromJob(BatchJob $dbBatchJob)
	{
		// convert profile finished - export source flavor
		if($dbBatchJob->getJobType() == BatchJobType::CONVERT_PROFILE)
		{
			$externalStorages = StorageProfilePeer::retrieveAutomaticByPartnerId($dbBatchJob->getPartnerId());
			$sourceFlavor = assetPeer::retrieveOriginalByEntryId($dbBatchJob->getEntryId());
			if (!$sourceFlavor) 
			{
			    KalturaLog::debug('Cannot find source flavor for entry id ['.$dbBatchJob->getEntryId().']');
			}
			else if (!$sourceFlavor->isLocalReadyStatus()) 
			{
			    KalturaLog::debug('Source flavor id ['.$sourceFlavor->getId().'] has status ['.$sourceFlavor->getStatus().'] - not ready for export');
			}
			else
			{
    			foreach($externalStorages as $externalStorage)
    			{
    				if ($externalStorage->triggerFitsReadyAsset($dbBatchJob->getEntryId()))
    				{
    				    self::exportFlavorAsset($sourceFlavor, $externalStorage);
    				}
    			}
			}
		}
    			
		// convert collection finished - export ism and ismc files
		if($dbBatchJob->getJobType() == BatchJobType::CONVERT_COLLECTION && $dbBatchJob->getJobSubType() == conversionEngineType::EXPRESSION_ENCODER3)
		{
			$entry = $dbBatchJob->getEntry();
			$externalStorages = StorageProfilePeer::retrieveAutomaticByPartnerId($dbBatchJob->getPartnerId());
			foreach($externalStorages as $externalStorage)
			{
				if($externalStorage->triggerFitsReadyAsset($entry->getId()))
				{
					$ismKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_ISM);
					if(kFileSyncUtils::fileSync_exists($ismKey))
						self::export($entry, $externalStorage, $ismKey);
					
					$ismcKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_ISMC);
					if(kFileSyncUtils::fileSync_exists($ismcKey))
						self::export($entry, $externalStorage, $ismcKey);
				}
			}
		}
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see kBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob, BatchJob $twinJob = null)
	{
		// convert profile finished - export source flavor
		if ($dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FINISHED)
		{
    		return self::exportSourceAssetFromJob($dbBatchJob);
		}
		return true;
	}
	
	
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		/* @var $object FileSync */
		$syncKey = kFileSyncUtils::getKeyForFileSync($object);
		$entryId = null;
		switch ($object->getObjectType())
		{
			case FileSyncObjectType::ENTRY:
				$entryId = $object->getObjectId();
				break;
				
			case FileSyncObjectType::BATCHJOB:
				BatchJobPeer::setUseCriteriaFilter(false);
				$batchJob = BatchJobPeer::retrieveByPK($object->getObjectId());
				if ($batchJob)
				{
					$entryId = $batchJob->getEntryId();
				}
				BatchJobPeer::setUseCriteriaFilter(true);
				break;
				
			case FileSyncObjectType::ASSET:
				assetPeer::setUseCriteriaFilter(false);
				$asset = assetPeer::retrieveById($object->getId());
				if ($asset)
				{
					$entryId = $asset->getEntryId();
					//the next piece of code checks whether the entry to which
					//the deleted asset belongs to is a "replacement" entry
                    $entry = entryPeer::retrieveByPKNoFilter($entryId);
                    if (!$entry) 
                    {
                    	KalturaLog::alert("No entry found by the ID of [$entryId]");
                    }
                    
                    else if ($entry->getReplacedEntryId())
                    {
                        KalturaLog::info("Will not handle event - deleted asset belongs to replacement entry");
                        return;
                    }
					
				}
				assetPeer::setUseCriteriaFilter(true);
				break;
		}
		
		$storage = StorageProfilePeer::retrieveByPK($object->getDc());
		
		kJobsManager::addStorageDeleteJob($raisedJob, $entryId ,$storage, $object);		
	}
	
	/**
	 * @param BaseObject $object
	 * @return bool true if the consumer should handle the event
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		
		if ($object instanceof FileSync)
		{
			if ($object->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL)
			{
				$storage = StorageProfilePeer::retrieveByPK($object->getDc());
				KalturaLog::debug("storage auto delete policy: ".$storage->getAllowAutoDelete());
				if ($storage->getStatus() == StorageProfile::STORAGE_STATUS_AUTOMATIC && $storage->getAllowAutoDelete())
				{
					return true;
				}
				KalturaLog::debug('Unable to consume deleted event; storageProfile status is not equal to '. StorageProfile::STORAGE_STATUS_AUTOMATIC );
			}
		}
		return false;
	}
}