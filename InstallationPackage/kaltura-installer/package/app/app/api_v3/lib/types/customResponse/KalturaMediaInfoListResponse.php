<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaMediaInfoListResponse extends KalturaObject
{
	/**
	 * @var KalturaMediaInfoArray
	 * @readonly
	 */
	public $objects;

	/**
	 * @var int
	 * @readonly
	 */
	public $totalCount;
}