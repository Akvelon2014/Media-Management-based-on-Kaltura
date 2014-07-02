<?php
# This script retrieves apahce logs for use via Pentaho. 
# Currently used for FMS processing and Akamai bandwidth usage processing.
#

require_once dirname(__FILE__).'/filter_file_list.php';
require_once dirname(__FILE__).'/pentaho_decrypt.php';

// Check argument variables
if (count($argv) < 9)
{
	echo count($argv) . " parameters found.\n";
	echo "Usage: php fetch_apahce_logs.php <logsdir> <wildcard> <write_dir> <dbhost> <dbport> <dbuser> <dbpass> <processid> \n";
	exit(2);
}

// Get argument variables
$logsdir = $argv[1];
$wildcard = "/^".$argv[2]."$/";
$write_dir = $argv[3];
$dbhost = $argv[4];
$dbport = $argv[5];
$dbuser = $argv[6];
$dbpassenc = $argv[7];
$processid = $argv[8];

$dbpass = decrypt($dbpassenc);

echo "Copying to ".$write_dir."\n";

echo "Getting file list...\n";
if (!file_exists($logsdir))
{
	echo "Dir: ". $logsdir ." does not exist\n";
	exit(1);
}

$dirHandle = opendir("$logsdir");

$file_list = array();
//List files in images directory
while (($file = readdir($dirHandle)) !== false)
{
	if (is_file($logsdir . "/" . $file))
	{
        	$file_list[] = $file;
	}
}

closedir($dirHandle); 

$filtered_file_list = preg_grep( $wildcard, $file_list );
if (count($filtered_file_list)==0)
{
        echo "No files to copy from the logs dir.\n";
        exit(0);
}

$files_to_retrieve = filter_file_list($filtered_file_list, $processid, $dbhost, $dbport, $dbuser, $dbpass);
foreach ($files_to_retrieve as $file)
{
	echo "Copying ".$file."\n";
	copy($logsdir.'/'.$file, $write_dir.'/'.$file);
}
?>
