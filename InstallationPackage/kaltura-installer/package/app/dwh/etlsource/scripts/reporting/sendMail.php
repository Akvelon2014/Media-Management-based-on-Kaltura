<?php

require_once dirname(__FILE__).'/phpmailer/class.phpmailer.php';

// Check argument variables
if (count($argv) < 4)
{
      echo count($argv) . " parameters found.\n";
      echo "Usage: php sendMail.php <from> <csvListOfRecpients> <subject> <csvListOfAttachment>\n";
      exit(2);
}

$from = $argv[1];
$recipients = explode(',', $argv[2]);
$subject = $argv[3];


$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Subject = $subject;
$mail->From=$from;
$mail->FromName=$from;
foreach ($recipients as $recipient){
	if ($recipient != ''){
		$mail->AddAddress($recipient);
	}
}

if (count($argv) > 4)
{
	$attachmentFilePaths = explode(',', $argv[4]);
	foreach ($attachmentFilePaths as $attachmentFilePath){
       		if (file_exists($attachmentFilePath)){
			if (strrpos($attachmentFilePath, "html") === strlen($attachmentFilePath)-strlen("html")){
				$mail->Body .= file_get_contents($attachmentFilePath);
			} else {
				$mail->AddAttachment($attachmentFilePath); 
			}
        	}
	}
}

$mail->IsHTML(true);
if(!$mail->Send())
{
   echo "Error sending: " . $mail->ErrorInfo;;
}
else
{
   echo "Letter is sent";
}
?>
