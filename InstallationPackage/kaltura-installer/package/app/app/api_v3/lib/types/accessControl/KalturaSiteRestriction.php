<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use KalturaRule instead
 */
class KalturaSiteRestriction extends KalturaBaseRestriction 
{
	/**
	 * The site restriction type (allow or deny)
	 * 
	 * @var KalturaSiteRestrictionType
	 */
	public $siteRestrictionType;
	
	/**
	 * Comma separated list of sites (domains) to allow or deny
	 * 
	 * @var string
	 */
	public $siteList;
	
	private static $mapBetweenObjects = array
	(
		"siteRestrictionType",
		"siteList",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaBaseRestriction::toRule()
	 */
	public function toRule(KalturaRestrictionArray $restrictions)
	{
		return $this->toObject(new kAccessControlSiteRestriction());
	}
}