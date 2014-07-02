<?php

/**
 * Subclass for representing a row from the 'flavor_asset' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class flavorAsset extends asset
{
	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */
	public function applyDefaultValues()
	{
		parent::applyDefaultValues();
		$this->setType(assetType::FLAVOR);
	}

	/**
	 * Gets an array of assetParamsOutput objects which contain a foreign key that references this object.
	 *
	 * If this collection has already been initialized with an identical Criteria, it returns the collection.
	 * Otherwise if this asset has previously been saved, it will retrieve
	 * related assetParamsOutputs from storage. If this asset is new, it will return
	 * an empty collection or the current collection, the criteria is ignored on a new object.
	 *
	 * @param      PropelPDO $con
	 * @param      Criteria $criteria
	 * @return     array flavorParamsOutput[]
	 * @throws     PropelException
	 */
	public function getflavorParamsOutputs($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(assetPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collassetParamsOutputs === null) {
			if ($this->isNew()) {
			   $this->collassetParamsOutputs = array();
			} else {

				$criteria->add(assetParamsOutputPeer::FLAVOR_ASSET_ID, $this->id);

				assetParamsOutputPeer::addSelectColumns($criteria);
				$this->collassetParamsOutputs = assetParamsOutputPeer::doSelect($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return the collection.


				$criteria->add(assetParamsOutputPeer::FLAVOR_ASSET_ID, $this->id);

				assetParamsOutputPeer::addSelectColumns($criteria);
				if (!isset($this->lastassetParamsOutputCriteria) || !$this->lastassetParamsOutputCriteria->equals($criteria)) {
					$this->collassetParamsOutputs = assetParamsOutputPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastassetParamsOutputCriteria = $criteria;
		return $this->collassetParamsOutputs;
	}

	/**
	 * Get the associated assetParams object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     assetParams The associated assetParams object.
	 * @throws     PropelException
	 */
	public function getFlavorParams(PropelPDO $con = null)
	{
		if ($this->aassetParams === null && ($this->flavor_params_id !== null)) {
			$this->aassetParams = assetParamsPeer::retrieveByPk($this->flavor_params_id);
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   $this->aassetParams->addassets($this);
			 */
		}
		return $this->aassetParams;
	}
	
	public function getIsWeb()
	{
		return $this->hasTag(flavorParams::TAG_WEB);
	}

	public function setFromAssetParams($dbAssetParams)
	{
		parent::setFromAssetParams($dbAssetParams);
		
		$this->setBitrate($dbAssetParams->getVideoBitrate()+$dbAssetParams->getAudioBitrate());
		$this->setFrameRate($dbAssetParams->getFrameRate());
		$this->setVideoCodecId($dbAssetParams->getVideoCodec());
	}
	
		
    /**
     * (non-PHPdoc)
     * @see asset::setStatusLocalReady()
     */
    public function setStatusLocalReady()
	{	    
	    KalturaLog::debug('Setting local ready status for asset id ['.$this->getId().']');
	    $newStatus = asset::ASSET_STATUS_READY;
	    
	    $externalStorages = StorageProfilePeer::retrieveExternalByPartnerId($this->getPartnerId());
		foreach($externalStorages as $externalStorage)
		{
		    // check if storage profile should affect the asset ready status
		    if ($externalStorage->getReadyBehavior() != StorageProfileReadyBehavior::REQUIRED)
		    {
		        // current storage profile is not required for asset readiness - skipping
		        continue;
		    }
		    
		    // check if export should happen now or wait for another trigger
		    if (!$externalStorage->triggerFitsReadyAsset($this->getEntryId())) {
		        KalturaLog::debug('Asset id ['.$this->getId().'] is not ready to export to profile ['.$externalStorage->getId().']');
		        continue;
		    }
		    
		    // check if asset needs to be exported to the remote storage
		    if (!$externalStorage->shouldExportFlavorAsset($this))
		    {
    		    // check if asset is currently being exported to the remote storage
    		    if (!$externalStorage->isPendingExport($this))
    		    {
    		        KalturaLog::debug('Should not export asset id ['.$this->getId().'] to profile ['.$externalStorage->getId().']');
		        continue;
    		    }
    		    else
    		    {
    		        KalturaLog::debug('Asset id ['.$this->getId().'] is currently being exported to profile ['.$externalStorage->getId().']');
    		    }		        
		    }		    
		    
		    KalturaLog::debug('Asset id ['.$this->getId().'] is required to export to profile ['.$externalStorage->getId().'] - setting status to [EXPORTING]');
		    $newStatus = asset::ASSET_STATUS_EXPORTING;
		    break;
		}   
        KalturaLog::debug('Setting status to ['.$newStatus.']');
	    $this->setStatus($newStatus);
	}
	
	

    public function save(PropelPDO $con = null)
	{
	    // check if flavor asset is new before saving
	    $isNew = $this->isNew();
	    $statusModified = $this->isColumnModified(assetPeer::STATUS);
	    $flavorParamsIdModified = $this->isColumnModified(assetPeer::FLAVOR_PARAMS_ID);
	    
	    // save the asset
		$saveResult = parent::save();
		
		// update associated entry's flavorParamsId list
		if ( $this->getStatus() == self::ASSET_STATUS_READY && ($isNew || $statusModified || $flavorParamsIdModified) )
		{
		    $entry = $this->getentry();
		    if (!$entry) {
		        KalturaLog::err('Cannot get entry object for flavor asset id ['.$this->getId().']');
		    }
		    else {
		        KalturaLog::debug('Adding flavor params id ['.$this->getFlavorParamsId().'] to entry id ['.$entry->getId().']');
		        $entry->addFlavorParamsId($this->getFlavorParamsId());
		        
		        if($flavorParamsIdModified)
		        	$entry->removeFlavorParamsId($this->getColumnsOldValue(assetPeer::FLAVOR_PARAMS_ID));
		        
		        $entry->save();
		    }
		}

		// return the parent::save result
		return $saveResult;
	}
	
	public function getDownloadUrlWithExpiry($expiry, $useCdn = false)
	{
		$ksStr = "";
				
		$ksNeeded = true;
		$partnerId = $this->getPartnerId();
		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_ENTITLEMENT, $partnerId))
		{
			$invalidModerationStatuses = array(
				entry::ENTRY_MODERATION_STATUS_PENDING_MODERATION, 
				entry::ENTRY_MODERATION_STATUS_REJECTED
			);
			
			$entry = $this->getentry();
			if ($entry &&
				!in_array($entry->getModerationStatus(), $invalidModerationStatuses) &&
				($entry->getStartDate() === null || $entry->getStartDate(null) < time()) && 
				($entry->getEndDate() === null || $entry->getEndDate(null) > time() + 86400))
			{ 			
				$accessControl = $entry->getaccessControl();			
				if ($accessControl && !$accessControl->getRulesArray())
					$ksNeeded = false;
			}
		}
		
		if ($ksNeeded)
		{
			$partner = PartnerPeer::retrieveByPK($partnerId);
			$secret = $partner->getSecret();
			$privilege = ks::PRIVILEGE_DOWNLOAD.":".$this->getEntryId();
			$privilege .= ",".kSessionBase::PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY .":". $this->getEntryId();
			$result = kSessionUtils::startKSession($partnerId, $secret, null, $ksStr, $expiry, false, "", $privilege);
	
			if ($result < 0)
				throw new Exception("Failed to generate session for asset [".$this->getId()."] of type ". $this->getType());
		}
		
		$finalPath = $this->getFinalDownloadUrlPathWithoutKs();
		
		if ($ksStr)
			$finalPath .= "/ks/".$ksStr;
			
		// Gonen May 12 2010 - removing CDN URLs. see ticket 5135 in internal mantis
		// in order to avoid conflicts with access_control (geo-location restriction), we always return the requestHost (www_host from kConf)
		// and not the CDN host relevant for the partner.
		
		// Tan-Tan January 27 2011 - in some places we do need the cdn, I added a paramter useCdn to force it.
		if($useCdn)
		{
			// TODO in that case we should use the serve flavor and the url manager in order to support secured and signed urls
			$downloadUrl = myPartnerUtils::getCdnHost($partnerId) . $finalPath;
		}
		else
			$downloadUrl = requestUtils::getRequestHost() . $finalPath;
		
		return $downloadUrl;
	}
	
}
