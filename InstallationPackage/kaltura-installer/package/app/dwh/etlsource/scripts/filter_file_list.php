<?php
# This script provides an ability to filter file list according to the registered files of the DWH
#
# Requires a PHP with Propel installed.

require_once 'propel/Propel.php';

function filter_file_list($file_list, $processid, $dbhost, $dbport, $dbuser, $dbpass)
{
	// Get files from MySQL
	echo "Getting registered files from mysql for process_id ".$processid.".\n";
	$dsn = 'mysql:host='.$dbhost.';dbname=kalturadw_ds;port='.$dbport;
        echo "Connecting to database...";
        try {
	        $dbcon = new PDO($dsn, $dbuser,$dbpass);
        } catch ( PDOException $e ) {
        	echo "Could not connect to database ".$dbhost."@".$dbport." with u/p ".$dbuser."/".$dbpass."\n";
        	echo "DB Connection Error: " . $e->getMessage(). "\n";
                exit(1);
        }
	$files_unregistered = array();
	$files_registered = array();
	$query=	"SELECT DISTINCT IF(SUBSTR(file_name,1,6)='split_'," 
						."SUBSTR(file_name, 7,LENGTH(file_name)-9),"
						."file_name) file_name "
			."FROM kalturadw_ds.files WHERE process_id = ".$processid;
        try {
              	foreach ($dbcon->query($query) as $line)
               	{
			array_push($files_registered, $line[0]);
               	}
        } catch (PDOException $e) {
        	print "DB Query Error: " . $e->getMessage() . "\n";
        	exit(1);
        }
	//print_r ($files_registered);
	foreach ($file_list as $file){
		if (!in_array(preg_replace("/\.gz$/", "", $file), $files_registered))
		{
			array_push($files_unregistered, $file);
		}
	}
	return $files_unregistered;	
}
	
?>
