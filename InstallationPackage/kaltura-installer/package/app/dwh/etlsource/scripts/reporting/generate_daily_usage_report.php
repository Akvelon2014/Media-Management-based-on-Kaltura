<?php
require_once dirname(__FILE__).'/../pentaho_decrypt.php';
require_once dirname(__FILE__).'/phpmailer/class.phpmailer.php';


// Check argument variables
if (count($argv) < 6)
{
      echo count($argv) . " parameters found.\n";
      echo "Usage: php generate_daily_usage_report.php <dbhost> <dbport> <dbuser> <dbpass> <date_id> \n";
      exit(2);
}
$dbhost = $argv[1];
$dbport = $argv[2];
$dbuser = $argv[3];
$dbpassenc = $argv[4];
$date_id = $argv[5];
$dbpass = decrypt($dbpassenc);

$logPath = "/home/etl/logs";
$logFileName = $logPath . "/daily_usage_report_log-" . $date_id . ".log";
exec('echo ' . date("Y-M-d H:i:s") . ' Starting >> ' . $logFileName); 

$tmpFileName = "/tmp/daily_usage_report.xml";

exec('echo ' . date("Y-M-d H:i:s") . ' Executing daily usage stored_procedure >> ' . $logFileName); 
$sqlOSCommand = "/usr/bin/mysql --xml -u". $dbuser ." -p". $dbpass ." -h". $dbhost. " -P". $dbport ." -e \"CALL kalturadw.generate_daily_usage_report(". $date_id .")\"";
exec('echo ' . date("Y-M-d H:i:s") . ' ' . $sqlOSCommand . ' >> ' . $logFileName); 
exec($sqlOSCommand . ' 1> ' . $tmpFileName . ' 2>>' . $logFileName);
exec('echo >> ' . $logFileName);                       
exec('echo ' . date("Y-M-d H:i:s") . ' Finished daily usage stored_procedure execution >> ' . $logFileName); 

exec('echo ' . date("Y-M-d H:i:s") . ' Parsing XML to HTML >> ' . $logFileName); 
$tmpOutputFileName = "/tmp/daily_usage_report-".$date_id.".html";
$groupColumn = "Measure";
$xml2TableOSCommand = "/usr/bin/php ".dirname(__FILE__)."/xml2table.php ".$tmpFileName." ".$tmpOutputFileName." ". $groupColumn; 
exec('echo ' . date("Y-M-d H:i:s") . ' ' . $xml2TableOSCommand . ' >> ' . $logFileName); 
exec($xml2TableOSCommand . ' 2>&1 >> ' . $logFileName);
exec('echo >> ' . $logFileName);                       

exec('echo ' . date("Y-M-d H:i:s") . ' Finished parsing >> ' . $logFileName); 

$mailSubject = "\"Daily usage report\"";
$sender = "dwh@kaltura.com";
#$recipients = "dor.porat@kaltura.com,alex.bandel@kaltura.com,yuval.shemesh@kaltura.com,eran.etam@kaltura.com,tomer.wolff@kaltura.com,Anatol.Schwartz@kaltura.com";
$recipients = "dor.porat@kaltura.com";
$attachment = $tmpOutputFileName;

exec('echo ' . date("Y-M-d H:i:s") . ' Sending mail to ' . $recipients . ' >> ' . $logFileName); 
$sendMailOSCommand = "/usr/bin/php ".dirname(__FILE__)."/sendMail.php ". $sender . " " . $recipients . " " . $mailSubject . " " . $attachment;
exec($sendMailOSCommand . ' 2>&1 >> ' . $logFileName);
exec('echo >> ' . $logFileName);                       

exec('echo ' . date("Y-M-d H:i:s") . '  Mail sent >> ' . $logFileName);                       
exec('echo ' . date("Y-M-d H:i:s") . '  Finished  successfully >> ' . $logFileName);                       

?>
