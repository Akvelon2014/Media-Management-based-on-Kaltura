<?php

include_once('installer/OsUtils.class.php');
include_once('installer/Log.php');

// start the log
startLog("pre_install_log_".date("d.m.Y_H.i.s"));
logMessage(L_USER, 'start');
$currWD = getcwd();
logMessage(L_USER, "dir: $currWD");
$kalturaUserName = 'kaltura';
$kalturaUserPassword = 'batchUserPassw0r462';

for ($i = 1; $i < $argc; $i++) {
    if(($argv[$i] != '-s') && ($argv[$i] != '-c')){
    	$kalturaUserPassword = $argv[$i];
    }
}

logMessage(L_USER, 'add user');
if (OsUtils::execute("useradd $kalturaUserName -g root")) {	
	logMessage(L_USER, 'create password');
	if (!OsUtils::execute("passwd $kalturaUserName <<EOF\n".$kalturaUserPassword."\n".$kalturaUserPassword."\nEOF 2>&1")) {
		logMessage(L_USER, 'Failed creating user password');
		return "\nFailed creating user password\n";
	}
}


//logMessage(L_USER, 'chmod');
//if (!OsUtils::execute("chmod -R 740 /home/$kalturaUserName ")) {
//echo "Failed chmod ";			
//return "\nFailed chmod \n";
//}


//logMessage(L_USER, 'chmod');
//if (!OsUtils::execute("chmod 664 /opt/kaltura/app/batch/KGenericBatchMgr.class")) {
//	echo "Failed chmod";			
//	return "\nFailed chmod\n";
//}

logMessage(L_USER, 'chmod');
if (!OsUtils::execute("chmod 770 /etc/cron.d")) {
	echo "Failed chmod";			
	return "\nFailed chmod\n";
}

logMessage(L_USER, 'chmod');
if (!OsUtils::execute("chmod 770 /etc/rc.d/init.d")) {
	echo "Failed chmod";			
	return "\nFailed chmod\n";
}


//logMessage(L_USER, 'adding group');
//if (!OsUtils::execute("groupadd $kalturaUserName")) {
//	logMessage(L_USER, 'Failed add group');			
//	return "\nFailed add group\n";
//}

logMessage(L_USER, 'adding ownership to user');
if (!OsUtils::execute("chown -R $kalturaUserName:root $currWD")) {
	logMessage(L_USER, 'Failed add ownership');			
	return "\nFailed adding ownership\n";
}

logMessage(L_USER, 'chmod home');
if (!OsUtils::execute("chmod -R 740 /home/$kalturaUserName")) {
	logMessage(L_USER, 'Failed chmod home');			
//	return "\nFailed chmod home\n";
}

logMessage(L_USER, 'chmod log');
if (!OsUtils::execute("chmod 777 /opt/instlBkgrndRun.log")) {
	logMessage(L_USER, 'Failed chmod log');			
	return "\nFailed chmod log\n";
}

logMessage(L_USER, 'chmod');
if (!OsUtils::execute("chmod 775 /var/lock/subsys/")) {
	echo "Failed chmod";			
	return "\nFailed chmod\n";
}

logMessage(L_USER, 'chmod');
if (!OsUtils::execute("chmod 775 /var/spool/cron")) {
	echo "Failed chmod";			
	return "\nFailed chmod\n";
}

logMessage(L_USER, 'chmod');
if (!OsUtils::execute("chmod -R 777 /usr/local/pentaho")) {
	echo "Failed chmod";			
	return "\nFailed chmod\n";
}

logMessage(L_USER, 'create installation directory');
if (!OsUtils::execute("mkdir /opt/kaltura")) {
	logMessage(L_USER, 'Failed creating installation directory');			
	return "\nFailed creating installation directory\n";
}

logMessage(L_USER, 'change ownership of installation directory');
if (!OsUtils::execute("chown -R $kalturaUserName:root /opt/kaltura")) {
	logMessage(L_USER, 'Failed change ownership of  installation directory');			
	return "\nFailed change ownership of installation directory\n";
}

logMessage(L_USER, 'chmod installer');
if (!OsUtils::execute("chmod 744 install.php")) {
	logMessage(L_USER, 'Failed chmod installer');			
	return "\nFailed chmod installer\n";
}

unset($argv[0]);
$imp = implode(' ',$argv);

$installerCommand = "php install.php $imp";
logMessage(L_USER, 'switch to user');
if (!OsUtils::execute("su $kalturaUserName --command='$installerCommand'")) {
	logMessage(L_USER, 'Failed switch to user');		
	return "\nFailed switch to batch user\n";
}

//logMessage(L_USER, 'switch to user');
//if (!OsUtils::execute("su ".$kalturaUserName." --command='/opt/kaltura/app/scripts/serviceBatchMgr.sh start'")) {
//	echo "Failed switch to batch user";			
//	return "\nFailed switch to batch user\n";
//}


//if (!OsUtils::execute("/userTest/batchuser.sh $kalturaUserName")) {
//echo "\nFailed running script batchuser\n";			
//return "\nFailed running script batchuser\n";
//}

logMessage(L_USER, 'add crons');
$cron_content = file_get_contents('/opt/kaltura/crontab/kaltura_crontab');
logMessage(L_USER, 'apending crons: '.$cron_content);
OsUtils::appendFile('/etc/crontab', $cron_content);
$cron_content = file_get_contents('/opt/kaltura/dwh/crontab/dwh_crontab');
logMessage(L_USER, 'apending crons: '.$cron_content);
OsUtils::appendFile('/etc/crontab', $cron_content);

logMessage(L_USER, 'restart cron');
if (!OsUtils::execute("/etc/init.d/crond restart")) {
	echo "Failed restart cron";			
	return "\nFailed cron\n";
}

logMessage(L_USER, 'chmod');
if (!OsUtils::execute("chmod 700 /etc/cron.d")) {
	echo "Failed chmod";			
	return "\nFailed chmod\n";
}

//logMessage(L_USER, 'chmod');
//if (!OsUtils::execute("chmod 700 /etc/rc.d/init.d")) {
//	echo "Failed chmod";			
//	return "\nFailed chmod\n";
//}


$kalturaProcesses = implode(OsUtils::executeReturnOutput("ps -ef | grep kaltura"));
logMessage(L_USER, 'ps -ef | grep kaltura: '.$kalturaProcesses);
$processFound = strstr($kalturaProcesses, 'searchd --config');
if(!$processFound){
	echo 'ERROR: searchd is not running';
}
$processFound = strstr($kalturaProcesses, 'KGenericBatchMgr.class.php');
if(!$processFound){
	echo 'ERROR: batch is not running';
}
$processFound = strstr($kalturaProcesses, 'watch.daemon.sh');
if(!$processFound){
	echo 'ERROR: watch daemon is not running';
}
$processFound = strstr($kalturaProcesses, 'populateFromLog.php');
if(!$processFound){
	echo 'ERROR: populate is not running';
}
$processFound = strstr($kalturaProcesses, 'memcached');
if(!$processFound){
	echo 'ERROR: memcached is not running';
}

logMessage(L_USER, 'end preinstallation');
