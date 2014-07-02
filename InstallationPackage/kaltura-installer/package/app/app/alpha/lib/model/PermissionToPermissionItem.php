<?php


/**
 * Skeleton subclass for representing a row from the 'permission_to_permission_item' table.
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
class PermissionToPermissionItem extends BasePermissionToPermissionItem {

	public function getCacheInvalidationKeys()
	{
		return array("permissionToPermissionItem:permissionId=".$this->getPermissionId());
	}
} // PermissionToPermissionItem
