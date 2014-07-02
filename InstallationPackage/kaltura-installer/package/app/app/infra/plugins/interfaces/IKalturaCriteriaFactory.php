<?php
/**
 * Enable the plugin to return extended KalturaCriteria object according to the searched object type
 * @package infra
 * @subpackage Plugins
 */
interface IKalturaCriteriaFactory extends IKalturaBase
{
	/**
	 * Creates a new KalturaCriteria for the given object name
	 * 
	 * @param string $objectType object type to create Criteria for.
	 * @return KalturaCriteria derived object
	 */
	public static function getKalturaCriteria($objectType);
}