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
 * @package Core
 * @subpackage externalWidgets
 */
class downloadAction extends sfAction
{
	/**
	 * Will forward to the regular swf player according to the widget_id 
	 */
	public function execute()
	{
		$entryId = $this->getRequestParameter("entry_id");
		$flavorId = $this->getRequestParameter("flavor");
		$fileName = $this->getRequestParameter("file_name");
		$fileName = basename($fileName);
		$ksStr = $this->getRequestParameter("ks");
		$referrer = $this->getRequestParameter("referrer");
		$referrer = base64_decode($referrer);
		if (!is_string($referrer)) // base64_decode can return binary data
			$referrer = "";
			
		$entry = null;
		
		if($ksStr)
		{
			try {
				kCurrentContext::initKsPartnerUser($ksStr);
			}
			catch (Exception $ex)
			{
				KExternalErrors::dieError(KExternalErrors::INVALID_KS);	
			}
		}
		else
		{
			$entry = kCurrentContext::initPartnerByEntryId($entryId);
			if(!$entry)
				KExternalErrors::dieError(KExternalErrors::ENTRY_NOT_FOUND);
		}
		
		kEntitlementUtils::initEntitlementEnforcement();
		
		if (!$entry)
		{
			$entry = entryPeer::retrieveByPK($entryId);
			
			if(!$entry)
				KExternalErrors::dieError(KExternalErrors::ENTRY_NOT_FOUND);
		}
		else
		{
			if(!kEntitlementUtils::isEntryEntitled($entry))
				KExternalErrors::dieError(KExternalErrors::ENTRY_NOT_FOUND);
		}
		
		myPartnerUtils::blockInactivePartner($entry->getPartnerId());
			
		$securyEntryHelper = new KSecureEntryHelper($entry, $ksStr, $referrer, accessControlContextType::DOWNLOAD);
		$securyEntryHelper->validateForDownload($entry, $ksStr);
		
		$flavorAsset = null;

		if ($flavorId) 
		{
			// get flavor asset
			$flavorAsset = assetPeer::retrieveById($flavorId);
			if (is_null($flavorAsset) || $flavorAsset->getStatus() != flavorAsset::FLAVOR_ASSET_STATUS_READY)
				KExternalErrors::dieError(KExternalErrors::FLAVOR_NOT_FOUND);
			
			// the request flavor should belong to the requested entry
			if ($flavorAsset->getEntryId() != $entryId)
				KExternalErrors::dieError(KExternalErrors::FLAVOR_NOT_FOUND);
		}
		else // try to find some flavor
		{
			$flavorAsset = assetPeer::retrieveBestPlayByEntryId($entry->getId());
		}

		// Gonen 26-04-2010: in case entry has no flavor with 'mbr' tag - we return the source
		if(!$flavorAsset && ($entry->getMediaType() == entry::ENTRY_MEDIA_TYPE_VIDEO || $entry->getMediaType() == entry::ENTRY_MEDIA_TYPE_AUDIO))
		{
			$flavorAsset = assetPeer::retrieveOriginalByEntryId($entryId);
		}
		
		if ($flavorAsset)
		{
			$syncKey = $this->getSyncKeyAndForFlavorAsset($entry, $flavorAsset);
		}
		else
		{
			$syncKey = $this->getBestSyncKeyForEntry($entry);
		}
		
		if (is_null($syncKey))
			KExternalErrors::dieError(KExternalErrors::FILE_NOT_FOUND);
			
		$this->handleFileSyncRedirection($syncKey);

		$filePath = kFileSyncUtils::getReadyLocalFilePathForKey($syncKey);
		$wamsAssetId = kFileSyncUtils::getWamsAssetIdForKey($syncKey);
		$wamsURL = kFileSyncUtils::getWamsURLForKey($syncKey);

		list($fileBaseName, $fileExt) = $this->getFileName($entry, $flavorAsset);

		if (!$fileName)
			$fileName = $fileBaseName;
		
		if ($fileExt && !is_dir($filePath))
			$fileName = $fileName . '.' . $fileExt;
			
		
		//enable downloading file_name which inside the flavor asset directory 
		if(is_dir($filePath))
			$filePath = $filePath.DIRECTORY_SEPARATOR.$fileName;
		$this->dumpFile($filePath, $fileName, $wamsAssetId, $wamsURL);
		
		die(); // no view
	}
	
	private function getFileName(entry $entry, flavorAsset $flavorAsset = null)
	{
		$fileExt = "";
		$fileBaseName = $entry->getName();
		if ($flavorAsset)
		{
			$flavorParams = $flavorAsset->getFlavorParams();
			if ($flavorParams)
				$fileBaseName = ($fileBaseName . " (" . $flavorParams->getName() . ")");
					
			$fileExt = $flavorAsset->getFileExt();
		}
		else
		{
			$syncKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
			list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
			if ($fileSync)
				$fileExt = $fileSync->getFileExt();
		}
		
		return array($fileBaseName, $fileExt);
	}
	
	private function getSyncKeyAndForFlavorAsset(entry $entry, flavorAsset $flavorAsset)
	{
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		return $syncKey;
	}
	
	private function getBestSyncKeyForEntry(entry $entry)
	{
		$entryType = $entry->getType();
		$entryMediaType = $entry->getMediaType();
		$syncKey = null;
		switch($entryType)
		{
			case entryType::MEDIA_CLIP: 
				switch ($entryMediaType)
				{
					case entry::ENTRY_MEDIA_TYPE_IMAGE:
						$syncKey = $entry->getSyncKey(entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA);
						break;
				}
				break;
		}
		
		return $syncKey;
	}
	
	private static function encodeUrl($url)
	{
		return str_replace(array('?', '|', '*', '\\', '/' , '>' , '<', '&', '[', ']'), '_', $url);
	}
	
	private function dumpFile($file_path, $file_name, $wams_asset_id = null, $wams_url = null)
	{
		$relocate = $this->getRequestParameter("relocate");
		$directServe = $this->getRequestParameter("direct_serve");

		if (!$relocate)
		{
			$url = $_SERVER["REQUEST_URI"];
			if (strpos($url, "?") !== false) // when query string exists, just remove it (otherwise it might cause redirect loops)
			{
				$url .= "&relocate=";
			}
			else
			{
				$url .= "/relocate/";
			}
				
			$url .= $this->encodeUrl($file_name);

			kFile::cacheRedirect($url);

			header("Location: {$url}");
			die;
		}
		else
		{
			if(!$directServe)
				header("Content-Disposition: attachment; filename=\"$file_name\"");

			if (!empty($wams_asset_id)) {
				$fileSync = FileSyncPeer::retrieveByWamsAssetId($wams_asset_id);
				kWAMS::getInstance($fileSync->getPartnerId())->dumpFile($wams_asset_id, pathinfo($file_name, PATHINFO_EXTENSION));
			}
			else {
				$mime_type = kFile::mimeType($file_path);
				kFile::dumpFile($file_path, $mime_type);
			}
		}
	}

	private function handleFileSyncRedirection(FileSyncKey $syncKey)
	{
		list($fileSync, $local) = kFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false);
		
		if (is_null($fileSync))
			KExternalErrors::dieError(KExternalErrors::FILE_NOT_FOUND);
			
		if (!$local)
		{
			$remote_url = kDataCenterMgr::getRedirectExternalUrl($fileSync);
			$this->redirect($remote_url);
		}
	}
}
