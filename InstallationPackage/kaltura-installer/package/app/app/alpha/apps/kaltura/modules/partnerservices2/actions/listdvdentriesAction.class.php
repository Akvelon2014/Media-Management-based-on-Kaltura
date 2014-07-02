<?php
/**
 * @package api
 * @subpackage ps2
 */
require_once 'listentriesAction.class.php';

/**
 * @package api
 * @subpackage ps2
 */
class listdvdentriesAction extends listentriesAction
{
	public function describe()
	{
		return 	
			array (
				"display_name" => "listDvdEntries",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"filter" => array ("type" => "entryFilter", "desc" => "")
						),
					"optional" => array (
						"detailed" => array ("type" => "boolean", "desc" => ""),
						"detailed_fields" => array ("type" => "string", "desc" => "A list of fields (that do not belong to the level of details) to add to the entry - separated by ','"),
						"page_size" => array ("type" => "integer", "default" => 10, "desc" => ""),
						"page" => array ("type" => "boolean", "default" => 1, "desc" => ""),
						"use_filter_puser_id" => array ("type" => "boolean", "desc" => ""),
						)
					),
				"out" => array (
					"count" => array ("type" => "integer", "desc" => ""),
					"page_size" => array ("type" => "integer", "desc" => ""),
					"page" => array ("type" => "integer", "desc" => ""),
					"entrys" => array ("type" => "*entry", "desc" => ""),
					"user" => array ("type" => "kuser", "desc" => ""),
					),
				"errors" => array (
				)
			); 
	}
	
	protected function ticketType()
	{
		return self::REQUIED_TICKET_REGULAR;
	}
	
	protected function joinOnDetailed () { return false;}
		
	// for this specific kshow list - the ticket is regular and the filter is for all
	// kshows for the current user only 
	protected function setExtraFilters ( entryFilter &$fields_set )
	{
		$fields_set->set( "_eq_type" , entryType::DVD );
		$this->setP ( "use_filter_puser_id" , "false" ); // don't mind filtering according to the puser/kuser
	}
	
	protected function getObjectPrefix () { return "dvdEntries"; }
}
?>