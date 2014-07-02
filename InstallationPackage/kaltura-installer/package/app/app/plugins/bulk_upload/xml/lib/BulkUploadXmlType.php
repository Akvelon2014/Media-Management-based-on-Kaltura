<?php
/**
 * @package plugins.bulkUploadXml
 * @subpackage lib
 */
class BulkUploadXmlType implements IKalturaPluginEnum, BulkUploadType
{
	const XML = 'XML';
	
	/**
	 * 
	 * Returns the dynamic enum additional values
	 */
	public static function getAdditionalValues()
	{
		return array(
			'XML' => self::XML,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
