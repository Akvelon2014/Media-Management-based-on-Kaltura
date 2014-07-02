<?php

echo " Starting to convert file into readable mode...\n";

// set files to read & write (make args if needed later)
$filename = "kalturaDataTest.sql";
$outfile = "kalturaDataOut.sql";
 
// open files for use
$fh = fopen($filename, "r") or die("Could not open file for reading!"); 
$fo = fopen($outfile, "w") or die("Could not open file for writing!"); 

// read file contents 
$data = fread($fh, filesize($filename)) or die("Could not read file!"); 

// add new lines between table definition and values and between table records 
$newtext = str_replace(",(", ",\n(", $data); 
$newtext = str_replace("values (", "values\n(", $newtext);

// make all nulls look the same
//$newtext = str_replace("null", "NULL", $newtext);
//$newtext = str_replace("''", "NULL", $newtext);

// convert regExp of format '2012-05-21 11:24:35' to 'now()' string
//$newtext = preg_replace('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', '2008-09-01 12:35:45', $newtext);
$newtext = preg_replace('/\'(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\'/', 'now()', $newtext);

// write new file contents to output file
fwrite($fo, $newtext);

// close files
fclose($fh);
fclose($fo);
echo " Finished converting the file into readable mode.";

?>