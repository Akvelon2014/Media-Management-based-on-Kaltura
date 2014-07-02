<?php
/**
 * @package plugins.metroPcsDistribution
 * @subpackage lib
 */
class MetroPcsDistributionFeedHelper
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
	 * @var AttUverseDistributionProfile
	 */
	protected $distributionProfile;

	/**
	 * @var KalturaEntryDistribution
	 */
	protected $entryDistribution;
	
	/**
	 * @var KalturaMetroPcsDistributionJobProviderData
	 */
	protected $providerData;
	
	/**
	 * @var array
	 */
	protected $fieldValues;
		
	
	/**
	 * @param $templateName
	 * @param $distributionProfile
	 */
	public function __construct($templateName, $entryDistribution, KalturaMetroPcsDistributionProfile $distributionProfile, KalturaMetroPcsDistributionJobProviderData $providerData) 
	{
		$this->entryDistribution = $entryDistribution;
		$this->distributionProfile = $distributionProfile;
		$this->providerData = $providerData;
		$this->fieldValues = unserialize($providerData->fieldValues);
		if (!$this->fieldValues) {
		    $this->fieldValues = array();
		}
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml_templates/' . $templateName;
		$this->doc = new KDOMDocument();
		$this->doc->load($xmlTemplate);		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('msdp', 'http://www.real.com/msdp');	
		
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:title', $this->getValueForField(KalturaMetroPcsDistributionField::TITLE));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:link', $this->getValueForField(KalturaMetroPcsDistributionField::LINK));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:externalId', $this->getValueForField(KalturaMetroPcsDistributionField::EXTERNAL_ID));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:providerId', $this->getValueForField(KalturaMetroPcsDistributionField::PROVIDER_ID));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:shortDescription', $this->getValueForField(KalturaMetroPcsDistributionField::SHORT_DESCRIPTION));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:description', $this->getValueForField(KalturaMetroPcsDistributionField::DESCRIPTION));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:language', $this->getValueForField(KalturaMetroPcsDistributionField::LANGUAGE));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:copyright', $this->getValueForField(KalturaMetroPcsDistributionField::COPYRIGHT));		
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:managingEditor', $this->getValueForField(KalturaMetroPcsDistributionField::MANAGING_EDITOR));
		
		$pubDate = $this->getValueForField(KalturaMetroPcsDistributionField::PUB_DATE);
		if ($pubDate) 
		{
		   $this->setNodeValue('/msdp:rss/msdp:channel/msdp:pubDate',date('D M j G:i:s T Y', intval($pubDate))); 
		}
					
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:category', $this->getValueForField(KalturaMetroPcsDistributionField::CATEGORY));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:upc', $this->getValueForField(KalturaMetroPcsDistributionField::UPC));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:isrc', $this->getValueForField(KalturaMetroPcsDistributionField::ISRC));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:program', $this->getValueForField(KalturaMetroPcsDistributionField::PROGRAM));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:seasonId', $this->getValueForField(KalturaMetroPcsDistributionField::SEASON_ID));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:episodicId', $this->getValueForField(KalturaMetroPcsDistributionField::EPISODIC_ID));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:chapterId', $this->getValueForField(KalturaMetroPcsDistributionField::CHAPTER_ID));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:artist', $this->getValueForField(KalturaMetroPcsDistributionField::ARTIST));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:performer', $this->getValueForField(KalturaMetroPcsDistributionField::PERFORMER));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:director', $this->getValueForField(KalturaMetroPcsDistributionField::DIRECTOR));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:studio', $this->getValueForField(KalturaMetroPcsDistributionField::STUDIO));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:originalRelease', $this->getValueForField(KalturaMetroPcsDistributionField::ORIGINAL_RELEASE));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:topStory', $this->getValueForField(KalturaMetroPcsDistributionField::TOP_STORY));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:sortOrder', $this->getValueForField(KalturaMetroPcsDistributionField::SORT_ORDER));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:sortName', $this->getValueForField(KalturaMetroPcsDistributionField::SORT_NAME));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:genre', $this->getValueForField(KalturaMetroPcsDistributionField::GENRE));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:keywords', $this->getValueForField(KalturaMetroPcsDistributionField::KEYWORDS));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:localCode', $this->getValueForField(KalturaMetroPcsDistributionField::LOCAL_CODE));
		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:entitlements', $this->getValueForField(KalturaMetroPcsDistributionField::ENTITLEMENTS));
				
		$startDate = new DateTime('@'.$this->getValueForField(KalturaMetroPcsDistributionField::START_DATE));
		if ($startDate) 
		{	
			// force time zone to EST
			$startDate->setTimezone(new DateTimeZone('EST'));
			$date = $startDate->format('c');			
		    $this->setNodeValue('/msdp:rss/msdp:channel/msdp:startDate',$date);  
		}		
		
		$endDate = new DateTime('@'.$this->getValueForField(KalturaMetroPcsDistributionField::END_DATE));
		if ($endDate) 
		{
			// force time zone to EST
			$endDate->setTimezone(new DateTimeZone('EST'));
		    $date = $endDate->format('c');			
		    $this->setNodeValue('/msdp:rss/msdp:channel/msdp:endDate',$date); 
		}	

		$this->setNodeValue('/msdp:rss/msdp:channel/msdp:rating', $this->getValueForField(KalturaMetroPcsDistributionField::RATING));
	}
				
	/**
	 * @param string $xpath
	 * @param string $value
	 * @param DOMNode $contextnode
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
	
	private function getValueForField($fieldName)
	{
	    if (isset($this->fieldValues[$fieldName])) {
	        return $this->fieldValues[$fieldName];
	    }
	    return null;
	}	
		
	/**
	 * set flavors in XML
	 * @param KalturaThumbAsset $thumbAssets
	 */
	public function setThumbnails($thumbAssets, $thumbUrls)
	{	
		$templateImageNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:image')->item(0);		
		if (count($thumbAssets) && count($thumbUrls))	
		{										
			foreach ($thumbAssets as $thumbAsset)
			{
				$url = $thumbUrls[$thumbAsset->id];
				$this->setNodeValue('msdp:url', $url, $templateImageNode);
				$this->setNodeValue('msdp:width', $thumbAsset->width , $templateImageNode);
				$this->setNodeValue('msdp:height', $thumbAsset->height , $templateImageNode);
				//$this->cloneNode instead the DOMNode cloneNode since the cloneNode doesn't deep copy the namespaces inside the tags
				$newImageNode = $this->cloneNode($templateImageNode, $this->doc);
				$templateImageNode->parentNode->insertBefore($newImageNode, $templateImageNode);
			}			
			$templateImageNode->parentNode->removeChild($templateImageNode);		
		}
		else
		{
			//ignore image element	
			$this->setNodeValue('@ignore', "Y", $templateImageNode);
		}
	}
	
	/**
	 * set flavors in XML
	 * @param KalturaFlavorAsset $flavorAssets
	 */
	public function setFlavor ($flavorAsset, $entryDuration, $currenTime)
	{	
		$templateItemNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:item')->item(0);
		if($flavorAsset)
		{	
			$itemTitle = $this->getValueForField(KalturaMetroPcsDistributionField::ITEM_TITLE);
			$itemDescription= $this->getValueForField(KalturaMetroPcsDistributionField::ITEM_DESCRIPTION);
			$itemType= $this->getValueForField(KalturaMetroPcsDistributionField::ITEM_TYPE);
			//$url = $this->getAssetUrl($flavorAsset);
			$url = $this->flavorAssetUniqueName($flavorAsset, $currenTime);			
			$this->setNodeValue('msdp:title', $itemTitle, $templateItemNode);
			$this->setNodeValue('msdp:description', $itemDescription, $templateItemNode);
			$this->setNodeValue('msdp:type', $itemType, $templateItemNode);				
			$this->setNodeValue('msdp:width', $flavorAsset->width , $templateItemNode);		
			$this->setNodeValue('msdp:height', $flavorAsset->height , $templateItemNode);					
			$this->setNodeValue('msdp:enclosure/@url', $url, $templateItemNode);
			$this->setNodeValue('msdp:enclosure/@length', $entryDuration, $templateItemNode);
			//$this->cloneNode instead the DOMNode cloneNode since the cloneNode doesn't deep copy the namespaces inside the tags
			$newItemNode = $this->cloneNode($templateItemNode, $this->doc);
			$templateItemNode->parentNode->insertBefore($newItemNode, $templateItemNode);
			$templateItemNode->parentNode->removeChild($templateItemNode);
		}
		else
		{
			//ignore image element	
			$this->setNodeValue('@ignore', "Y", $templateItemNode);
		}
	}
	
	/**
	 * Setting the start and end dates to passed dates while maintaining startDate<endDate
	 */
	public function setTimesForDelete()
	{
		//two days ago
		$startDate = time() - 48*60*60;  
		$startDate = new DateTime('@'.$startDate);
		if ($startDate) 
		{	
			// force time zone to EST
			$startDate->setTimezone(new DateTimeZone('EST'));
			$date = $startDate->format('c');			
		    $this->setNodeValue('/msdp:rss/msdp:channel/msdp:startDate',$date);  
		}		
		
		//yesterday
		$endDate = time() - 24*60*60;  
		$endDate = new DateTime('@'.$endDate);
		if ($endDate) 
		{
			// force time zone to EST
			$endDate->setTimezone(new DateTimeZone('EST'));
		    $date = $endDate->format('c');			
		    $this->setNodeValue('/msdp:rss/msdp:channel/msdp:endDate',$date); 
		}	
	}
	
	public function setImageIgnore()
	{
		$imageNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:image')->item(0);	
		$this->setNodeValue('@ignore', "Y", $imageNode);		
	}
	
	
	public function setItemIgnore()
	{		
		$itemNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:item')->item(0);
		$this->setNodeValue('msdp:type', $this->getValueForField(KalturaMetroPcsDistributionField::ITEM_TYPE), $itemNode);
		$this->setNodeValue('@ignore', "Y", $itemNode);		
	}
	
	public function getXmlString()
	{
		return $this->doc->saveXML();
	}
		
	/**
	 * creates unique name for flavor asset
	 * @param KalturaFlavorAsset $flavorAsset
	 */
	public function flavorAssetUniqueName($flavorAsset, $currentTime)
	{
		$path = $this->distributionProfile->ftpPath;
		$fileExt = $flavorAsset->fileExt;	
		//$uniqueName = $path.'/'.$currentTime.'_'.$this->entryDistribution->id.'_'.$flavorAsset->entryId.'_'.$flavorAsset->id.'.'.$fileExt;
		$uniqueName = $currentTime.'_'.$this->entryDistribution->id.'_'.$flavorAsset->entryId.'_'.$flavorAsset->id.'.'.$fileExt;
		return $uniqueName;		
	}
	
	private function cloneNode($node,$doc)
	{
	    $nd = $doc->createElement($node->nodeName);           
	    foreach($node->attributes as $value)
	        $nd->setAttribute($value->nodeName,$value->value);
	           
	    if(!$node->childNodes)
	        return $nd;
	               
	    foreach($node->childNodes as $child) {
	        if($child->nodeName=="#text")
	            $nd->appendChild($doc->createTextNode($child->nodeValue));
	        else
	            $nd->appendChild($this->cloneNode($child,$doc));
	    }          
    	return $nd;
	}
	
}