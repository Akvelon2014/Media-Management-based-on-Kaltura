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
 * Subclass for representing a row from the 'flavor_asset' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class asset extends Baseasset implements ISyncableFile
{
	/**
	 * @deprecated use ASSET_STATUS_ERROR instead
	 */
	const FLAVOR_ASSET_STATUS_ERROR = -1;
	
	/**
	* @deprecated use ASSET_STATUS_QUEUED instead
	*/
	const FLAVOR_ASSET_STATUS_QUEUED = 0;
	
	/**
	* @deprecated use ASSET_STATUS_CONVERTING instead
	*/
	const FLAVOR_ASSET_STATUS_CONVERTING = 1;
	
	/**
	* @deprecated use ASSET_STATUS_READY instead
	*/
	const FLAVOR_ASSET_STATUS_READY = 2;
	
	/**
	* @deprecated use ASSET_STATUS_DELETED instead
	*/
	const FLAVOR_ASSET_STATUS_DELETED = 3;
	
	/**
	* @deprecated use ASSET_STATUS_NOT_APPLICABLE instead
	*/
	const FLAVOR_ASSET_STATUS_NOT_APPLICABLE = 4;
	
	/**
	* @deprecated use ASSET_STATUS_TEMP instead
	*/
	const FLAVOR_ASSET_STATUS_TEMP = 5; // used during conversion and should be deleted
	
	/**
	* @deprecated use ASSET_STATUS_WAIT_FOR_CONVERT instead
	*/
	const FLAVOR_ASSET_STATUS_WAIT_FOR_CONVERT = 6; // can't convert since the source is not ready yet, will be converted when the source is ready
	
	/**
	* @deprecated use ASSET_STATUS_IMPORTING instead
	*/
	const FLAVOR_ASSET_STATUS_IMPORTING = 7;
	
	/**
	* @deprecated use ASSET_STATUS_VALIDATING instead
	*/
	const FLAVOR_ASSET_STATUS_VALIDATING = 8;
	
	const ASSET_STATUS_ERROR = -1;
	const ASSET_STATUS_QUEUED = 0;
	const ASSET_STATUS_CONVERTING = 1;
	const ASSET_STATUS_READY = 2;
	const ASSET_STATUS_DELETED = 3;
	const ASSET_STATUS_NOT_APPLICABLE = 4;
	const ASSET_STATUS_TEMP = 5; // used during conversion and should be deleted
	const ASSET_STATUS_WAIT_FOR_CONVERT = 6; // can't convert since the source is not ready yet, will be converted when the source is ready
	const ASSET_STATUS_IMPORTING = 7;
	const ASSET_STATUS_VALIDATING = 8;
	const ASSET_STATUS_EXPORTING = 9;
	
	/**
	 * @deprecated use FILE_SYNC_ASSET_SUB_TYPE_ASSET instead
	 */
	const FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET = 1;
	
	/**
	* @deprecated use FILE_SYNC_ASSET_SUB_TYPE_CONVERT_LOG instead
	*/
	const FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_CONVERT_LOG = 2;
	
	
	const FILE_SYNC_ASSET_SUB_TYPE_ASSET = 1;
	const FILE_SYNC_ASSET_SUB_TYPE_CONVERT_LOG = 2;
	
	const CUSTOM_DATA_FIELD_PARTNER_DESCRIPTION = "partnerDescription";
	const CUSTOM_DATA_FIELD_PARTNER_DATA = "partnerData";
	
	public function copyToEntry($entryId = null, $partnerId = null)
	{
		$newFlavorAsset = $this->copy();
		//this is the first version of the new asset.
		$newFlavorAsset->setVersion(1);
		if($partnerId)
			$newFlavorAsset->setPartnerId($partnerId);
		if($entryId)
			$newFlavorAsset->setEntryId($entryId);
		$newFlavorAsset->save();
		
		$flavorParamsOutput = assetParamsOutputPeer::retrieveByAssetId($this->getId());
		if($flavorParamsOutput)
		{
			$newFlavorParamsOutput = $flavorParamsOutput->copy();
			$newFlavorParamsOutput->setPartnerId($newFlavorAsset->getPartnerId());
			$newFlavorParamsOutput->setEntryId($newFlavorAsset->getEntryId());
			$newFlavorParamsOutput->setFlavorAssetId($newFlavorAsset->getId());
			$newFlavorParamsOutput->save();
		}
		
		$mediaInfo = mediaInfoPeer::retrieveByFlavorAssetId($this->getId());
		if($mediaInfo)
		{
			$newMediaInfo = $mediaInfo->copy();
			$newMediaInfo->setFlavorAssetId($newFlavorAsset->getId());
			$newMediaInfo->setFlavorAssetVersion($newFlavorAsset->getVersion());
			$newMediaInfo->save();
		}
		
		$assetSyncKey = $this->getSyncKey(self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$convertLogSyncKey = $this->getSyncKey(self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_CONVERT_LOG);
		
		$newAssetSyncKey = $newFlavorAsset->getSyncKey(self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$newConvertLogSyncKey = $newFlavorAsset->getSyncKey(self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_CONVERT_LOG);
		
		if(kFileSyncUtils::fileSync_exists($assetSyncKey))
			kFileSyncUtils::softCopy($assetSyncKey, $newAssetSyncKey);

		if(kFileSyncUtils::fileSync_exists($convertLogSyncKey))
			kFileSyncUtils::softCopy($convertLogSyncKey, $newConvertLogSyncKey);
		
		return $newFlavorAsset;
	}
	
	public function save(PropelPDO $con = null)
	{
		if ($this->isNew())
		{
			$this->setId($this->calculateId());
		}
		parent::save($con);
	}

	/* (non-PHPdoc)
	 * @see lib/model/om/BaseflavorAsset#postUpdate()
	 */
	public function postUpdate(PropelPDO $con = null)
	{
		if ($this->alreadyInSave)
			return parent::postUpdate($con);
		
		$objectDeleted = false;
		if(
			($this->isColumnModified(assetPeer::STATUS) && $this->getStatus() == self::FLAVOR_ASSET_STATUS_DELETED)
			||
			($this->isColumnModified(assetPeer::DELETED_AT) && !is_null($this->getDeletedAt(null)))
		)
			$objectDeleted = true;
			
		$ret = parent::postUpdate($con);
		
		if($objectDeleted)
			kEventsManager::raiseEvent(new kObjectDeletedEvent($this));
			
		return $ret;
	}
	
	public function incrementVersion()
	{
		$version = $this->getVersion();
		$this->setVersion(is_null($version) ? 1 : $version + 1);
	}
	
	public function addTags(array $newTags)
	{
		$tags = $this->getTagsArray();
		foreach($newTags as $newTag)
			if(!in_array($newTag, $tags))
				$tags[] = $newTag;
				
		$this->setTagsArray($tags);
	}
	
	public function removeTags(array $tagsToRemove)
	{
		$tags = $this->getTagsArray();
		$newTags = array();
		foreach($tags as $tag)
			if(!in_array($tag, $tagsToRemove))
				$newTags[] = $tag;
				
		$this->setTagsArray($newTags);
	}
	
	
	private static function validateFileSyncSubType ( $sub_type )
	{
		$valid_sub_types = array(
			self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET,
			self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_CONVERT_LOG,
		);
		if (!in_array($sub_type, $valid_sub_types))
			throw new FileSyncException(FileSyncObjectType::FLAVOR_ASSET, $sub_type, $valid_sub_types);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see lib/model/ISyncableFile#getSyncKey()
	 */
	public function getSyncKey($sub_type, $version = null)
	{
		self::validateFileSyncSubType($sub_type);
		$key = new FileSyncKey();
		$key->object_type = FileSyncObjectType::FLAVOR_ASSET;
		$key->object_sub_type = $sub_type;
		$key->object_id = $this->getId();
		if ($version)
		{
			$key->version = $version;
		}
		else
		{
			switch ($sub_type)
			{
				case flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET:
					$key->version = $this->getVersion();
					break;
				case flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_CONVERT_LOG:
					$key->version = $this->getLogFileVersion();
					break;
			}
		}
		$key->partner_id = $this->getPartnerId();
		
		return $key;
	}

	
	
	/* (non-PHPdoc)
	 * @see lib/model/ISyncableFile#generateFileName()
	 */
	public function generateFileName( $sub_type, $version = null)
	{
		self::validateFileSyncSubType ( $sub_type );
		
		$entry = $this->getentry();
		$fileName = $entry->getId() . "_" . $this->getId() . "_$version";
				 
		switch($sub_type)
		{
			case self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET:
				$ext = '';
				if($this->getFileExt())
					$ext = '.' . $this->getFileExt();
				return $fileName . $ext;
				
			case self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_CONVERT_LOG:
				return "$fileName.conv.log";
		}
		
		return null;
	}

	/**
	 * (non-PHPdoc)
	 * @see lib/model/ISyncableFile#generateFilePathArr()
	 */
	public function generateFilePathArr($sub_type, $version = null)
	{
		self::validateFileSyncSubType ( $sub_type );
		$version = (is_null($version) ? $this->getVersion() : $version);
		
		$entry = entryPeer::retrieveByPKNoFilter($this->getEntryId());
		if(!$entry)
			throw new Exception("Could not find entry [" . $this->getEntryId() . "] for asset [" . $this->getId() . "]");
		
		$dir = (intval($entry->getIntId() / 1000000)).'/'.	(intval($entry->getIntId() / 1000) % 1000);
		$path =  "/content/entry/data/$dir/" . $this->generateFileName($sub_type, $version);

		return array(myContentStorage::getFSContentRootPath(), $path); 
	}

	/**
	 * Getting name of attachment
	 * @return string|null Attachment name or error
	 */
	public function getAttachmentName () {
		if ($this->getType() == AttachmentPlugin::getAssetTypeCoreValue(AttachmentAssetType::ATTACHMENT)) {
			return $this->getFromCustomData('filename');
		}
		return null;
	}

	/**
	 * Getting name of flavor parameters
	 * @return string|null Flavor parameters name or error
	 */
	public function getFlavorParamsName () {
		$flavorParamsId = $this->getFlavorParamsId();
		$flavorParams = assetParamsPeer::retrieveByPK($flavorParamsId);
		if ($flavorParams) {
			return $flavorParams->getName();
		}
		return null;
	}

	/**
	 * @var FileSync
	 */
	private $m_file_sync;
	
	/* (non-PHPdoc)
	 * @see lib/model/ISyncableFile#getFileSync()
	 */
	public function getFileSync ( )
	{
		return $this->m_file_sync; 
	}
	
	/* (non-PHPdoc)
	 * @see lib/model/ISyncableFile#setFileSync()
	 */
	public function setFileSync ( FileSync $file_sync )
	{
		 $this->m_file_sync = $file_sync;
	}
	
	private function calculateId()
	{
		$dc = kDataCenterMgr::getCurrentDc();
		for ($i = 0; $i < 10; $i++)
		{
			$id = $dc["id"].'_'.kString::generateStringId();
			$existingObject = assetPeer::retrieveById($id);
			
			if ($existingObject)
				KalturaLog::log(__METHOD__ . ": id [$id] already exists");
			else
				return $id;
		}
		
		throw new Exception("Could not find unique id for flavorAsset");
	}

	public function getFormat()
	{
		$assetParams = $this->getassetParams();
		if ($assetParams)
			return $assetParams->getFormat();
		else
			return null;
	}
	
	public function getExternalUrl($storageId)
	{
		$key = $this->getSyncKey(self::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$fileSync = kFileSyncUtils::getReadyExternalFileSyncForKey($key, $storageId);
		if(!$fileSync || $fileSync->getStatus() != FileSync::FILE_SYNC_STATUS_READY)
			return null;
		
		$storage = StorageProfilePeer::retrieveByPK($fileSync->getDc());
		if(!$storage)
			return null;
			
		$urlManager = kUrlManager::getUrlManagerByStorageProfile($fileSync->getDc(), $this->getEntryId());
		$urlManager->setFileExtension($this->getFileExt());
		
		$url = rtrim($storage->getDeliveryHttpBaseUrl(), "/") . "/". ltrim($urlManager->getFileSyncUrl($fileSync), "/");
		return $url;
	}
	
	public function getDownloadUrl($useCdn = false)
	{
		$syncKey = $this->getSyncKey(self::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		
		$fileSync = null;
		$serveRemote = false;
		$partner = PartnerPeer::retrieveByPK($this->getPartnerId());
		
		switch($partner->getStorageServePriority())
		{
			case StorageProfile::STORAGE_SERVE_PRIORITY_EXTERNAL_ONLY:
				$serveRemote = true;
				$fileSync = kFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
				if(!$fileSync || $fileSync->getStatus() != FileSync::FILE_SYNC_STATUS_READY)
					throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
				
				break;
			
			case StorageProfile::STORAGE_SERVE_PRIORITY_EXTERNAL_FIRST:
				$fileSync = kFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
				if($fileSync && $fileSync->getStatus() == FileSync::FILE_SYNC_STATUS_READY)
					$serveRemote = true;
				
				break;
			
			case StorageProfile::STORAGE_SERVE_PRIORITY_KALTURA_FIRST:
				$fileSync = kFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
				if($fileSync)
					break;
					
				$fileSync = kFileSyncUtils::getReadyExternalFileSyncForKey($syncKey);
				if(!$fileSync || $fileSync->getStatus() != FileSync::FILE_SYNC_STATUS_READY)
					throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
				
				$serveRemote = true;
				break;
			
			case StorageProfile::STORAGE_SERVE_PRIORITY_KALTURA_ONLY:
				$fileSync = kFileSyncUtils::getReadyInternalFileSyncForKey($syncKey);
				if(!$fileSync)
					throw new kCoreException("File sync not found: $syncKey", kCoreException::FILE_NOT_FOUND);
				
				break;
		}
		
		if($serveRemote && $fileSync)
			return $fileSync->getExternalUrl($this->getEntryId());
		
		return $this->getDownloadUrlWithExpiry(86400, $useCdn);
	}
	
	public function getDownloadUrlWithExpiry($expiry, $useCdn = false)
	{
		$ksStr = "";
				
		$partnerId = $this->getPartnerId();
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		$secret = $partner->getSecret();
		$privilege = ks::PRIVILEGE_DOWNLOAD.":".$this->getEntryId();
		$privilege .= ",".kSessionBase::PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY .":". $this->getEntryId();
		$result = kSessionUtils::startKSession($partnerId, $secret, null, $ksStr, $expiry, false, "", $privilege);
	
		if ($result < 0)
			throw new Exception("Failed to generate session for asset [".$this->getId()."] of type ". $this->getType());

		$finalPath = $this->getFinalDownloadUrlPathWithoutKs();
		
		$finalPath .= "/ks/".$ksStr;
			
		// Gonen May 12 2010 - removing CDN URLs. see ticket 5135 in internal mantis
		// in order to avoid conflicts with access_control (geo-location restriction), we always return the requestHost (www_host from kConf)
		// and not the CDN host relevant for the partner.
		
		// Tan-Tan January 27 2011 - in some places we do need the cdn, I added a paramter useCdn to force it.
		if($useCdn)
			$downloadUrl = myPartnerUtils::getCdnHost($partnerId) . $finalPath;
		else
			$downloadUrl = requestUtils::getRequestHost() . $finalPath;
		
		return $downloadUrl;
	}
	
	protected function getFinalDownloadUrlPathWithoutKs()
	{
		$finalPath = myPartnerUtils::getUrlForPartner($this->getPartnerId(),$this->getPartnerId()*100).
					"/download".
					"/entry_id/".$this->getEntryId().
					"/flavor/".$this->getId();
		
		return $finalPath;
	}
	
	public function hasTag($v)
	{
		$tags = explode(',', $this->getTags());
		return in_array($v, $tags);
	}
	
	public function setTagsArray(array $tags)
	{
		$this->setTags(implode(',', $tags));
	}
	
	public function getTagsArray()
	{
		if(!strlen(trim($this->getTags())))
			return array();
			
		return explode(',', $this->getTags());
	}
	
	public function getTags()
	{
		return trim(parent::getTags());
	}

	/**
	 * @return flavorParamsOutput
	 */
	public function getFlavorParamsOutput()
	{
		return assetParamsOutputPeer::retrieveByAsset($this);
	}
	
	public function getLogFileVersion()
	{
		return $this->getFromCustomData("logFileVersion", null, 0);
	}
	
	public function incLogFileVersion()
	{
		$this->incInCustomData("logFileVersion", 1);
	}

	public function getCacheInvalidationKeys()
	{
		return array("flavorAsset:id=".$this->getId(), "flavorAsset:entryId=".$this->getEntryId());
	}
	
	public function getPartnerDescription()			{return $this->getFromCustomData(self::CUSTOM_DATA_FIELD_PARTNER_DESCRIPTION);}
	public function setPartnerDescription($v)		{$this->putInCustomData(self::CUSTOM_DATA_FIELD_PARTNER_DESCRIPTION, $v);}
	
	public function getPartnerData()		{return $this->getFromCustomData(self::CUSTOM_DATA_FIELD_PARTNER_DATA);}
	public function setPartnerData($v)		{$this->putInCustomData(self::CUSTOM_DATA_FIELD_PARTNER_DATA, $v);}

	public function setFromAssetParams($dbAssetParams)
	{
		$this->setContainerFormat($dbAssetParams->getFormat());
		$this->setHeight($dbAssetParams->getHeight());
		$this->setWidth($dbAssetParams->getWidth());
		$this->addTags($dbAssetParams->getTagsArray());
	}
	
	/**
	 * @return array of asset status values that mean the asset is at post conversion status (ready locally)
	 * Can be overwritten for specific asset types
	 */
	public function isLocalReadyStatus()
	{
		$status = $this->getStatus();
	    if($status == asset::ASSET_STATUS_EXPORTING || $status == asset::ASSET_STATUS_READY)
	    	return true;
	    	
	    return false;
	}
	
    /**
     * Set the asset status to a locally ready status (READY, EXPORTING) according to the required jobs to perform on the asset
     */
    public function setStatusLocalReady()
	{
	    parent::setStatus(asset::ASSET_STATUS_READY);
	}
	
}
