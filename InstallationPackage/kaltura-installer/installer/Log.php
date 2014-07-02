<?php

define("L_USER","USER"); // user level logging constant
define("L_ERROR","ERROR"); // error level logging constant
define("L_WARNING","WARNING"); // warning level logging constant
define("L_INFO","INFO"); // info level logging constant
define("L_DATE_FORMAT","d.m.Y H:i:s"); // log file date format

$logFile = null;
$logPrintLevel=0; // screen print log level, 0=user, 1=error, 2=warning, 3=info

// start a new log with the given $filename
function startLog($filename) {
	global $logFile;
	$logFile = $filename;
	OsUtils::writeFile($logFile, "");
}

// log a $message in the given $level, will print to the screen according to the log level
// if $new_line = false, no new line will be printed (default is to print a new line)
function logMessage($level, $message, $new_line = true) {
	global $logFile, $logPrintLevel;
	
	if (!isset($logFile))
		return;
		
	$message = str_replace("\\n", PHP_EOL, $message);
	$message = str_replace("\\t", "\t", $message);
	$logLine = date(L_DATE_FORMAT).' '.$level.' '.$message.PHP_EOL;
	OsUtils::appendFile($logFile, $logLine);	
	
	// print to screen according to log level
	if ((($level === L_USER) && ($logPrintLevel >= 0)) ||
		(($level === L_ERROR) && ($logPrintLevel >= 1)) ||
		(($level === L_WARNING) && ($logPrintLevel >= 2)) ||
		(($level === L_INFO) && ($logPrintLevel >= 3))) {
		echo $message;
		
		if ($new_line)
			echo PHP_EOL;		
	}
}
