<?php

class kObjectDeleteHandler implements kObjectDeletedEventConsumer
{
	/* (non-PHPdoc)
	 * @see kObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if($object instanceof entry)
			return true;
			
		if($object instanceof category)
			return true;
			
		if($object instanceof uiConf)
			return true;
			
		if($object instanceof BatchJob)
			return true;
			
		if($object instanceof asset)
			return true;
			
		if($object instanceof syndicationFeed)
			return true;
			
		if($object instanceof conversionProfile2)
			return true;
			
		if($object instanceof kuser)
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see kObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null) 
	{
		if($object instanceof entry)
			$this->entryDeleted($object);
			
		if($object instanceof category)
			$this->categoryDeleted($object);
			
		if($object instanceof uiConf)
			$this->uiConfDeleted($object);
			
		if($object instanceof BatchJob)
			$this->batchJobDeleted($object);
			
		if($object instanceof asset)
			$this->assetDeleted($object);
			
		if($object instanceof syndicationFeed)
			$this->syndicationFeedDeleted($object);
			
		if($object instanceof conversionProfile2)
			$this->conversionProfileDeleted($object);
			
		if($object instanceof kuser)
			$this->kuserDelete($object);
			
		return true;
	}

	/**
	 * @param string $id
	 * @param int $type
	 */
	protected function syncableDeleted($id, $type) 
	{
		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_ID, $id);
		$c->add(FileSyncPeer::OBJECT_TYPE, $type);
		$c->add(FileSyncPeer::STATUS, array(FileSync::FILE_SYNC_STATUS_PURGED, FileSync::FILE_SYNC_STATUS_DELETED), Criteria::NOT_IN);
		
		$fileSyncs = FileSyncPeer::doSelect($c);
		foreach($fileSyncs as $fileSync)
		{
			$key = kFileSyncUtils::getKeyForFileSync($fileSync);
			kFileSyncUtils::deleteSyncFileForKey($key);
		}
	}

	/**
	 * @param entry $entry
	 */
	protected function entryDeleted(entry $entry) 
	{
		$this->syncableDeleted($entry->getId(), FileSyncObjectType::ENTRY);
		
		// delete flavor assets
		$c = new Criteria();
		$c->add(assetPeer::ENTRY_ID, $entry->getId());
		$c->add(assetPeer::STATUS, asset::FLAVOR_ASSET_STATUS_DELETED, Criteria::NOT_EQUAL);
		$c->add(assetPeer::DELETED_AT, null, Criteria::ISNULL);
		
		$assets = assetPeer::doSelect($c);
		foreach($assets as $asset)
		{
			$asset->setStatus(asset::FLAVOR_ASSET_STATUS_DELETED);
			$asset->setDeletedAt(time());
			$asset->save();
		}
	
		$c = new Criteria();
		$c->add(assetParamsOutputPeer::ENTRY_ID, $entry->getId());
		$c->add(assetParamsOutputPeer::DELETED_AT, null, Criteria::ISNULL);
		$flavorParamsOutputs = assetParamsOutputPeer::doSelect($c);
		foreach($flavorParamsOutputs as $flavorParamsOutput)
		{
			$flavorParamsOutput->setDeletedAt(time());
			$flavorParamsOutput->save();
		}
		
		$filter = new categoryEntryFilter();
		$filter->setEntryIdEqaul($entry->getId());

		kJobsManager::addDeleteJob($entry->getPartnerId(), DeleteObjectType::CATEGORY_ENTRY, $filter);
	}
	
	protected function kuserDelete(kuser $kuser)
	{
		$filter = new categoryKuserFilter();
		$filter->setUserIdEqual($kuser->getPuserId());

		kJobsManager::addDeleteJob($kuser->getPartnerId(), DeleteObjectType::CATEGORY_USER, $filter);
	}
	
	/**
	 * @param category $category
	 */
	protected function categoryDeleted(category $category)
	{
		//TODO - ADD JOB TO DELETE ALL CategoryKusers.
	}
	
	/**
	 * @param uiConf $uiConf
	 */
	protected function uiConfDeleted(uiConf $uiConf) 
	{
		$this->syncableDeleted($uiConf->getId(), FileSyncObjectType::UICONF);
	}

	/**
	 * @param BatchJob $batchJob
	 */
	protected function batchJobDeleted(BatchJob $batchJob) 
	{
		$this->syncableDeleted($batchJob->getId(), FileSyncObjectType::BATCHJOB);
	}

	/**
	 * @param asset $asset
	 */
	protected function assetDeleted(asset $asset) 
	{
		$this->syncableDeleted($asset->getId(), FileSyncObjectType::FLAVOR_ASSET);
	}
	
	/**
	 * @param syndicationFeed $syndicationFeed
	 */
	protected function syndicationFeedDeleted(syndicationFeed $syndicationFeed)
	{
		if($syndicationFeed->getType() == syndicationFeedType::KALTURA_XSLT)
			$this->syncableDeleted($syndicationFeed->getId(), FileSyncObjectType::SYNDICATION_FEED);
	}
	
	/**
	 * @param conversionProfile2 $conversionProfile
	 */
	protected function conversionProfileDeleted(conversionProfile2 $conversionProfile)
	{
		$this->syncableDeleted($conversionProfile->getId(), FileSyncObjectType::CONVERSION_PROFILE);
	}
}
