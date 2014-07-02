<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use KalturaAccessControlProfileArray instead
 */
class KalturaAccessControlArray extends KalturaTypedArray
{
	public static function fromDbArray($arr)
	{
		$newArr = new KalturaAccessControlArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new KalturaAccessControl();
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("KalturaAccessControl");	
	}
}