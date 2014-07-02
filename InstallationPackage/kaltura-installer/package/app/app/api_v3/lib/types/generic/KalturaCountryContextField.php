<?php
/**
 * Represents the current request country context as calculated based on the IP address
 * 
 * @package api
 * @subpackage objects
 */
class KalturaCountryContextField extends KalturaStringField
{
	/**
	 * The ip geo coder engine to be used
	 * 
	 * @var KalturaGeoCoderType
	 */
	public $geoCoderType = geoCoderType::KALTURA;
	
	private $map_between_objects = array
	(
		'geoCoderType',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), $this->map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new kCountryContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}