<?php

$config = array( 'default' => array() );

/* Friendly name for Clipping Application */
$config['default']['title'] = 'Clipping Application';

/*
 * Service Host URL
 * Description: Service host url, used by client and where we load the kdp & clipper swf files
 * Default: www.kaltura.com
 */
$config['default']['host'] = 'www.kaltura.com';

/*
 * Partner ID
 * You can get it from KMC > Settings > Integration Settings
 */
$config['default']['partner_id'] = '';

/*
 * User Secret - used for kclip
 * You can get it from KMC > Settings > Integration Settings
 */
$config['default']['usersecret'] = '';

/*
 * Admin Secret - used for saving the clip/trim
 * You can get it from KMC > Settings > Integration Settings
 */
$config['default']['adminsecret'] = '';

/*
 * User ID
 * You can get it from KMC > Settings > My User Settings
 */
$config['default']['user_id'] = '';

/*
 * Default Entry Id
 * You can also use 'entryId' GET parameter to overwrite it
 * EX: http://localhost/ClipApp/?entryId=1_sfrj36g3
 */
$config['default']['entry_id'] = '';

/*
 * Overwrite Entry
 * true - Trimming, when user save the clip it will replace the current entry
 * false - Clipping, when user save the clip, it will create new entry
 */
$config['default']['overwrite_entry'] = false;

/* Redirect the user after save: true/false */
$config['default']['redirect_save'] = false;

/* Redirect URL if 'redirect_save' is true */
$config['default']['redirect_url'] = 'http://www.kaltura.com';

/* Message the will appear after the user click 'Save' button - Clip mode */
$config['default']['clip_save_message'] = 'A new clip is now being created. This might take a few minutes. You can copy the embed code and the video will
play once the clip is ready.';

/* Message the will appear after the user click 'Save' button - Trim mode */
$config['default']['trim_save_message'] = 'The trimmed video is now converting. This might take a few mintues. You can copy the embed code and the video will
play once the clip is ready.';

/* Show embed code after saving: true/false */
$config['default']['show_embed'] = true;

/* Use HTML5 Embed code: true/false */
$config['default']['html5_embed'] = true;

/* KDP UIConf ID */
$config['default']['kdp_uiconf_id'] = 0;

/* Clipper UIConf ID */
$config['default']['clipper_uiconf_id'] = 0;