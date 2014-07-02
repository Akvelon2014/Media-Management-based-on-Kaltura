<?php
/**
 * @package Core
 * @subpackage model.filters
 */ 
class ConversionProfileFilter extends baseObjectFilter
{
	public function init ()
	{
		// TODO - should separate the schema of the fields from the actual values
		// or can use this to set default valuse
		$this->fields = kArray::makeAssociativeDefaultValue ( array (
			"_eq_id" , 
			"_gte_id" ,
			"_eq_status" ,
			"_like_name" ,
			"_eq_profile_type",
			"_in_profile_type",
			"_eq_enabled" ,
			"_eq_type" ,
			"_eq_use_with_bulk" ,
		 	"_eq_partner_id" ,
			) , NULL );
			
		
		$this->allowed_order_fields = array ( "created_at" , "profile_type"  )	;
	}

	public function describe() 
	{
		return 
			array (
				"display_name" => "ConvesionProfileFilter",
				"desc" => ""
			);
	}
	
	// TODO - move to base class, all that should stay here is the peer class, not the logic of the field translation !
	// The base class should invoke $peek_class::translateFieldName( $field_name , BasePeer::TYPE_FIELDNAME , BasePeer::TYPE_COLNAME );
	public function getFieldNameFromPeer ( $field_name )
	{
		$res = ConversionProfilePeer::translateFieldName( $field_name , $this->field_name_translation_type , BasePeer::TYPE_COLNAME );
		return $res;
	}

	public function getIdFromPeer (  )
	{
		return ConversionProfilePeer::ID;
	}
}

?>