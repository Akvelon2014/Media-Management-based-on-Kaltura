<?php
/**
 * @package plugins.tvComDistribution
 * @subpackage lib
 */
class TVComFeed
{

	/**
	 * @var DOMDocument
	 */
	protected $doc;
	
	/**
	 * @var DOMXPath
	 */
	protected $xpath;
	
	/**
	 * @var DOMElement
	 */
	protected $item;
	
	/**
	 * @var TVComDistributionProfile
	 */
	protected $distributionProfile;
	
	/**
	 * @param $templateName
	 * @param $distributionProfile
	 */
	public function __construct($templateName)
	{
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml/' . $templateName;
		$this->doc = new KDOMDocument();
		$this->doc->load($xmlTemplate);
		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');
		$this->xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');
		
		$node = $this->xpath->query('/rss/channel/item')->item(0);
		$this->item = $node->cloneNode(true);
		$node->parentNode->removeChild($node);
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function setNodeValue($xpath, $value, DOMNode $contextnode = null)
	{
		if ($contextnode)
			$node = $this->xpath->query($xpath, $contextnode)->item(0);
		else 
			$node = $this->xpath->query($xpath)->item(0);
		if (!is_null($node))
		{
			// if CDATA inside, set the value of CDATA
			if ($node->childNodes->length > 0 && $node->childNodes->item(0)->nodeType == XML_CDATA_SECTION_NODE)
				$node->childNodes->item(0)->nodeValue = $value;
			else
				$node->nodeValue = $value;
		}
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function getNodeValue($xpath)
	{
		$node = $this->xpath->query($xpath)->item(0);
		if (!is_null($node))
			return $node->nodeValue;
		else
			return null;
	}
	
	/**
	 * @param TVComDistributionProfile $profile
	 */
	public function setDistributionProfile(TVComDistributionProfile $profile)
	{
		$this->distributionProfile = $profile;
		
		$this->setNodeValue('/rss/channel/title', $profile->getFeedTitle());
		$this->setNodeValue('/rss/channel/link', htmlentities($profile->getFeedLink()));
		$this->setNodeValue('/rss/channel/description', $profile->getFeedDescription());
		$this->setNodeValue('/rss/channel/language', $profile->getFeedLanguage());
		$this->setNodeValue('/rss/channel/copyright', $profile->getFeedCopyright());
		$this->setNodeValue('/rss/channel/image/title', $profile->getFeedImageTitle());
		$this->setNodeValue('/rss/channel/image/url', $profile->getFeedImageUrl());
		$this->setNodeValue('/rss/channel/image/link', $profile->getFeedImageLink());
		$this->setNodeValue('/rss/channel/image/width', $profile->getFeedImageWidth());
		$this->setNodeValue('/rss/channel/image/height', $profile->getFeedImageHeight());
	}
	
	/**
	 * @param array $values
	 */
	public function addItem(array $values, flavorAsset $flavorAsset = null, thumbAsset $thumbAsset = null,array $additionalAssets = null)
	{
		$item = $this->item->cloneNode(true);
		$channelNode = $this->xpath->query('/rss/channel', $item)->item(0);
		$channelNode->appendChild($item);
		
		$pubDate = date('c', $values[TVComDistributionField::ITEM_PUB_DATE]);
		$expDate = date('c', $values[TVComDistributionField::ITEM_EXP_DATE]);
		$node = $this->setNodeValue('guid', $values[TVComDistributionField::GUID_ID], $item);
		$node = $this->setNodeValue('pubDate', $pubDate, $item);
		$node = $this->setNodeValue('expDate', $expDate, $item);
		$node = $this->setNodeValue('link', $values[TVComDistributionField::ITEM_LINK], $item);
		$node = $this->setNodeValue('media:group/media:title', $values[TVComDistributionField::MEDIA_TITLE], $item);
		$node = $this->setNodeValue('media:group/media:description', $values[TVComDistributionField::MEDIA_DESCRIPTION], $item);
		$node = $this->setNodeValue('media:group/media:keywords', $values[TVComDistributionField::MEDIA_KEYWORDS], $item);
		$node = $this->setNodeValue('media:group/media:copyright', $values[TVComDistributionField::MEDIA_COPYRIGHT], $item);
		$node = $this->setNodeValue('media:group/media:rating', $values[TVComDistributionField::MEDIA_RATING], $item);
		$node = $this->setNodeValue('media:group/media:restriction/@relationship', $values[TVComDistributionField::MEDIA_RESTRICTION_TYPE], $item);
		$node = $this->setNodeValue('media:group/media:restriction', $values[TVComDistributionField::MEDIA_RESTRICTION_COUNTRIES], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:show-tmsid\']', $values[TVComDistributionField::MEDIA_CATEGORY_SHOW_TMSID], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:show-tmsid\']/@label', $values[TVComDistributionField::MEDIA_CATEGORY_SHOW_TMSID_LABEL], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:episode-tmsid\']', $values[TVComDistributionField::MEDIA_CATEGORY_EPISODE_TMSID], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:episode-tmsid\']/@label', $values[TVComDistributionField::MEDIA_CATEGORY_EPISODE_TMSID_LABEL], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:episodetype\']', $values[TVComDistributionField::MEDIA_CATEGORY_EPISODE_TYPE], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:original_air_date\']', $values[TVComDistributionField::MEDIA_CATEGORY_ORIGINAL_AIR_DATE], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:video_format\']', $values[TVComDistributionField::MEDIA_CATEGORY_VIDEO_FORMAT], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:season_number\']', $values[TVComDistributionField::MEDIA_CATEGORY_SEASON_NUMBER], $item);
		$node = $this->setNodeValue('media:group/media:category[@scheme=\'urn:tvcom:episode_number\']', $values[TVComDistributionField::MEDIA_CATEGORY_EPISODE_NUMBER], $item);
		
		$dcTerms = "start=$pubDate; end=$expDate; scheme=W3C-DTF";
		$node = $this->setNodeValue('dcterms:valid', $dcTerms, $item);

		if ($flavorAsset)
		{
			$node = $this->setNodeValue('media:group/media:content/@url', $this->getAssetUrl($flavorAsset), $item);
			$type = '';
			switch ($flavorAsset->getFileExt())
			{
				case 'mp4':
					$type = 'video/mp4';
					break;
				case 'flv':
					$type = 'video/x-flv';
					break;
			} 
			$node = $this->setNodeValue('media:group/media:content/@type', $type, $item);
			$node = $this->setNodeValue('media:group/media:content/@fileSize', $flavorAsset->getSize(), $item);
			$node = $this->setNodeValue('media:group/media:content/@expression', $values[TVComDistributionField::MEDIA_CATEGORY_EPISODE_TYPE], $item);
			$node = $this->setNodeValue('media:group/media:content/@duration', floor($flavorAsset->getentry()->getDuration()), $item);
		}
		
		if ($thumbAsset)
		{
			$node = $this->setNodeValue('media:group/media:thumbnail/@url', $this->getAssetUrl($thumbAsset), $item);
			$node = $this->setNodeValue('media:group/media:thumbnail/@width', $thumbAsset->getWidth(), $item);
			$node = $this->setNodeValue('media:group/media:thumbnail/@height', $thumbAsset->getHeight(), $item);
		}
		
		if(is_array($additionalAssets)){
			foreach ($additionalAssets as $additionalAsset){
				/* @var $additionalAsset asset */
				$assetType = $additionalAsset->getType();
				switch($assetType){
					case CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION):
						/* @var $captionPlugin CaptionPlugin */
						$captionPlugin = KalturaPluginManager::getPluginInstance(CaptionPlugin::PLUGIN_NAME);
						$dummyElement = new SimpleXMLElement('<dummy/>');
						$captionPlugin->contributeCaptionAssets($additionalAsset, $dummyElement);
						$dummyDom = dom_import_simplexml($dummyElement);
						$captionDom = $dummyDom->getElementsByTagName('subTitle');
						$captionDom = $this->doc->importNode($captionDom->item(0),true);
						$captionDom = $item->appendChild($captionDom);
						break;
					case AttachmentPlugin::getAssetTypeCoreValue(AttachmentAssetType::ATTACHMENT):
						/* @var $attachmentPlugin AttachmentPlugin */
						$attachmentPlugin = KalturaPluginManager::getPluginInstance(AttachmentPlugin::PLUGIN_NAME);
						$dummyElement = new SimpleXMLElement('<dummy/>');
						$attachmentPlugin->contributeAttachmentAssets($additionalAsset, $dummyElement);
						$dummyDom = dom_import_simplexml($dummyElement);
						$attachmentDom = $dummyDom->getElementsByTagName('attachment');
						$attachmentDom = $this->doc->importNode($attachmentDom->item(0),true);
						$attachmentDom = $item->appendChild($attachmentDom);
						break;
				}			
			}
		}
	}
	
	public function getAssetUrl(asset $asset)
	{
		$cdnHost = myPartnerUtils::getCdnHost($asset->getPartnerId());
		
		$urlManager = kUrlManager::getUrlManagerByCdn($cdnHost, $asset->getEntryId());
		$urlManager->setDomain($cdnHost);
		$url = $urlManager->getAssetUrl($asset);
		$url = $cdnHost . $url;
		$url = preg_replace('/^https?:\/\//', '', $url);
		return 'http://' . $url;
	}
	
	public function getXml()
	{
		return $this->doc->saveXML();
	}
}