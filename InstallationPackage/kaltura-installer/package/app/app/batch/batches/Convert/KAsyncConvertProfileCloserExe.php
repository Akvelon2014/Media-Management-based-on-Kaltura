<?php
/**
 * Will run KAsyncConvertProfileCloser
 *
 * @package Scheduler
 * @subpackage Conversion
 */
require_once("bootstrap.php");

$instance = new KAsyncConvertProfileCloser();
$instance->run(); 
$instance->done();
