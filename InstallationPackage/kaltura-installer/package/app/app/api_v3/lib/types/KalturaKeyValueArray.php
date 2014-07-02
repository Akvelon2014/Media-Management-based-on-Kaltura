<?php
/**
 * An array of KalturaKeyValue
 * 
 * @package api
 * @subpackage objects
 */
class KalturaKeyValueArray extends KalturaTypedArray
{
	public static function fromDbArray(array $pairs = null)
	{
		return self::fromKeyValueArray($pairs);
	}
	
	public static function fromKeyValueArray(array $pairs = null)
	{
		$pairsArray = new KalturaKeyValueArray();
		if($pairs && is_array($pairs))
		{
			foreach($pairs as $key => $value)
			{
				$pairObject = new KalturaKeyValue();
				$pairObject->key = $key;
				$pairObject->value = $value;
				$pairsArray[] = $pairObject;
			}
		}
		return $pairsArray;
	}
	
	public function __construct()
	{
		return parent::__construct("KalturaKeyValue");
	}
}
