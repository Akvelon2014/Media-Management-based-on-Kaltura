<?php
/**
 * @package plugins.ideticDistribution
 * @subpackage api.objects
 */
class KalturaIdeticDistributionProfile extends KalturaConfigurableDistributionProfile
{	
	/**
	 * @var string
	 */	
	public $ftpPath;
	/**
	 * @var string
	 */
	public $username;
	
	/**
	 * @var string
	 */
	public $password;
	
	/**
	 * @var string
	 */
	public $domain;

	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array 
	(
		'ftpPath',
		'username',
		'password',
		'domain',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}