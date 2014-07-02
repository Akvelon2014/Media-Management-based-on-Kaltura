<?php

if( $configName == 'kmc' ) {
	
	if( !isset($_COOKIE['kmcks']) || empty($_COOKIE['kmcks']) ) {
		die('Error: Missing KS');
	}

	if( !isset($_GET['partnerId']) ) {
		die('Error: Missing Partner ID');
	}

	if( !isset($_GET['kclipUiconf']) || !isset($_GET['kdpUiconf']) ) {
		die('Error: Missing Uiconfs for KDP/kClip');
	}

	$config['kmc'] = array(
		'host' => (isset($_GET['host'])) ? $_GET['host'] : $_SERVER['HTTP_HOST'],
		'partner_id' => intval($_GET['partnerId']),
		'user_id' => null,
		'ks' => $_COOKIE['kmcks'],
		'overwrite_entry' => ($_GET['mode'] == "trim") ? true : false,
		'clipper_uiconf_id' => intval($_GET['kclipUiconf']),
		'kdp_uiconf_id' => intval($_GET['kdpUiconf']),
		'show_embed' => false,
		'trim_save_message' => 'The trimmed video is now converting. This might take a few minutes. Please close the window to continue.',
		'clip_save_message' => 'A new entry "<strong>@title@</strong>" has been created.<br />Entry ID: <strong>@entryId@</strong><br /><br />You can close this window to view the new clip in the entries table or create another clip.',
	);

}