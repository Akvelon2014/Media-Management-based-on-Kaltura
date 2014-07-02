<?php
/**
 * @package Core
 * @subpackage model.filters
 */ 
class ControlPanelCommandFilter extends baseObjectFilter
{
	public function init ()
	{
		// TODO - should separate the schema of the fields from the actual values
		// or can use this to set default valuse
		$this->fields = kArray::makeAssociativeDefaultValue ( array (
			"_eq_id" , 
			"_in_id" ,
			"_eq_created_by_id" ,
			"_gte_created_at" ,
			"_lte_created_at" ,
			"_eq_type" ,
			"_in_type" ,
			"_eq_target_type" ,
			"_in_target_type" ,
			"_eq_status" ,
			"_in_status" ,
			) , NULL );
			
		$this->allowed_order_fields = array ( "created_at" , "updated_at" );
			
	}

	public function describe() 
	{
		return 
			array (
				"display_name" => "BatchJobFilter",
				"desc" => ""
			);
	}
	
	// TODO - move to base class, all that should stay here is the peer class, not the logic of the field translation !
	// The base class should invoke $peek_class::translateFieldName( $field_name , BasePeer::TYPE_FIELDNAME , BasePeer::TYPE_COLNAME );
	public function getFieldNameFromPeer ( $field_name )
	{
		$res = ControlPanelCommandPeer::translateFieldName( $field_name , $this->field_name_translation_type , BasePeer::TYPE_COLNAME );
		return $res;
	}

	public function getIdFromPeer (  )
	{
		return ControlPanelCommandPeer::ID;
	}
}

?>