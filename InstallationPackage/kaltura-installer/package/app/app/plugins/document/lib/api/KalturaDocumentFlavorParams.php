<?php
/**
 * @package plugins.document
 * @subpackage api.objects
 */
class KalturaDocumentFlavorParams extends KalturaFlavorParams 
{
	public function toObject($object = null, $skip = array())
	{
		if(is_null($object))
			$object = new flavorParams();
		
		parent::toObject($object, $skip);
		$object->setType(DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::DOCUMENT));
	}
	
	private static $map_between_objects = array
	(
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}