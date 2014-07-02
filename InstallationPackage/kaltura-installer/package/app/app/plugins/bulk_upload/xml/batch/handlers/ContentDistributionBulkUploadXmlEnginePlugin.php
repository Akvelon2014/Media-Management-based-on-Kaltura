<?php
/**
 * @package plugins.contentDistributionBulkUploadXml
 */
class ContentDistributionBulkUploadXmlEnginePlugin extends KalturaPlugin implements IKalturaPending, IKalturaBulkUploadXmlHandler, IKalturaConfigurator
{
	const PLUGIN_NAME = 'contentDistributionBulkUploadXml';
	
	const BULK_UPLOAD_XML_VERSION_MAJOR = 1;
	const BULK_UPLOAD_XML_VERSION_MINOR = 0;
	const BULK_UPLOAD_XML_VERSION_BUILD = 0;
	
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 1;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 1;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;
	
	/**
	 * @var array<string, int> of distribution profiles by their system name
	 */
	private $distributionProfilesNames = null;
	
	/**
	 * @var array<string, int> of distribution profiles by their provider name
	 */
	private $distributionProfilesProviders = null;
	
	/**
	 * @var BulkUploadEngineXml
	 */
	private $xmlBulkUploadEngine = null;
	
	/**
	 * @return string
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$bulkUploadXmlVersion = new KalturaVersion(
			self::BULK_UPLOAD_XML_VERSION_MAJOR,
			self::BULK_UPLOAD_XML_VERSION_MINOR,
			self::BULK_UPLOAD_XML_VERSION_BUILD);
			
		$contentDistributionVersion = new KalturaVersion(
			self::CONTENT_DSTRIBUTION_VERSION_MAJOR,
			self::CONTENT_DSTRIBUTION_VERSION_MINOR,
			self::CONTENT_DSTRIBUTION_VERSION_BUILD);
			
		$bulkUploadXmlDependency = new KalturaDependency(BulkUploadXmlPlugin::getPluginName(), $bulkUploadXmlVersion);
		$contentDistributionDependency = new KalturaDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		
		return array($bulkUploadXmlDependency, $contentDistributionDependency);
	}
	
	public function getDistributionProfileId($systemName, $providerName)
	{
		if(is_null($this->distributionProfilesNames))
		{
			$distributionPlugin = KalturaContentDistributionClientPlugin::get($this->xmlBulkUploadEngine->getClient());
			$distributionProfileListResponse = $distributionPlugin->distributionProfile->listAction();
			if(!is_array($distributionProfileListResponse->objects))
				return null;
				
			$this->distributionProfilesNames = array();
			$this->distributionProfilesProviders = array();
			
			foreach($distributionProfileListResponse->objects as $distributionProfile)
			{
				if(!is_null($distributionProfile->systemName))
					$this->distributionProfilesNames[$distributionProfile->systemName] = $distributionProfile->id;
					
				if(!is_null($distributionProfile->providerType))
					$this->distributionProfilesProviders[$distributionProfile->providerType] = $distributionProfile->id;
			}
		}
		
		if(!empty($systemName) && isset($this->distributionProfilesNames[$systemName]))
			return $this->distributionProfilesNames[$systemName];
		
		if(!empty($providerName) && isset($this->distributionProfilesProviders[$providerName]))
			return $this->distributionProfilesProviders[$providerName];
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaBulkUploadXmlHandler::configureBulkUploadXmlHandler()
	 */
	public function configureBulkUploadXmlHandler(BulkUploadEngineXml $xmlBulkUploadEngine)
	{
		$this->xmlBulkUploadEngine = $xmlBulkUploadEngine;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaBulkUploadXmlHandler::handleItemAdded()
	 */
	public function handleItemAdded(KalturaObjectBase $object, SimpleXMLElement $item)
	{
		if(!($object instanceof KalturaBaseEntry))
			return;
			
		if(empty($item->distribution))
			return;
			
		foreach($item->distribution as $distribution)
			$this->handleDistribution($object->id, $distribution);
	}
	
	public function handleDistribution($entryId, SimpleXMLElement $distribution)
	{
		$distributionProfileId = null;
		if(!empty($distribution->distributionProfileId))
			$distributionProfileId = (int)$distribution->distributionProfileId;

		if(!$distributionProfileId && (!empty($distribution->distributionProfile) || !empty($distribution->distributionProvider)))
			$distributionProfileId = $this->getDistributionProfileId($distribution->distributionProfile, $distribution->distributionProvider);
				
		if(!$distributionProfileId)
			throw new KalturaBatchException("Missing custom data distributionProfile attribute", KalturaBatchJobAppErrors::BULK_MISSING_MANDATORY_PARAMETER);
		
		$distributionPlugin = KalturaContentDistributionClientPlugin::get($this->xmlBulkUploadEngine->getClient());
		
		$entryDistributionFilter = new KalturaEntryDistributionFilter();
		$entryDistributionFilter->distributionProfileIdEqual = $distributionProfileId;
		$entryDistributionFilter->entryIdEqual = $entryId;
		
		$pager = new KalturaFilterPager();
		$pager->pageSize = 1;
		
		$entryDistributionResponse = $distributionPlugin->entryDistribution->listAction($entryDistributionFilter, $pager);
		
		$entryDistribution = new KalturaEntryDistribution();
		$entryDistributionId = null;
		if(is_array($entryDistributionResponse->objects) && count($entryDistributionResponse->objects) > 0)
		{
			$existingEntryDistribution = reset($entryDistributionResponse->objects);
			$entryDistributionId = $existingEntryDistribution->id;
		}
		else
		{
			$entryDistribution->entryId = $entryId;
			$entryDistribution->distributionProfileId = $distributionProfileId;
		}
		
		if(!empty($distribution->sunrise) && KBulkUploadEngine::isFormatedDate($distribution->sunrise))
			$entryDistribution->sunrise = KBulkUploadEngine::parseFormatedDate($distribution->sunrise);
			
		if(!empty($distribution->sunset) && KBulkUploadEngine::isFormatedDate($distribution->sunset))
			$entryDistribution->sunset = KBulkUploadEngine::parseFormatedDate($distribution->sunset);
		
		if(!empty($distribution->flavorAssetIds))
			$entryDistribution->flavorAssetIds = $distribution->flavorAssetIds;
		
		if(!empty($distribution->thumbAssetIds))
			$entryDistribution->thumbAssetIds = $distribution->thumbAssetIds;
			
		$submitWhenReady = false;
		if($distribution['submitWhenReady'])
			$submitWhenReady = true;
			
		$this->xmlBulkUploadEngine->impersonate();
		if($entryDistributionId)
		{
			$updatedEntryDistribution = $distributionPlugin->entryDistribution->update($entryDistributionId, $entryDistribution);
			if($submitWhenReady && $updatedEntryDistribution->dirtyStatus == KalturaEntryDistributionFlag::UPDATE_REQUIRED)
				$distributionPlugin->entryDistribution->submitUpdate($entryDistributionId);
		}
		else
		{
			$createdEntryDistribution = $distributionPlugin->entryDistribution->add($entryDistribution);
			$distributionPlugin->entryDistribution->submitAdd($createdEntryDistribution->id, $submitWhenReady);
		}
		$this->xmlBulkUploadEngine->unimpersonate();
	}

	/* (non-PHPdoc)
	 * @see IKalturaBulkUploadXmlHandler::handleItemUpdated()
	 */
	public function handleItemUpdated(KalturaObjectBase $object, SimpleXMLElement $item)
	{
		$this->handleItemAdded($object, $item);
	}

	/* (non-PHPdoc)
	 * @see IKalturaBulkUploadXmlHandler::handleItemDeleted()
	 */
	public function handleItemDeleted(KalturaObjectBase $object, SimpleXMLElement $item)
	{
		// No handling required
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaConfigurator::getConfig()
	 */
	public static function getConfig($configName)
	{
		if($configName == 'generator')
			return new Zend_Config_Ini(dirname(__FILE__) . '/config/contentDistributionBulkUploadXml.generator.ini');
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IKalturaConfigurator::getContainerName()
	*/
	public function getContainerName()
	{
		return 'distribution';
	}
}
