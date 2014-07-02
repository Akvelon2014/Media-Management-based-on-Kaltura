<?php
/**
 * @package Core
 * @subpackage storage.Level3
 */
class kLevel3UrlManager extends kUrlManager
{
	/**
	 * @return kUrlTokenizer
	 */
	public function getTokenizer()
	{
		switch ($this->protocol)
		{
		case StorageProfile::PLAY_FORMAT_HTTP:
   		    $name = isset($this->params['http_auth_param_name']) ? $this->params['http_auth_param_name'] : "h";
			$key = isset($this->params['http_auth_key']) ? $this->params['http_auth_key'] : false;
			$gen = isset($this->params['http_auth_gen']) ? $this->params['http_auth_gen'] : false;
			$window = 0;
			$entry = entryPeer::retrieveByPK($this->entryId);
			if ($entry && $entry->getSecurityPolicy())
				$window = 30;
			if ($name && $key !== false && $gen !== false)
				return new kLevel3UrlTokenizer($name, $key, $gen, false, $window);
			break;

		case StorageProfile::PLAY_FORMAT_RTMP:
		    $name = isset($this->params['rtmp_auth_param_name']) ? $this->params['rtmp_auth_param_name'] : "h";
		    $key = isset($this->params['rtmp_auth_key']) ? $this->params['rtmp_auth_key'] : false;
		    $gen = isset($this->params['rtmp_auth_gen']) ? $this->params['rtmp_auth_gen'] : false;
			if ($name && $key !== false && $gen !== false)
				return new kLevel3UrlTokenizer($name, $key, $gen, true);
			break;
		}
		return null;
	}
	
	/**
	 * @param flavorAsset $flavorAsset
	 * @return string
	 */
	protected function doGetFlavorAssetUrl(flavorAsset $flavorAsset)
	{
		$entry = $flavorAsset->getentry();
		$partnerId = $entry->getPartnerId();
		$subpId = $entry->getSubpId();
		$flavorAssetId = $flavorAsset->getId();
		$partnerPath = myPartnerUtils::getUrlForPartner($partnerId, $subpId);
		
		$this->setFileExtension($flavorAsset->getFileExt());

		$versionString = $this->getFlavorVersionString($flavorAsset);
		$url = "$partnerPath/serveFlavor/entryId/".$flavorAsset->getEntryId()."{$versionString}/flavorId/$flavorAssetId";
		
		if($this->clipTo)
			$url .= "/clipTo/$this->clipTo";

		if($this->extention)
			$url .= "/name/a.$this->extention";
					
		if($this->protocol != StorageProfile::PLAY_FORMAT_RTMP)
		{	
			$url .= '?novar=0';
				
			if ($entry->getSecurityPolicy())
			{
				$url = "/s$url";
			}
		
			$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			$seekFromBytes = $this->getSeekFromBytes(kFileSyncUtils::getLocalFilePathForKey($syncKey));
			if($seekFromBytes)
				$url .= '&start=' . $seekFromBytes;
		}
		else
		{
			if($this->extention && strtolower($this->extention) != 'flv' ||
				$this->containerFormat && strtolower($this->containerFormat) != 'flash video')
				$url = "mp4:$url";
		}
				
		$url = str_replace('\\', '/', $url);
		return $url;
	}
	
	/**
	 * @param FileSync $fileSync
	 * @return string
	 */
	protected function doGetFileSyncUrl(FileSync $fileSync)
	{
		$url = parent::doGetFileSyncUrl($fileSync);
		if (in_array($fileSync->getPartnerId(), array(666132,628012,357521,560751)) && kString::beginsWith($url, "mp4:"))
			$url .= ".mp4";
						
		return $url;
	}
}
