<?php
/**
 * @package deployment
 * @subpackage falcon.roles_and_permissions
 * 
 * Adds batch entit permissions
 * 
 * No need to re-run after server code deploy
 */

$script = realpath(dirname(__FILE__) . '/../../../../') . '/scripts/utils/permissions/addPermissionsAndItems.php';
$config = realpath(dirname(__FILE__)) . '/configs/entitlement_partner_category.ini';
passthru("php $script $config");