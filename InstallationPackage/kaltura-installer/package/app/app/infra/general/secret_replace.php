<?php 
error_reporting(E_ALL); //TODO: remove
DEFINE('APP_DIR', dirname(__FILE__).'/../../');



// --- configurations
$admin_partner_id = '-2';
$batch_partner_id = '-1';
$admin_console_config_file = APP_DIR.'/admin_console/configs/application.ini';
$batch_config_file = APP_DIR.'/batch/batch_config.ini';
$batch_full_status_file = APP_DIR.'/batch/monitor/fullstatus.php';
$kconf_file = APP_DIR.'/configurations/local.ini';



// --- script start

require_once(APP_DIR.'/alpha/config/sfrootdir.php');
require_once(APP_DIR.'/api_v3/bootstrap.php');


DbManager::setConfig(kConf::getDB());
DbManager::initialize();

// replace admin console partner secrets
$admin_partner = PartnerPeer::retrieveByPK($admin_partner_id);
$admin_partner->setSecret(generate_secret());
$admin_partner->setAdminSecret(generate_secret());
$admin_partner->save();

// replace admin console config file secret to match
replace_in_file(
	$admin_console_config_file,
	'/settings.secret(\s)*=(\s)*(.+)/',
	'settings.secret = '.$admin_partner->getAdminSecret()
);


// replace batch partner secrets
$batch_partner = PartnerPeer::retrieveByPK($batch_partner_id);
$batch_partner->setSecret(generate_secret());
$batch_partner->setAdminSecret(generate_secret());
$batch_partner->save();

// replace batch config file secret to match
replace_in_file(
	$batch_config_file,
	'/secret(\s)*=(\s)*(.+)/',
	'secret = '.$batch_partner->getAdminSecret()
);

// replace batch full status script secret to match
replace_in_file(
	$batch_full_status_file,
	'/\$secret(\s)*=(\s)*(.+)/',
	'$secret = \''.$batch_partner->getAdminSecret().'\';'
);


// replace kconf system pages login password
replace_in_file(
	$kconf_file,
	'/system_pages_login_password(\s)*=(\s)*(.+)/',
	"system_pages_login_password = ".generate_secret()
);


// change parameter in kconf so secret replacement will not happen again
replace_in_file(
	$kconf_file,
	'/replace_passwords(\s)*=(\s)*(.+)/',
	"replace_passwords = false"
);


//------------------------------------------------

function replace_in_file($file_name, $regexp, $replace)
{
	$file_data = file_get_contents($file_name);
	$file_data = preg_replace($regexp, $replace, $file_data);
	@file_put_contents($file_name, $file_data);
}

function generate_secret()
{
	$secret = md5(str_makerand(5,10,true, false, true));
	return $secret;
}


function str_makerand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
{
	$charset = "abcdefghijklmnopqrstuvwxyz";
	if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if ($usenumbers) $charset .= "0123456789";
	if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
	if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
	else $length = mt_rand ($minlength, $maxlength);
	$key = "";
	for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
	return $key;
}
