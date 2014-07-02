<?php
/**
 * @package plugins.externalMedia
 * @subpackage api.objects
 */
class KalturaExternalMediaEntryArray extends KalturaTypedArray
{
	public static function fromDbArray($arr)
	{
		$newArr = new KalturaExternalMediaEntryArray();
		if($arr == null)
			return $newArr;
		
		foreach($arr as $obj)
		{
			$nObj = new KalturaExternalMediaEntry();
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("KalturaExternalMediaEntry");	
	}
}