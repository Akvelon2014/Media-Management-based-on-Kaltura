<?php
/**
 * @package plugins.timeWarnerDistribution
 * @subpackage lib
 */
class TimeWarnerFeed
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
	 * @var DOMElement
	 */
	protected $content;
	
	/**
	 * @var DOMElement
	 */
	protected $thumbnail;
	
	/**
	 * @var DOMElement
	 */
	protected $category;
	
	/**
	 * @var TimeWarnerDistributionProfile
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
		$this->doc->formatOutput = true;
		$this->doc->preserveWhiteSpace = false;
		$this->doc->load($xmlTemplate);
		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');
		$this->xpath->registerNamespace('dcterms', 'http://purl.org/dc/terms/');
		$this->xpath->registerNamespace('pl', 'http://xml.theplatform.com/data/object');
		$this->xpath->registerNamespace('pllist', 'http://xml.theplatform.com/data/list');
		$this->xpath->registerNamespace('plfile', 'http://xml.theplatform.com/media/data/MediaFile');
		$this->xpath->registerNamespace('plmedia', 'http://xml.theplatform.com/media/data/Media');
		$this->xpath->registerNamespace('pla', 'http://xml.theplatform.com/data/object/admin');
		$this->xpath->registerNamespace('twcable', 'http://twcable.com/customfields');
		
		// item node template
		$node = $this->xpath->query('/rss/channel/item')->item(0);
		$this->item = $node->cloneNode(true);
		$node->parentNode->removeChild($node);

		// content node template
		$node = $this->xpath->query('media:group/media:content', $this->item)->item(0);
		$this->content = $node->cloneNode(true);
		$node->parentNode->removeChild($node);
		
		// category node template
		$node = $this->xpath->query('media:category', $this->item)->item(0);
		$this->category = $node->cloneNode(true);
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
	 * @param TimeWarnerDistributionProfile $profile
	 */
	public function setDistributionProfile(TimeWarnerDistributionProfile $profile)
	{
		$this->distributionProfile = $profile;
	}
	
	/**
	 * @param array $values
	 * @param array $flavorAssets
	 * @param array $thumbAssets
	 */
	public function addItem(array $values, array $flavorAssets = null, array $thumbAssets = null,array $additionalAssets = null)
	{
		$item = $this->item->cloneNode(true);
		$channelNode = $this->xpath->query('/rss/channel', $item)->item(0);
		$channelNode->appendChild($item);
		
		$this->setNodeValue('guid', $values[TimeWarnerDistributionField::GUID], $item);
		$this->setNodeValue('title', $values[TimeWarnerDistributionField::TITLE], $item);
		$this->setNodeValue('description', $values[TimeWarnerDistributionField::DESCRIPTION], $item);
		$this->setNodeValue('author', $values[TimeWarnerDistributionField::AUTHOR], $item);
		$this->setNodeValue('pubDate', $this->formatTimeWarnerDate($values[TimeWarnerDistributionField::PUB_DATE]), $item);
		
		$this->setNodeValue('media:copyright', $values[TimeWarnerDistributionField::MEDIA_COPYRIGHT], $item);
		$this->setNodeValue('media:keywords', $values[TimeWarnerDistributionField::MEDIA_KEYWORDS], $item);
		$this->setNodeValue('media:rating', $values[TimeWarnerDistributionField::MEDIA_RATING], $item);

		//handle category
		$this->addCategory($item,'CT-'.$values[TimeWarnerDistributionField::MEDIA_CATEGORY_CT]);
		if ($values[TimeWarnerDistributionField::MEDIA_CATEGORY_GR] != ""){
			$this->addCategory($item,'GR-'.$values[TimeWarnerDistributionField::MEDIA_CATEGORY_GR]);
		}
		else{
			$this->addCategory($item,'GR-None');
		}
		$this->addCategory($item,'TX-'.$values[TimeWarnerDistributionField::MEDIA_CATEGORY_TX]);		
		$geCategories = explode(',', $values[TimeWarnerDistributionField::MEDIA_CATEGORY_GE]);
		$geCategories = array_unique($geCategories);
		foreach($geCategories as $geCategory)
		{
			$this->addCategory($item,'GE-'.$geCategory);
		}				
		
		
		$this->setNodeValue('plmedia:approved', $values[TimeWarnerDistributionField::PLMEDIA_APPROVED], $item);
		
		$this->setNodeValue('twcable:episodeNumber', $values[TimeWarnerDistributionField::CABLE_EPISODE_NUMBER], $item);
		$this->setNodeValue('twcable:externalID', $values[TimeWarnerDistributionField::CABLE_EXTERNAL_ID], $item);
		$this->setNodeValue('twcable:productionDate', $values[TimeWarnerDistributionField::CABLE_PRODUCTION_DATE], $item);
		$this->setNodeValue('twcable:network', $values[TimeWarnerDistributionField::CABLE_NETWORK], $item);
		$this->setNodeValue('twcable:provider', $values[TimeWarnerDistributionField::CABLE_PROVIDER], $item);
		$this->setNodeValue('twcable:shortDescription', $values[TimeWarnerDistributionField::CABLE_SHORT_DESCRIPTION], $item);
		$this->setNodeValue('twcable:shortTitle', $values[TimeWarnerDistributionField::CABLE_SHORT_TITLE], $item);
		$this->setNodeValue('twcable:showName', $values[TimeWarnerDistributionField::CABLE_SHOW_NAME], $item);
		
		$startTime = date('c', $values[TimeWarnerDistributionField::START_TIME]);
		$endTime = date('c', $values[TimeWarnerDistributionField::END_TIME]);
		$dcTerms = "start=$startTime; end=$endTime;";
		$this->setNodeValue('dcterms:valid', $dcTerms, $item);

		if (!is_null($flavorAssets) && is_array($flavorAssets) && count($flavorAssets)>0)
			$this->setFlavorAsset($item, $flavorAssets);
			
		if (!is_null($thumbAssets) && is_array($thumbAssets) && count($thumbAssets)>0)
		{
			$this->setThumbAsset($item, $thumbAssets);			
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
	
	public function addCategory($item, $categoryValue)
	{	
		$categoryNode = $this->category->cloneNode(true);
		$categoryNode->nodeValue = $categoryValue;	
		$beforeNode = $this->xpath->query('plmedia:approved', $item)->item(0);		
		$item->insertBefore($categoryNode, $beforeNode);	
	}
	
	/**
	 * @param array $flavorAssets
	 */
	public function setFlavorAsset(DOMElement $item, array $flavorAssets)
	{
		$flavorAsset = $flavorAssets[0];
		/* @var $flavorAsset flavorAsset */
		$content = $this->content->cloneNode(true);
		$mediaGroup = $this->xpath->query('media:group', $item)->item(0);
		$mediaGroup->appendChild($content);
		$url = $this->getAssetUrl($flavorAsset);
		$this->setNodeValue('@url', $url, $content);
		
	}
	
	/**
	 * @param array $thumbAssets
	 */
	public function setThumbAsset(DOMElement $item, array $thumbAssets)
	{
		/** @var $thumbAsset thumbAsset */ 
		$thumbAsset = $thumbAssets[0];
		$url = $this->getAssetUrl($thumbAsset);
		$this->setNodeValue('media:thumbnail/@url', $url, $item);
		$this->setNodeValue('media:thumbnail/@width', $thumbAsset->getWidth(), $item);
		$this->setNodeValue('media:thumbnail/@height', $thumbAsset->getHeight(), $item);	
	}
	
	protected function getContentTypeFromUrl($url)
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_HEADER, true);
		curl_setopt($this->ch, CURLOPT_NOBODY, true);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		$headers = curl_exec($this->ch);
		if (preg_match('/Content-Type: (.*)/', $headers, $matched))
		{
			return trim($matched[1]);
		}
		else
		{
			KalturaLog::alert('"Content-Type" header was not found for the following URL: '. $url);
			return null;
		}
	}
	
	/**
	 * time warner used Z for UTC timezone in their example (2008-04-11T12:30:00Z)
	 * @param int $time
	 */
	protected function formatTimeWarnerDate($time)
	{
		$date = new DateTime('@'.$time, new DateTimeZone('UTC'));
		return str_replace('+0000', 'Z', $date->format(DateTime::ISO8601)); 
	}
	
}