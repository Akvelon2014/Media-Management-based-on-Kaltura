<?php
/**
 * @package plugins.huluDistribution
 */
class HuluDistributionPlugin extends KalturaPlugin implements IKalturaPermissions, IKalturaEnumerator, IKalturaPending, IKalturaObjectLoader, IKalturaContentDistributionProvider, IKalturaConfigurator
{
	const PLUGIN_NAME = 'huluDistribution';
	const CONTENT_DSTRIBUTION_VERSION_MAJOR = 2;
	const CONTENT_DSTRIBUTION_VERSION_MINOR = 0;
	const CONTENT_DSTRIBUTION_VERSION_BUILD = 0;

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public static function dependsOn()
	{
		$contentDistributionVersion = new KalturaVersion(
			self::CONTENT_DSTRIBUTION_VERSION_MAJOR,
			self::CONTENT_DSTRIBUTION_VERSION_MINOR,
			self::CONTENT_DSTRIBUTION_VERSION_BUILD);
			
		$dependency = new KalturaDependency(ContentDistributionPlugin::getPluginName(), $contentDistributionVersion);
		return array($dependency);
	}
	
	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
			
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(ContentDistributionPlugin::getPluginName());
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('HuluDistributionProviderType');
	
		if($baseEnumName == 'DistributionProviderType')
			return array('HuluDistributionProviderType');
			
		return array();
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		// client side apps like batch and admin console
		if (class_exists('KalturaClient') && $enumValue == KalturaDistributionProviderType::HULU)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDelete')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineReport')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineSubmit')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineUpdate')
				return new HuluDistributionEngine();
		
			if($baseClass == 'IDistributionEngineEnable')
				return new HuluDistributionEngine();
					
			if($baseClass == 'IDistributionEngineDisable')
				return new HuluDistributionEngine();
		
			if($baseClass == 'KalturaDistributionProfile')
				return new KalturaHuluDistributionProfile();
		
			if($baseClass == 'KalturaDistributionJobProviderData')
				return new KalturaHuluDistributionJobProviderData();
		}
		
		if (class_exists('Kaltura_Client_Client') && $enumValue == Kaltura_Client_ContentDistribution_Enum_DistributionProviderType::HULU)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
			{
				$reflect = new ReflectionClass('Form_HuluProfileConfiguration');
				return $reflect->newInstanceArgs($constructorArgs);
			}
		}
		
		if($baseClass == 'KalturaDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU))
		{
			$reflect = new ReflectionClass('KalturaHuluDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'kDistributionJobProviderData' && $enumValue == self::getApiValue(HuluDistributionProviderType::HULU))
		{
			$reflect = new ReflectionClass('kHuluDistributionJobProviderData');
			return $reflect->newInstanceArgs($constructorArgs);
		}
	
		if($baseClass == 'KalturaDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU))
			return new KalturaHuluDistributionProfile();
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU))
			return new HuluDistributionProfile();
			
		return null;
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @return string
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// client side apps like batch and admin console
		if (class_exists('KalturaClient') && $enumValue == KalturaDistributionProviderType::HULU)
		{
			if($baseClass == 'IDistributionEngineCloseDelete')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseSubmit')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineCloseUpdate')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDelete')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineReport')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineSubmit')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineUpdate')
				return 'HuluDistributionEngine';
		
			if($baseClass == 'IDistributionEngineEnable')
				return 'HuluDistributionEngine';
					
			if($baseClass == 'IDistributionEngineDisable')
				return 'HuluDistributionEngine';
		
			if($baseClass == 'KalturaDistributionProfile')
				return 'KalturaHuluDistributionProfile';
		
			if($baseClass == 'KalturaDistributionJobProviderData')
				return 'KalturaHuluDistributionJobProviderData';
		}
		
		if (class_exists('Kaltura_Client_Client') && $enumValue == Kaltura_Client_ContentDistribution_Enum_DistributionProviderType::HULU)
		{
			if($baseClass == 'Form_ProviderProfileConfiguration')
				return 'Form_HuluProfileConfiguration';
				
			if($baseClass == 'Kaltura_Client_ContentDistribution_Type_DistributionProfile')
				return 'Kaltura_Client_HuluDistribution_Type_HuluDistributionProfile';
		}
		
		if($baseClass == 'KalturaDistributionJobProviderData' && $enumValue == self::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU))
			return 'KalturaHuluDistributionJobProviderData';
	
		if($baseClass == 'kDistributionJobProviderData' && $enumValue == self::getApiValue(HuluDistributionProviderType::HULU))
			return 'kHuluDistributionJobProviderData';
	
		if($baseClass == 'KalturaDistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU))
			return 'KalturaHuluDistributionProfile';
			
		if($baseClass == 'DistributionProfile' && $enumValue == self::getDistributionProviderTypeCoreValue(HuluDistributionProviderType::HULU))
			return 'HuluDistributionProfile';
			
		return null;
	}
	
	/**
	 * Return a distribution provider instance
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider()
	{
		return HuluDistributionProvider::get();
	}
	
	/**
	 * Return an API distribution provider instance
	 * 
	 * @return KalturaDistributionProvider
	 */
	public static function getKalturaProvider()
	{
		$distributionProvider = new KalturaHuluDistributionProvider();
		$distributionProvider->fromObject(self::getProvider());
		return $distributionProvider;
	}
	
	/**
	 * Append provider specific nodes and attributes to the MRSS
	 * 
	 * @param EntryDistribution $entryDistribution
	 * @param SimpleXMLElement $mrss
	 */
	public static function contributeMRSS(EntryDistribution $entryDistribution, SimpleXMLElement $mrss)
	{
		$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
		/* @var $distributionProfile HuluDistributionProfile */
		$mrss->addChild('SeriesChannel', $distributionProfile->getSeriesChannel());
		$mrss->addChild('SeriesPrimaryCategory', $distributionProfile->getSeriesPrimaryCategory());
		foreach($distributionProfile->getSeriesAdditionalCategories() as $category)
			$mrss->addChild('AdditionalCategories', $category);
		$mrss->addChild('SeasonNumber', $distributionProfile->getSeasonNumber());
		$mrss->addChild('SeasonSynopsis', $distributionProfile->getSeasonSynopsis());
		$mrss->addChild('SeasonTuneInInformation', $distributionProfile->getSeasonTuneInInformation());
		$mrss->addChild('MediaType', $distributionProfile->getVideoMediaType());
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getDistributionProviderTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IKalturaEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return kPluginableEnumsManager::apiToCore('DistributionProviderType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IKalturaEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

	
	/* (non-PHPdoc)
	 * @see IKalturaConfigurator::getConfig()
	 */
	public static function getConfig($configName)
	{
		if($configName == 'generator')
			return new Zend_Config_Ini(dirname(__FILE__) . '/config/generator.ini');
			
		return null;
	}
}
