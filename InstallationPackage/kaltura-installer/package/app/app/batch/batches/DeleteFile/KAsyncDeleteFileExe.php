<?php
/**
 * Will run KAsyncStorageDelete.class.php 
 * 
 *
 * @package Scheduler
 * @subpackage Storage
 */
require_once("bootstrap.php");

$instance = new KAsyncDeleteFile();
$instance->run(); 
$instance->done();