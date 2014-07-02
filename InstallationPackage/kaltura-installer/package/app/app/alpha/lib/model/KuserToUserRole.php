<?php


/**
 * Skeleton subclass for representing a row from the 'kuser_to_user_role' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class KuserToUserRole extends BaseKuserToUserRole {

	public function getCacheInvalidationKeys()
	{
		return array("kuserToUserRole:kuserId=".$this->getKuserId());
	}
	
} // KuserToUserRole
