<?php
/**
 * @package plugins.cuePoint
 */
interface IKalturaCuePoint extends IKalturaCuePointXmlParser, IKalturaPermissions, IKalturaEnumerator, IKalturaPending, IKalturaObjectLoader, IKalturaSchemaContributor
{
	/**
	 * @param string $valueName the name of the value
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getCuePointTypeCoreValue($valueName);
	
	/**
	 * @param string $valueName the name of the value
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName);
}
