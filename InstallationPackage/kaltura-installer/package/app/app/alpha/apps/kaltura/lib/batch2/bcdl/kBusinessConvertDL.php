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

class kBusinessConvertDL
{
	/**
	 * @param entry $entry
	 * @param entry $tempEntry
	 */
	public static function replaceEntry(entry $entry, entry $tempEntry = null)
	{
		KalturaLog::debug("in replaceEntry");
		
		if(!$tempEntry)
			$tempEntry = entryPeer::retrieveByPK($entry->getReplacingEntryId());
			
		if(!$tempEntry)
		{
			KalturaLog::err("Temp entry id [" . $entry->getReplacingEntryId() . "] not found");
			return;
		}
		//Extract all assets of the temp entry
		$tempAssets = assetPeer::retrieveByEntryId($tempEntry->getId());
		
		
		//Extract all assets of the existing entry
		$oldAssets = assetPeer::retrieveByEntryId($entry->getId());
		KalturaLog::debug("num of old assets: ".count($oldAssets));
		$newAssets = array();
		
		//Loop which creates a mapping between the new assets' paramsId and their type to the asset itself
		foreach($tempAssets as $newAsset)
		{
			if($newAsset->getStatus() != asset::FLAVOR_ASSET_STATUS_READY)
			{
				KalturaLog::debug("Do not add new asset [" . $newAsset->getId() . "] to flavor [" . $newAsset->getFlavorParamsId() . "] status [" . $newAsset->getStatus() . "]");
				continue;	
			}
			
			//If doesn't exist - create a new array for the current asset's type.
			if (!isset($newAssets[$newAsset->getType()]))
			{
				$newAssets[$newAsset->getType()] = array();
			}
			
			if($newAsset->getFlavorParamsId() || $newAsset instanceof flavorAsset)
			{
				$newAssets[$newAsset->getType()][$newAsset->getFlavorParamsId()] = $newAsset;
				KalturaLog::debug("Added new asset [" . $newAsset->getId() . "] for asset params [" . $newAsset->getFlavorParamsId() . "]");
			}
			else
			{
				$newAssets[$newAsset->getType()]['asset_' . count($newAssets[$newAsset->getType()])] = $newAsset;
				KalturaLog::debug("Added new asset [" . $newAsset->getId() . "] with no asset params");
			}
		}
		
		$saveEntry = false;
		$defaultThumbAssetNew = null;
		foreach($oldAssets as $oldAsset)
		{
			/* @var $oldAsset asset */

			kFileSyncUtils::clearWAMSDataForKey($oldAsset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET));

			//If the newAssets map contains an asset of the same type and paramsId as the current old asset,
			// re-link the old asset to the new asset.
			if(isset($newAssets[$oldAsset->getType()]) && isset($newAssets[$oldAsset->getType()][$oldAsset->getFlavorParamsId()]))
			{
				$newAsset = $newAssets[$oldAsset->getType()][$oldAsset->getFlavorParamsId()];
				
				/* @var $newAsset asset */
				KalturaLog::debug("Create link from new asset [" . $newAsset->getId() . "] to old asset [" . $oldAsset->getId() . "] for flavor [" . $oldAsset->getFlavorParamsId() . "]");

				if($oldAsset instanceof flavorAsset)
				{
					$oldAsset->setBitrate($newAsset->getBitrate());
					$oldAsset->setFrameRate($newAsset->getFrameRate());
					$oldAsset->setVideoCodecId($newAsset->getVideoCodecId());
				}
				$oldAsset->setWidth($newAsset->getWidth());
				$oldAsset->setHeight($newAsset->getHeight());
				$oldAsset->setContainerFormat($newAsset->getContainerFormat());
				$oldAsset->setSize($newAsset->getSize());
				$oldAsset->setFileExt($newAsset->getFileExt());
				$oldAsset->setTags($newAsset->getTags());
				$oldAsset->setDescription($newAsset->getDescription());
				$oldAsset->incrementVersion();
				$oldAsset->setStatusLocalReady();
				$oldAsset->save();
				
				$oldFileSync = $oldAsset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
				$newFileSync = $newAsset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);

				kFileSyncUtils::createSyncFileLinkForKey($oldFileSync, $newFileSync);

				$newFlavorMediaInfo = mediaInfoPeer::retrieveByFlavorAssetId($newAsset->getId());
				if($newFlavorMediaInfo)
				{
					$oldFlavorNewMediaInfo = $newFlavorMediaInfo->copy();
					$oldFlavorNewMediaInfo->setFlavorAssetId($oldAsset->getId());
					$oldFlavorNewMediaInfo->setFlavorAssetVersion($oldAsset->getVersion());
					$oldFlavorNewMediaInfo->save();
				}
				unset($newAssets[$oldAsset->getType()][$oldAsset->getFlavorParamsId()]);
				
				if ($oldAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
				{
					$defaultThumbAssetNew = $oldAsset;
					KalturaLog::debug("Nominating ThumbAsset [".$oldAsset->getId()."] as the default ThumbAsset after replacent");
				}
				
			}	
			//If the old asset is not set for replacement by its paramsId and type, delete it.
			elseif($oldAsset instanceof flavorAsset || $oldAsset instanceof thumbAsset)
			{
				KalturaLog::debug("Delete old asset [" . $oldAsset->getId() . "] for paramsId [" . $oldAsset->getFlavorParamsId() . "]");

				$oldAsset->setStatus(flavorAsset::ASSET_STATUS_DELETED);
				$oldAsset->setDeletedAt(time());
				$oldAsset->save();

				$entry->removeFlavorParamsId($oldAsset->getFlavorParamsId());
				$saveEntry = true;
			}
		}
		
