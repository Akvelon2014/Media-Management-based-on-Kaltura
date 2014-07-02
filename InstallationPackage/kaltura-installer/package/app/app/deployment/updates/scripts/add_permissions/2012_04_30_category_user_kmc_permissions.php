<?php
/**
 * @package deployment
 * @subpackage falcon.roles_and_permissions
 * 
 * Adds permissions for category entry service
 * 
 * No need to re-run after server code deploy
 */

$script = realpath(dirname(__FILE__) . '/../../../../') . '/scripts/utils/permissions/addPermissionsAndItems.php';
$config = realpath(dirname(__FILE__)) . '/configs/category_user_kmc_permissions.ini';
passthru("php $script $config");