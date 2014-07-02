<?php

define('YESNO_REGEX', '/^(y|yes|n|no)$/i');

$dbs_to_drop = array ( 
	'kaltura',
	'kalturadw',
	'kalturadw_ds',
	'kalturadw_bisources',
	'kalturalog',
	'kaltura_sphinx_log'
);
	
// returns true or false according to the user input, if empty return $default
function getTrueFalse($default) {
	$inputOk = false;
	while (!$inputOk) {
		echo '> ';
		$input = trim(fgets(STDIN));
		
		if (empty($input)) {
			return $default;
		} else if (preg_match(YESNO_REGEX, $input) === 1) {
			$inputOk = true;
		} else {
			echo "Input invalid, must be y/n/yes/no".PHP_EOL;
		}
	}
	return ((strcasecmp('y',$input) === 0) || (strcasecmp('yes',$input) === 0));	
}

// execute a shell command and returns, returns true if succeeds, false otherwise
function execute($command) {
	@exec($command . ' 2>&1', $output, $return_var);
	return ($return_var === 0);
}
	
// connect to a db, returns true if succeeds, false otherwise
function connect(&$link, $host, $user, $pass, $db, $port) {
	// set mysqli to connect via tcp
	if ($host == 'localhost') {
		$host = '127.0.0.1';
	}
	if (trim($pass) == '') $pass = null;
	
	$link = @mysqli_init();
	$result = @mysqli_real_connect($link, $host, $user, $pass, $db, $port);	
	if (!$result) {
		return false;
	}
	return true;
}

// executes a db query, returns true if succeeds, false otherwise
function executeQuery($query, $host, $user, $pass, $db, $port, $link = null) {
	if (!$link && !connect($link, $host, $user, $pass, $db, $port)) return false;
	else if (isset($db) && !mysqli_select_db($link, $db)) return false;

	if (!mysqli_multi_query($link, $query) || $link->error != '') return false;		
	
	while (mysqli_more_results($link) && mysqli_next_result($link)) {
		$discard = mysqli_store_result($link);
	}
	$link->commit();
	return true;
}

function isDbExist($db, $host, $user, $pass, $port)
{
	$link = null;
	return connect($link, $host, $user, $pass, $db, $port);
}

// drops a db, returns true if succeeds, false otherwise
function dropDb($db, $host, $user, $pass, $port) {
	$drop_db_query = "DROP DATABASE $db;";
	return executeQuery($drop_db_query, $host, $user, $pass, null, $port);
}

function deleteTextFromFile($filePath, $text){
	$data = file_get_contents($filePath);
	while(strstr($data,$text)){
		$textPos = strpos($data,$text);
		$startData = substr($data,0,$textPos);
		$endData = substr($data,$textPos + strlen($filePath));
		$startData = substr($startData,0,strrpos($startData,PHP_EOL));
		$endData = substr($endData,strpos($endData,PHP_EOL));
		$data = $startData . $endData;
	}
	file_put_contents($filePath,$data);
}
	
$silentRun = false;
if($argc > 1 && $argv[1] == '-s') $silentRun = true;
$config = parse_ini_file("uninstall.ini");
$success = true;
echo 'Uninstaller will fully remove the Kaltura software from your system.'.PHP_EOL;
echo 'Databases and any uploaded content will also be removed.'.PHP_EOL;
echo 'This action cannot be undone. Do you wish to continue? (y/N)'.PHP_EOL;
if ((!$silentRun) && (!getTrueFalse(false))) {
	echo 'Uninstallation was cancelled, uninstaller will now exit.'.PHP_EOL;
	die(0);
}
//We first want to clear the crontab symbolic link so we can remove all services without them returning
if(is_array($config['symlinks']))
{
	foreach ($config['symlinks'] as $slink) {
		if(is_link($slink))
		{
			echo 'Removing '.$slink.'... ';
			if (execute('rm -rf ' . $slink)) {
				echo 'OK'.PHP_EOL;
			} else {
				echo 'Failed'.PHP_EOL;
				$success = false;
			}
		}
	}
}

echo 'Stopping sphinx daemon and sphinx... ';
if (execute($config['BASE_DIR'].'/app/plugins/sphinx_search/scripts/watch.stop.sh')) {
	echo 'OK'.PHP_EOL;
} else {
	echo 'Failed'.PHP_EOL;
	$success = false;
}

echo 'Stopping the batch manager... ';
if (execute($config['BASE_DIR'].'/app/scripts/serviceBatchMgr.sh stop')) {
	echo 'OK'.PHP_EOL;
} else {
	echo 'Failed'.PHP_EOL;
	$success = false;
}

echo 'Deleting dwh pentaho directories... ';
if (execute($config['BASE_DIR'].'/dwh/setup/cleanup.sh')) {
	echo 'OK'.PHP_EOL;
} else {
	echo 'Failed'.PHP_EOL;
	$success = false;
}

//TODO: Kaltura user currently does not created
//echo 'Deleting kaltura user... ';
//if (execute('userdel kaltura')) {
//	echo 'OK'.PHP_EOL;
//} else {
//	echo 'Failed'.PHP_EOL;
//	$success = false;
//}
echo 'Would you like to drop the KalturaDB? (y/N)'.PHP_EOL;
if (getTrueFalse(true))
{
	foreach ($dbs_to_drop as $db) {
		if(isDbExist($db, $config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_PORT']))
		{
			echo "Dropping '$db' database... ";
			if (dropDb($db, $config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_PORT'])) {
				echo 'OK'.PHP_EOL;
			} else {
				echo 'Failed'.PHP_EOL;
				$success = false;
			}
		}
	}
}

echo "Removing ".$config['BASE_DIR']."... ";
if (execute("rm -rf ".$config['BASE_DIR'])) {
	echo 'OK'.PHP_EOL;
} else {
	echo 'Failed'.PHP_EOL;
	$success = false;
}

echo "Removing apache and red5 symlinks...";
if (!execute("rm -f /etc/init.d/red5"))
	echo "Failed to remove the red5 symlink from /etc/init.d/red5, maybe red5 was not installed..";
if (!execute("rm -f /etc/httpd/conf.d/my_kaltura.ssl.conf"))
	echo "Failed to delete my_kaltura.ssl.conf symlink from /etc/httpd/conf.d";

	
if ($success) echo 'Uninstall finished successfully'.PHP_EOL;
else echo 'Some of the uninstall steps failed, please complete the process manually'.PHP_EOL;
echo 'Please maually remove Kaltura-related symbolic links in /etc/httpd/conf.d'.PHP_EOL;

