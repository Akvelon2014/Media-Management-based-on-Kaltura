<?php

/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute.Debug
 */

// /opt/kaltura/app/batch
chdir(dirname( __FILE__ ) . "/../../../../batch");

require_once("bootstrap.php");

$iniFile = "batch_config.ini";		// should be the full file path

$kdebuger = new KGenericDebuger($iniFile);
$kdebuger->run('KAsyncDistributeDisable');
