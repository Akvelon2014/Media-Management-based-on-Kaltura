<?php
# This script retrieves apahce logs for use via Pentaho. 
# Currently used for FMS processing and Akamai bandwidth usage processing.
#

require_once dirname(__FILE__).'/filter_file_list.php';
require_once dirname(__FILE__).'/pentaho_decrypt.php';

// Check argument variables
if (count($argv) < 10)
{
	echo count($argv) . " parameters found.\n";
	echo "Usage: php fetch_apahce_logs.php <logsdir> <wildcard> <subdirWildcard> <write_dir> <dbhost> <dbport> <dbuser> <dbpass> <processid> \n";
	exit(2);
}

// Get argument variables
$logsdir = $argv[1];
$wildcard = "/^".$argv[2]."$/";
$subdir_wildcard = "/^".$argv[3]."$/";
$write_dir = $argv[4];
$dbhost = $argv[5];
$dbport = $argv[6];
$dbuser = $argv[7];
$dbpassenc = $argv[8];
$processid = $argv[9];

$dbpass = decrypt($dbpassenc);
scan_dir_files($logsdir, $wildcard, $subdir_wildcard, $write_dir, $dbhost, $dbport, $dbuser, $dbpass, $processid);

function scan_dir_files($logsdir, $wildcard, $subdir_wildcard, $write_dir, $dbhost, $dbport, $dbuser, $dbpass, $processid) 
{
	if (!file_exists($logsdir))
	{
		echo "Dir: ". $logsdir ." does not exist\n";
		exit(1);
	}


	$dirHandle = opendir("$logsdir");

    	//List files in images directory
    	while (($file = readdir($dirHandle)) !== false)
    	{
        	$file_list[] = $file;
    	}

    	closedir($dirHandle); 

	$filtered_file_list = preg_grep($wildcard, $file_list);

	if (count($filtered_file_list)>0)
	{
		$files_to_retrieve = filter_file_list($filtered_file_list, $processid, $dbhost, $dbport, $dbuser, $dbpass);
		foreach ($files_to_retrieve as $file)
		{
			echo "Copying ".$file."\n";
			copy($logsdir.'/'.$file, $write_dir.'/'.$file);
		}
	}
	else
	{
        	echo "No files to copy from the logs dir(".$logsdir.").\n";
	}
	$filtered_subdir_list = preg_grep( $subdir_wildcard, $file_list );
	foreach ($filtered_subdir_list as $subdir)
        {
		if (is_dir($logsdir."/".$subdir))
		{
                      	echo "Scanning ".$subdir."\n";
                 	scan_dir_files($logsdir."/".$subdir, $wildcard, $subdir_wildcard, $write_dir, $dbhost, $dbport, $dbuser, $dbpass, $processid);
		}
	}
}
?>
