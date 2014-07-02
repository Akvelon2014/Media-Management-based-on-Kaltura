<?php
/**
 * @package plugins.synacorHboDistribution
 * @subpackage lib
 */
class SynacorHboFeed
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
	 * @param $templateName
	 */
	public function __construct($templateName)
	{
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml/' . $templateName;
		$this->doc = new KDOMDocument();
		$this->doc->formatOutput = true;
		$this->doc->preserveWhiteSpace = false;
		$docLoadRes = $this->doc->load($xmlTemplate);
		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
		$this->xpath->registerNamespace('go', 'http://hbogo.com/elements/1.0');
		
		// item node template
		$node = $this->xpath->query('/atom:feed/atom:entry')->item(0);

		$this->item = $node->cloneNode(true);
		$node->parentNode->removeChild($node);
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function setNodeValue($xpath, $value, DOMNode $contextnode = null)
	{
		kXml::setNodeValue($this->doc, $this->xpath, $xpath, $value, $contextnode);
	}
	
	public function removeNode($xpath, DOMNode $contextnode = null)
	{
		if ($contextnode) {
			$node = $this->xpath->query($xpath, $contextnode)->item(0);
		}
		else {
			$node = $this->xpath->query($xpath)->item(0);
		}
		if (!is_null($node))
		{
	        $node->parentNode->removeChild($node);
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
	
	
	public function setDistributionProfile(SynacorHboDistributionProfile $profile)
	{
	    $this->setNodeValue('/atom:feed/atom:title', $profile->getFeedTitle());
		$this->setNodeValue('/atom:feed/atom:link/@href', $profile->getFeedLink());
		
		$feedSubtitleValue = $profile->getFeedSubtitle();
		if (strlen($feedSubtitleValue) > 0) {
		    $this->setNodeValue('/atom:feed/atom:subtitle', $feedSubtitleValue);
		}
		else {
		    $this->removeNode('/atom:feed/atom:subtitle');
		}
	}
	
	/**
	 * @param array $values
	 * @param array $flavorAssets
	 * @param array $thumbAssets
	 */
	public function addItem(array $values, entry $entry, array $flavorAssets = null, array $thumbAssets = null,array $additionalAssets = null)
	{
		$item = $this->item->cloneNode(true);
		$feedNode = $this->xpath->query('/atom:feed', $item)->item(0);
		$feedNode->appendChild($item);
		
		$this->setNodeValue('atom:title', $values[SynacorHboDistributionField::ENTRY_TITLE], $item);
		$this->setNodeValue('atom:summary', $values[SynacorHboDistributionField::ENTRY_SUMMARY], $item);
		
		$updatedTime = $this->formatSynacorHboTime($values[SynacorHboDistributionField::ENTRY_UPDATED]);		
		$this->setNodeValue('atom:updated', $updatedTime, $item);
		
		$this->setNodeValue('atom:author/atom:name', $values[SynacorHboDistributionField::ENTRY_AUTHOR_NAME], $item);
		
	    $categoryValue = $values[SynacorHboDistributionField::ENTRY_CATEGORY_TERM];
		if (strlen($categoryValue) > 0) {
		    $this->setNodeValue('atom:category/@term', $categoryValue, $item);
		}
		else {
		    $this->removeNode('atom:category', $item);
		}
		
	    $genreValue = $values[SynacorHboDistributionField::ENTRY_GENRE_TERM];
		if (strlen($genreValue) > 0) {
		    $this->setNodeValue('atom:genre/@term', $genreValue, $item);
		}
		else {
		    $this->removeNode('atom:genre', $item);
		}
		
		$this->setNodeValue('atom:assetType', $values[SynacorHboDistributionField::ENTRY_ASSET_TYPE], $item);
		$this->setNodeValue('atom:assetId', $values[SynacorHboDistributionField::ENTRY_ASSET_ID], $item);
		
		$startTime = $this->formatSynacorHboTime($values[SynacorHboDistributionField::ENTRY_OFFERING_START]);
		$this->setNodeValue('atom:offering/atom:start', $startTime, $item);
		$endTime = $this->formatSynacorHboTime($values[SynacorHboDistributionField::ENTRY_OFFERING_END]);
		$this->setNodeValue('atom:offering/atom:end', $endTime, $item);
		
	    $ratingValue = $values[SynacorHboDistributionField::ENTRY_RATING];
		if (strlen($ratingValue) > 0) {
		    $this->setNodeValue('atom:rating', $ratingValue, $item);
		    $ratingType = stripos($ratingValue, 'tv') === '0' ? 'tv' : 'theatrical';
		    $this->setNodeValue('atom:rating/@type', $ratingType, $item);
		}
		else {
		    $this->removeNode('atom:rating', $item);
		}
		
		$durationInSeconds = ceil($entry->getDuration());
		$durationInMinuesRoundedUp = ceil($durationInSeconds/60);
		$this->setNodeValue('atom:runtime', $durationInMinuesRoundedUp, $item);
		$this->setNodeValue('atom:runtime/@timeInSeconds', $durationInSeconds, $item);
		
		$this->setNodeValue('go:series/go:title', $values[SynacorHboDistributionField::ENTRY_SERIES_TITLE], $item);
		
		$this->setNodeValue('atom:brand', $values[SynacorHboDistributionField::ENTRY_BRAND], $item);		
		
		if (!is_null($flavorAssets) && is_array($flavorAssets) && count($flavorAssets)>0)
		{
			$flavorAsset = $flavorAssets[0];
    		/* @var $flavorAsset flavorAsset */
    		$flavorUrl = $this->getAssetUrl($flavorAsset);
		    // we don't have a way to identify the mime type of the file
			// as there is no guarantee that the file exists in the current data center
			// so we will just use those hardcoded conditions
			$mimeType = '';
			switch($flavorAsset->getFileExt())
			{
				case 'flv':
					$mimeType = 'video/x-flv';
					break;
				case 'mp4':
					$mimeType = 'video/mp4';
					break;
				case 'mpeg':
				case 'mpg':
					$mimeType = 'video/mpeg';
					break;
				default:
					$mimeType = 'video/x-flv'; // default requested by synacor
			}

    		$this->setNodeValue('atom:link[@type=\'VIDEO_MIME_TYPE\']/@href', $flavorUrl, $item);
    		$this->setNodeValue('atom:link[@type=\'VIDEO_MIME_TYPE\']/@type', $mimeType, $item);
		}
		if (!is_null($thumbAssets) && is_array($thumbAssets) && count($thumbAssets)>0)
		{
		    $thumbAsset = $thumbAssets[0];
    		/* @var $thumbAssets thumbAssets */
    		$thumbUrl = $this->getAssetUrl($thumbAsset);
    		$this->setNodeValue('atom:link[@type=\'image/jpeg\']/@href', $thumbUrl, $item);
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
	
		
	/**
	 * @param int $time
	 */
	protected function formatSynacorHboTime($time)
	{
		$date = new DateTime('@'.$time, new DateTimeZone('UTC'));
		return str_replace('+0000', 'Z', $date->format(DateTime::ISO8601));
	}
	
}