<?php
/**
 * @package plugins.shortLink
 */
class ShortLinkPlugin extends KalturaPlugin implements IKalturaServices, IKalturaEventConsumers, IKalturaMemoryCleaner, IKalturaConfigurator
{
	const PLUGIN_NAME = 'shortLink';
	const SHORT_LINK_FLOW_MANAGER_CLASS = 'kShortLinkFlowManager';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'shortLink' => 'ShortLinkService',
		);
		return $map;
	}
	
	/**
	 * @return string - the path to services.ct
	 */
	public static function getServiceConfig()
	{
		return realpath(dirname(__FILE__).'/config/short_link.ct');
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::SHORT_LINK_FLOW_MANAGER_CLASS
		);
	}

	public static function cleanMemory()
	{
	    ShortLinkPeer::clearInstancePool();
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