		foreach($newAssets as $newAssetsByTypes)
		{
			foreach ($newAssetsByTypes as $newAsset)
			{
				$createdAsset = $newAsset->copyToEntry($entry->getId(), $entry->getPartnerId());
				KalturaLog::debug("Copied from new asset [" . $newAsset->getId() . "] to copied asset [" . $createdAsset->getId() . "] for flavor [" . $newAsset->getFlavorParamsId() . "]");
				
				if ($createdAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
				{
					$defaultThumbAssetNew = $newAsset;
					KalturaLog::debug("Nominating ThumbAsset [".$newAsset->getId()."] as the default ThumbAsset after replacent");
				}
			}
		}
		
		if ($defaultThumbAssetNew)
		{
			kBusinessConvertDL::setAsDefaultThumbAsset($defaultThumbAssetNew);
			kalturalog::debug("Setting ThumbAsset [". $defaultThumbAssetNew->getId() ."] as the default ThumbAsset");
		}
		else 
		{		
			kalturalog::debug("No default ThumbAsset found for replacing entry [". $tempEntry->getId() ."]");
			$entry->setThumbnail(".jpg"); // thumbnailversion++
			$entry->save();
			$tempEntrySyncKey = $tempEntry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB);
			$realEntrySyncKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB);
			kFileSyncUtils::createSyncFileLinkForKey($realEntrySyncKey, $tempEntrySyncKey);
		}
		
		$entry->setDimensions($tempEntry->getWidth(), $tempEntry->getHeight());
		$entry->setLengthInMsecs($tempEntry->getLengthInMsecs());
		$entry->setConversionProfileId($tempEntry->getConversionProfileId());
		$entry->setConversionQuality($tempEntry->getConversionQuality());
		$entry->setReplacingEntryId(null);
		$entry->setReplacementStatus(entryReplacementStatus::NONE);
		$entry->setStatus($tempEntry->getStatus());	
		$entry->save();
		
		myEntryUtils::deleteEntry($tempEntry,null,true);
		
		$te = new TrackEntry();
		$te->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_REPLACED_ENTRY);
		$te->setEntryId($entry->getId());
		$te->setParam1Str($tempEntry->getId());
		$te->setDescription(__METHOD__ . "[" . __LINE__ . "]");
		TrackEntry::addTrackEntry($te);
	}
	
	public static function setAsDefaultThumbAsset($thumbAsset)
	{
		/* @var $thumbAsset thumbAsset */
		$entry = $thumbAsset->getentry();
		if (!$entry)
			throw new kCoreException("Could not retrieve entry ID [".$thumbAsset->getEntryId()."] from ThumbAsset ID [".$thumbAsset->getId()."]",KalturaErrors::ENTRY_ID_NOT_FOUND);
		
		$entryThumbAssets = assetPeer::retrieveThumbnailsByEntryId($thumbAsset->getEntryId());			
		foreach($entryThumbAssets as $entryThumbAsset)
		{
			if($entryThumbAsset->getId() == $thumbAsset->getId())
				continue;				
			if(!$entryThumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
				continue;
			$entryThumbAsset->removeTags(array(thumbParams::TAG_DEFAULT_THUMB));
			$entryThumbAsset->save();
		}
		
		if(!$thumbAsset->hasTag(thumbParams::TAG_DEFAULT_THUMB))
		{
			/* @var $thumbAsset KalturaThumbAsset */
			$thumbAsset->addTags(array(thumbParams::TAG_DEFAULT_THUMB));
			$thumbAsset->save();
			KalturaLog::debug("Setting entry [". $thumbAsset->getEntryId() ."] default ThumbAsset to [". $thumbAsset->getId() ."]");
		}
		
		$entry->setThumbnail(".jpg");
		$entry->setCreateThumb(false);
		$entry->save();
		
		$thumbSyncKey = $thumbAsset->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		$entrySyncKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_THUMB);
		kFileSyncUtils::createSyncFileLinkForKey($entrySyncKey, $thumbSyncKey);
	}
	
	public static function parseFlavorDescription(flavorParamsOutputWrap $flavor)
	{
		$description = '';
		if(is_array($flavor->_errors) && count($flavor->_errors))
		{
			$errDesc = '';
			foreach($flavor->_errors as $section => $errors)
				$errDesc .= "$section errors: " . join("; ", $errors) . "\n";
				
			KalturaLog::log("Flavor errors: $errDesc");
			$description .= $errDesc;
		}
			
		if(is_array($flavor->_warnings) && count($flavor->_warnings))
		{
			$errDesc = '';
			foreach($flavor->_warnings as $section => $errors)
				$errDesc .= "$section warnings: " . join("; ", $errors) . "\n";
				
			KalturaLog::log("Flavor warnings: $errDesc");
			$description .= $errDesc;
		}
		return $description;
	}
	
	protected static function isFlavorLower(flavorParams $target, flavorParams $compare)
	{
		// currently check only the bitrate
		return ($target->getVideoBitrate() < $compare->getVideoBitrate());
	}
	
	public static function filterTagFlavors(array $flavors)
	{
		KalturaLog::log("Filter Tag Flavors, " . count($flavors) . " flavors supplied");
		
		// check if there is a complete flavor
		$hasComplied = false;
		$hasForced = false;
		$originalFlavorParamsIds = array();
		foreach($flavors as $flavorParamsId => $flavor)
		{
			$originalFlavorParamsIds[] = $flavor->getFlavorParamsId();
			if(!$flavor->_isNonComply)
				$hasComplied = true;
				
			if($flavor->_force)
				$hasForced = true;
		}
		
		$originalFlavorParams = array();
		$dbOriginalFlavorParams = assetParamsPeer::retrieveByPKs($originalFlavorParamsIds);
		foreach($dbOriginalFlavorParams as $dbFlavorParams)
			$originalFlavorParams[$dbFlavorParams->getId()] = $dbFlavorParams;
		
		// return only complete flavors
		if($hasComplied)
			KalturaLog::log("Has complied flavors");
		if($hasForced)
			KalturaLog::log("Has forced flavors");
		if($hasComplied || $hasForced)
			return $flavors;
		
		// find the lowest flavor
		$lowestFlavorParamsId = null;
		foreach($flavors as $flavorParamsId => $flavor)
		{
			if(!$flavor->IsValid())
				continue;
				
			// is lower than the selected
			if(!isset($originalFlavorParams[$flavor->getFlavorParamsId()]))
				continue;
				
			$currentOriginalFlavor = $originalFlavorParams[$flavor->getFlavorParamsId()];
			
			// is first flavor to check
			if(is_null($lowestFlavorParamsId))
			{				
				$lowestFlavorParamsId = $flavorParamsId;
				continue;
			}
			
			$lowestOriginalFlavor = $originalFlavorParams[$flavors[$lowestFlavorParamsId]->getFlavorParamsId()];
			if(self::isFlavorLower($currentOriginalFlavor, $lowestOriginalFlavor))
				$lowestFlavorParamsId = $flavorParamsId;
		}
		
		if($lowestFlavorParamsId)
		{
			KalturaLog::log("Lowest flavor selected [$lowestFlavorParamsId]");
			$flavors[$lowestFlavorParamsId]->_create_anyway = true;
		}
		
		return $flavors;
	}
	
	/**
	 * compareFlavors compares to flavorParamsOutput and decide which should be performed first
	 * 
	 * @param flavorParamsOutput $a
	 * @param flavorParamsOutput $b
	 */
	public static function compareFlavors(flavorParamsOutput $a, flavorParamsOutput $b)
	{
		$flavorA = $a->getId();
		$flavorB = $b->getId();
	
		if($a->getReadyBehavior() == flavorParamsConversionProfile::READY_BEHAVIOR_NO_IMPACT && $b->getReadyBehavior() > flavorParamsConversionProfile::READY_BEHAVIOR_NO_IMPACT)
		{
			KalturaLog::debug("flavor[$flavorB] before flavor[$flavorA] at line[" . __LINE__ . "]");
			return 1;
		}
		
		if($a->getReadyBehavior() > flavorParamsConversionProfile::READY_BEHAVIOR_NO_IMPACT && $b->getReadyBehavior() == flavorParamsConversionProfile::READY_BEHAVIOR_NO_IMPACT)
		{
			KalturaLog::debug("flavor[$flavorA] before flavor[$flavorB] at line[" . __LINE__ . "]");
			return -1;
		}
			
		if($a->getReadyBehavior() == flavorParamsConversionProfile::READY_BEHAVIOR_OPTIONAL && $b->getReadyBehavior() == flavorParamsConversionProfile::READY_BEHAVIOR_REQUIRED)
		{
			KalturaLog::debug("flavor[$flavorB] before flavor[$flavorA] at line[" . __LINE__ . "]");
			return 1;
		}
			
		if($a->getReadyBehavior() == flavorParamsConversionProfile::READY_BEHAVIOR_REQUIRED && $b->getReadyBehavior() == flavorParamsConversionProfile::READY_BEHAVIOR_OPTIONAL)
		{
			KalturaLog::debug("flavor[$flavorA] before flavor[$flavorB] at line[" . __LINE__ . "]");
			return -1;
		}
			
		if($a->getVideoBitrate() > $b->getVideoBitrate())
		{
			KalturaLog::debug("flavor[$flavorB] before flavor[$flavorA] at line[" . __LINE__ . "]");
			return 1;
		}
			
		KalturaLog::debug("flavor[$flavorA] before flavor[$flavorB] at line[" . __LINE__ . "]");
		return -1;
	}
	
	/**
	 * compareFlavorsByHeight compares to flavorParamsOutput objects by height
	 * 
	 * @param flavorParamsOutput $a
	 * @param flavorParamsOutput $b
	 */
	public static function compareFlavorsByHeight(flavorParamsOutput $a, flavorParamsOutput $b)
	{
		if($a->getHeight() > $b->getHeight())
			return 1;
			
		return -1;
	}
}