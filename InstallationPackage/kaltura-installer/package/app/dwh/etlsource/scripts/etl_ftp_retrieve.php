<?php
# This script retrieves FTP files for use via Pentaho. 
# Currently used for FMS processing and Akamai bandwidth usage processing.
#

require_once dirname(__FILE__).'/filter_file_list.php';
require_once dirname(__FILE__).'/pentaho_decrypt.php';

// Check argument variables
if (count($argv) < 12)
{
	echo count($argv) . " parameters found.\n";
	echo "Usage: php etl_ftp_retrieve.php <ftphost> <ftpport> <ftpuser> <ftppass> <ftpwildcard> <write_dir> <dbhost> <dbport> <dbuser> <dbpass> <processid> <prefix>\n";
	exit(2);
}

// Get argument variables
$ftphost = $argv[1];
$ftpport = $argv[2];
$ftpuser = $argv[3];
$ftppassenc = $argv[4];
$ftpwildcard = "/^".$argv[5]."$/";
$write_dir = $argv[6];
$dbhost = $argv[7];
$dbport = $argv[8];
$dbuser = $argv[9];
$dbpassenc = $argv[10];
$processid = $argv[11];
$prefix = ( array_key_exists(12,$argv) ? $argv[12] : '' );

$ftppass = decrypt($ftppassenc); 
$dbpass = decrypt($dbpassenc);
if (!$ftppass or !$dbpass)
{
	echo "Could not determine FTP or DB password.\n";
	exit(1);
}
// Initialization
echo "Downloading to ".$write_dir."\n";

echo "Connecting to FTP...\n";

$dirIndicatorPosition = strpos($ftphost, "/");
$remote_dir = "";
if ($dirIndicatorPosition)
{
        $remote_dir = substr($ftphost, $dirIndicatorPosition);
        $ftphost = substr($ftphost, 0, $dirIndicatorPosition);

}

$ftpconn = ftp_connect( $ftphost, $ftpport );
if ( ! $ftpconn )
{
	echo "could not connect to ftp server ". $ftphost . " on port " . $ftpport . "\n";
	exit(1);
}

$ftp_login = ftp_login($ftpconn,$ftpuser,$ftppass);
if (! $ftp_login )
{
	echo "could not login to ftp server " . $ftphost . " with u/p " . $ftpuser . "/" . $ftppass . "\n";
	exit(1);
}
ftp_pasv($ftpconn,true);

if ($remote_dir)
{
        ftp_chdir($ftpconn, $remote_dir);
}

// Get a filtered file list from FTP
echo "Getting file list...";
$file_list=ftp_nlist($ftpconn,'.');
echo "Done\n";
if (!$file_list or count($file_list)==0)
{
	echo "No files to download from FTP.  FTP Directory is empty.\n";
	exit(0);
}

echo "Found ".count($file_list)." files. Filtering by wildcard: ".$ftpwildcard."\n";
$filtered_file_list = preg_grep( $ftpwildcard, $file_list );
if (count($filtered_file_list)==0)
{
	echo "No files to download from FTP.\n";
	exit(0);
}

echo "Found ".count($filtered_file_list)." files\n";
$files_to_retrieve = filter_file_list($filtered_file_list, $processid, $dbhost, $dbport, $dbuser, $dbpass);

echo count($files_to_retrieve)." new files found\nDownloading:\n";
foreach ($files_to_retrieve as $file)
{
	echo $prefix.$file."\n";
	if ( ! ftp_get($ftpconn,$write_dir."/".$prefix.$file,$file,FTP_BINARY) ) {
    exit(1);
	}
}

ftp_close($ftpconn);

?>
