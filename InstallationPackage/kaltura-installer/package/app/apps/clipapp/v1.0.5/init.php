<?php

// Load configuration
require_once('config.php');

// Decide which config to use
if( isset($_GET['config']) ) {
	$configName = $_GET['config'];
} else {
	$configName = 'default';
}

// Load local configuration
if( file_exists('config.local.php') )
	include('config.local.php');

// If we use local configuration, merge it with our default one
if( $configName != 'default' && isset($config[$configName]) ) {
	$conf = array_merge( $config['default'], $config[$configName] );
} else {
	// Else, use default configuration
	$conf = $config['default'];
}

// Load Kaltura Client
require_once('client/KalturaClient.php');

try {
	// Return a Client
	$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
	$config = new KalturaConfiguration( $conf['partner_id'] );
	$config->serviceUrl = $protocol . '://' . $conf['host'];
	$client = new KalturaClient( $config );

	// Create & Set KS
	if( isset($conf['ks']) ) {
		$ks = $conf['ks'];
	} else {
		if( isset( $save ) ) {
			$sessionType = KalturaSessionType::ADMIN;
			$sessionSecret = $conf['adminsecret'];
		} else {
			$sessionType = KalturaSessionType::USER;
			$sessionSecret = $conf['usersecret'];
		}
		$ks = $client->session->start($sessionSecret, $conf['user_id'], $sessionType, $conf['partner_id'], null, null);
	}
	$client->setKs($ks);
} catch( Exception $e ){
	$error = '<h1>Error</h1>' . $e->getMessage();
	die($error);
}
// Reset admin secret just in case
$conf['usersecret'] = null;
$conf['adminsecret'] = null;
