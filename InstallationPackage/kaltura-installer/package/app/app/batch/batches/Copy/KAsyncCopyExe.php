<?php
/**
 * Executes the KAsyncCopy
 * 
 * @package Scheduler
 * @subpackage Copy
 */
require_once("bootstrap.php");

$instance = new KAsyncCopy();
$instance->run(); 
$instance->done();
