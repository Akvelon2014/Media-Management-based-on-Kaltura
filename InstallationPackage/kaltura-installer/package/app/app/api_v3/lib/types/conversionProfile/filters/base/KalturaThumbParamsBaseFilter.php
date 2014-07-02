<?php
/**
 * @package api
 * @subpackage filters.base
 * @abstract
 */
abstract class KalturaThumbParamsBaseFilter extends KalturaAssetParamsFilter
{
	private $map_between_objects = array
	(
		"formatEqual" => "_eq_format",
	);

	private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), $this->map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), $this->order_by_map);
	}

	/**
	 * @var KalturaContainerFormat
	 */
	public $formatEqual;
}
