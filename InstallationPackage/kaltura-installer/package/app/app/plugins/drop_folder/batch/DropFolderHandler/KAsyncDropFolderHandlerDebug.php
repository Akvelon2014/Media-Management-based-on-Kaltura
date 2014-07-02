<?php

/**
 * @package plugins.dropFolder
 * @subpackage Scheduler.Debug
 */

// /opt/kaltura/app/batch
chdir(dirname( __FILE__ ) . "/../../../../batch");

require_once("bootstrap.php");

$iniFile = "../configurations/batch.ini";		// should be the full file path

$kdebuger = new KGenericDebuger($iniFile);
$kdebuger->run('KAsyncDropFolderHandler');
