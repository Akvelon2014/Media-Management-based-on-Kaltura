#!/usr/bin/php
<?php
/*
 * Created on Apr 18, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once(realpath(dirname(__FILE__)).'/../config/sfrootdir.php');
define('SF_APP',         'kaltura');
define('SF_ENVIRONMENT', 'batch');
define('SF_DEBUG',       true);

require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'lib/batch/myBatchPartnerUsage.class.php');

ini_set('memory_limit', '2048M');
kCurrentContext::$ps_vesion = 'ps2';
$batchClient = new myBatchPartnerUsage();

?>
