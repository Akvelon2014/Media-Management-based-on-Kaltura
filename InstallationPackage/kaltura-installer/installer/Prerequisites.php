<?php

include_once(realpath(dirname(__FILE__)).'/DatabaseUtils.class.php');
include_once(realpath(dirname(__FILE__)).'/Log.php');

define("FILE_PREREQUISITES_CONFIG", "prerequisites.ini"); // this file contains the definitions of the prerequisites that are being checked

$usage_string = 'Usage is php '.__FILE__.' <apachectl> <db host> <db port> <db user> <db pass>'.PHP_EOL;
$usage_string .= 'Prints all the missing prerequisites and exits with code 0 if all verifications passes and 1 otherwise'.PHP_EOL;

if (count($argv) < 5) {
	echo $usage_string;
	exit(1);
}

// get user arguments
$db_params = array();
$httpd_bin = trim($argv[1]);
$db_params['db_host'] = trim($argv[2]);
$db_params['db_port'] = trim($argv[3]);
$db_params['db_user'] = trim($argv[4]);
if (count($argv) > 5) $db_params['db_pass'] = trim($argv[5]);
else $db_params['db_pass'] = "";

$prerequisites_config = parse_ini_file(FILE_PREREQUISITES_CONFIG, true);
$prerequisites = "";

// check php version
if (!checkVersion(phpversion(), $prerequisites_config["php_min_version"])) {
	$prerequisites .= "PHP version should be >= $php_min_version (current version is ".phpversion().")".PHP_EOL;
}

// check php extensions
foreach ($prerequisites_config["php_extensions"] as $ext) {
	if (!extension_loaded($ext)) {
		$prerequisites .= "Missing $ext PHP extension".PHP_EOL;
	}
}

// check mysql
if (!extension_loaded('mysqli')) {
	$prerequisites .= "Cannot check MySQL connection, version and settings because PHP mysqli extension is not loaded".PHP_EOL;
} else if (!DatabaseUtils::connect($link, $db_params, null)) {
		$prerequisites .= "Failed to connect to database ".$db_params['db_host'].":".$db_params['db_port']." user:".$db_params['db_user'].". Please check the database settings you provided and verify that MySQL is up and running.".PHP_EOL;
} else {
	// check mysql version and settings
	$mysql_version = getMysqlSetting($link, 'version'); // will always return the value
	if (!checkVersion($mysql_version, $prerequisites_config["mysql_min_version"])) {
		$prerequisites .= "MySQL version should be >= ".$prerequisites_config["mysql_min_version"]." (current version is $mysql_version)".PHP_EOL;
	}
	
	$lower_case_table_names = getMysqlSetting($link, 'lower_case_table_names');
	if (!isset($lower_case_table_names)) {
		$prerequisites .= "Please set 'lower_case_table_names = ".$prerequisites_config["lower_case_table_names"]."' in my.cnf and restart MySQL".PHP_EOL;
	} else if (intval($lower_case_table_names) != intval($prerequisites_config["lower_case_table_names"])) {
		$prerequisites .= "Please set 'lower_case_table_names = ".$prerequisites_config["lower_case_table_names"]."' in my.cnf and restart MySQL (current value is $lower_case_table_names)".PHP_EOL;
	}
	
	$thread_stack = getMysqlSetting($link, 'thread_stack');
	if (!isset($thread_stack)) {
		$prerequisites .= "Please set 'thread_stack >= ".$prerequisites_config["thread_stack"]."' in my.cnf and restart MySQL".PHP_EOL;
	} else if (intval($thread_stack) < intval($prerequisites_config["thread_stack"])) {
		$prerequisites .= "Please set 'thread_stack >= ".$prerequisites_config["thread_stack"]."' in my.cnf and restart MySQL (current value is $thread_stack)".PHP_EOL;
	}	
}

// check apache modules
@exec("$httpd_bin -M 2>&1", $current_modules, $exit_code);
if ($exit_code !== 0) {
	$prerequisites .= "Cannot check apache modules, please make sure that '$httpd_bin -t' command runs properly".PHP_EOL;
} else {	
	foreach ($prerequisites_config["apache_modules"] as $module) {
		$found = false;
		for ($i=0; $i<count($current_modules); $i++) {
			$currentModule = trim($current_modules[$i]);
			if (strpos($currentModule,$module) === 0) {
				$found = true;
				break;
			}				
		}
		
		if (!$found) {
			$prerequisites .= "Missing $module Apache module".PHP_EOL;
		}
	}
}	

// check binaries
foreach ($prerequisites_config["binaries"] as $bin) {
	@exec("which $bin", $output, $exit_code);		
	if ($exit_code !== 0) {
		$prerequisites .= "Missing $bin binary file".PHP_EOL;
	}
}

// Hagai: Check that SELinux is not enabled (enforcing)
exec("getenforce", $statusresponse, $exit_code);
if ($exit_code !== 0) {
	// $prerequisites .= "Could not resolve SE-Linux status, run again.".PHP_EOL;
} elseif(!empty($statusresponse[0])) {
	if(!strcmp($statusresponse[0],'Enforcing')) {
		$prerequisites .= "SE linux is Enabled, please disable.".PHP_EOL;
	}
}

// check pentaho exists
$pentaho = $prerequisites_config["pentaho_path"];
if (!is_file($pentaho)) {
	$prerequisites .= "Missing pentaho at $pentaho".PHP_EOL;
}

// check if something is missing and exit accordingly
if (empty($prerequisites)) {
	exit(0);
} else {	
	echo $prerequisites;
	exit(1);
}

// checks if the mysql settings $key is as $expected using the db $link
// if $allow_greater it also checks if the value is greater the the $expected (not only equal)
function getMysqlSetting(&$link, $key) {	
	$result = mysqli_query($link, "SELECT @@$key;");
	if ($result === false) {
		return null;
	} else {			
		$tmp = '@@'.$key;
		$current = $result->fetch_object()->$tmp;
		return $current;
	}		
}

// check if the given $version is equal or bigger than the $expected
// both $version and $expected are version strings which means that they are numbers separated by dots ('.')
// if $version has less parts, the missing parts are treated as zeros
function checkVersion($version, $expected) {
	$version_parts = explode('.', $version);
	$expected_parts = explode('.', $expected);
	
	for ($i=0; $i<count($expected_parts); $i++) {
		// allow the version to have less parts than the expected, fill the missing with zeros
		$comparison = 0;
		if ($i < count($version_parts)) {
			$comparison = intval($version_parts[$i]);
		}
	
		// if the part is smaller the version is not ok
		if ($comparison < intval($expected_parts[$i])) {
			return false;
		// if the part is bigger the version is ok
		} else if ($comparison > intval($expected_parts[$i])) {
			return true;
		}		
	}
	
	return true;
}
