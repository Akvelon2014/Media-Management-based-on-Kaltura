<?php
/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Modified by Akvelon Inc.
* 2014-06-30
* http://www.akvelon.com/contact-us
*/

/**
 * @package Scheduler
 * @subpackage Mailer
 */

/**
 * Will import a single URL and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	parse URL	(youTube is a special case) 
 * 		fetch heraders (to calculate the size of the file)
 * 		fetch file (update the job's progress - 100% is when the whole file as appeared in the header)
 * 		move the file to the archive
 * 		set the entry's new status and file details  (check if FLV) 
 *
 * @package Scheduler
 * @subpackage Mailer
 */
class KAsyncMailer extends KJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::MAIL;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		return $job;
	}
	
	const MAILER_DEFAULT_SENDER_EMAIL = 'notifications@kaltura.com';
	const MAILER_DEFAULT_SENDER_NAME = 'Kaltura Notification Service';
	
	// replace email config mechanism !!
	protected $texts_array; // will hold the configuration of the in file
	
	/**
	 * @var PHPMailer
	 */
	protected $mail;
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::run()
	 */
	public function run($jobs = null)
	{
		KalturaLog::info("Mail batch is running");
		
		if($this->taskConfig->isInitOnly())
			return $this->init();
		
		$jobs = $this->kClient->batch->getExclusiveJobs( 
			$this->getExclusiveLockKey() , 
			$this->taskConfig->maximumExecutionTime , 
			$this->getMaxJobsEachRun() , 
			$this->getFilter(),
			$this->getJobType()
		);
			
		KalturaLog::info(count($jobs) . " mail jobs to perform");
								
		if(!count($jobs) > 0)
		{
			KalturaLog::info("Queue size: 0 sent to scheduler");
			$this->saveSchedulerQueue(self::getType());
			return;
		}
				
		$this->initConfig();
		$this->kClient->startMultiRequest();
		foreach($jobs as $job)
			$this->send($job, $job->data);
		$this->kClient->doMultiRequest();		
			
			
		$this->kClient->startMultiRequest();
		foreach($jobs as $job)
		{
			KalturaLog::info("Free job[$job->id]");
			$this->onFree($job);
	 		$this->kClient->batch->freeExclusiveJob($job->id, $this->getExclusiveLockKey(), $this->getJobType());
		}
		$responses = $this->kClient->doMultiRequest();
		$response = end($responses);
		
		KalturaLog::info("Queue size: $response->queueSize sent to scheduler");
		$this->saveSchedulerQueue(self::getType(), $response->queueSize);
	}
	
	/*
	 * Will take a single KalturaMailJob and send the mail using PHPMailer  
	 * 
	 * @param KalturaBatchJob $job
	 * @param KalturaMailJobData $data
	 */
	protected function send(KalturaBatchJob $job, KalturaMailJobData $data)
	{
		KalturaLog::debug("send($job->id)");
		
		try
		{
 			$result = $this->sendEmail( 
 				$data->recipientEmail,
 				$data->recipientName,
 				$data->mailType,
 				explode ( "|" , $data->subjectParams ) ,
 				explode ( "|" , $data->bodyParams ),
 				$data->fromEmail ,
 				$data->fromName,
 				$data->culture,
 				$data->isHtml);
			
	 		if ( $result )
	 		{
	 			$job->status = KalturaBatchJobStatus::FINISHED;
	 		}
	 		else
	 		{
	 			$job->status = KalturaBatchJobStatus::FAILED;
	 		}
	 			
			KalturaLog::info("job[$job->id] status: $job->status");
			$this->onUpdate($job);
			
			$updateJob = new KalturaBatchJob();
			$updateJob->status = $job->status;
	 		$this->kClient->batch->updateExclusiveJob($job->id, $this->getExclusiveLockKey(), $updateJob);			
		}
		catch ( Exception $ex )
		{
			KalturaLog::crit( $ex );
		}
	}
	

	protected function sendEmail( $recipientemail, $recipientname, $type, $subjectParams, $bodyParams, $fromemail , $fromname, $culture = 'en', $isHtml = false  )
	{
		KalturaLog::debug(__METHOD__ . "($recipientemail, $recipientname, $type, $subjectParams, $bodyParams, $culture, $fromemail , $fromname)");
		
		$this->mail = new PHPMailer();
		$this->mail->CharSet = 'utf-8';
		$this->mail->IsHTML($isHtml);
		$this->mail->AddAddress($recipientemail);
			
		if ( $fromemail != null && $fromemail != '' ) 
		{
			// the sender is what was definied before the template mechanism
			$this->mail->Sender = self::MAILER_DEFAULT_SENDER_EMAIL;
			
			$this->mail->From = $fromemail ;
			$this->mail->FromName = ( $fromname ? $fromname : $fromemail ) ;
		}
		else
		{
			$this->mail->Sender = self::MAILER_DEFAULT_SENDER_EMAIL;
			
			$this->mail->From = self::MAILER_DEFAULT_SENDER_EMAIL ;
			$this->mail->FromName = self::MAILER_DEFAULT_SENDER_NAME ;
		}
			
		$this->mail->Subject = $this->getSubjectByType( $type, $culture, $subjectParams  ) ;
		$this->mail->Body = $this->getBodyByType( $type, $culture, $bodyParams, $recipientemail, $isHtml ) ;
			
//		$this->mail->setContentType( "text/plain; charset=\"utf-8\"" ) ; //; charset=utf-8" );
		// definition of the required parameters
		
//		$this->mail->prepare();

		// send the email
		$body = $this->mail->Body;
		if ( strlen ( $body ) > 1000 ) 
		{
			$body_to_log = "total length [" . strlen ( $body ) . "]:\n" . " body: " . substr($body , 0 , 1000 ) ;
		}
		else
		{
			$body_to_log  = " body: " . $body;
		}
		KalturaLog::info( 'sending email to: '. $recipientemail . " subject: " . $this->mail->Subject .  $body_to_log );
			
		try
		{
			return ( $this->mail->Send() ) ;
		} 
		catch ( Exception $e )
		{
			KalturaLog::err( $e );
			return false;
		}
	}
	
	
	public function getSubjectByType( $type, $culture, $subjectParamsArray  )
	{
		KalturaLog::debug(__METHOD__ . "($type, $culture, $subjectParamsArray)");
		
		if ( $type > 0 )
		{
			$cultureTexts = isset($this->texts_array[$culture]) ? $this->texts_array[$culture] : reset($this->texts_array);
			$subject = $cultureTexts['subjects'][$type];
			$subject = vsprintf( $subject, $subjectParamsArray );
			//$this->mail->setSubject( $subject );
			return $subject;
		}
		else
		{
			// use template 
		}
	}

	public function getBodyByType( $type, $culture, $bodyParamsArray, $recipientemail, $isHtml = false  )
	{
		KalturaLog::debug(__METHOD__ . "($type, $culture, $bodyParamsArray, $recipientemail)");

		// if this does not need the common_header, under common_text should have $type_header =
		// same with footer
		$cultureTexts = isset($this->texts_array[$culture]) ? $this->texts_array[$culture] : reset($this->texts_array);
		$common_taxt_arr = $cultureTexts['common_text'];
		$footer = ( isset($common_taxt_arr[$type . '_footer']) ) ? $common_taxt_arr[$type . '_footer'] : $common_taxt_arr['footer'];
		$body = $cultureTexts['bodies'][$type];

		$forumsLink = kConf::get('forum_url');
		$unsubscribeLink = kConf::get('unsubscribe_mail_url') . self::createBlockEmailStr($recipientemail);

		$body .= "\n" . $footer;
		KalturaLog::debug("type [$type]");
		KalturaLog::debug("params [" . print_r($bodyParamsArray, true) . "]");
		KalturaLog::debug("body [$body]");
		KalturaLog::debug("footer [$footer]");
		$body = vsprintf( $body, $bodyParamsArray );
		if ($isHtml)
		{
			$body = str_replace( "<BR>", "<br />\n", $body );
			$body = '<p align="left" dir="ltr">'.$body.'</p>';
		}
		else
		{
			$body = str_replace( "<BR>", chr(13).chr(10), $body );
		}	
		$body = str_replace( "<EQ>", "=", $body );
		$body = str_replace( "<EM>", "!", $body ); // exclamation mark
		
		KalturaLog::debug("final body [$body]");
		return $body;
	}
		
	protected function initConfig ( )
	{
		KalturaLog::debug(__METHOD__ . "()");
		$cultures = array( 'en' );

		// now we read the ini files with the texts
		// NOTE: '=' signs CANNOT be used inside the ini files, instead use "<EQ>"
		$rootdir =  realpath(dirname(__FILE__).'');
			
		foreach ( $cultures as $culture)
		{
			$filename = $rootdir."/emails_".$culture.".ini";
			KalturaLog::debug( 'ini filename = '.$filename );
			if ( ! file_exists ( $filename )) 
			{
				KalturaLog::crit( 'Fatal:::: Cannot find file: '.$filename );
				die();
			}
			$ini_array = parse_ini_file( $filename, true );
			$this->texts_array[$culture] = array( 'subjects' => $ini_array['subjects'],
			'bodies'=>$ini_array['bodies'] ,
			'common_text'=> $ini_array['common_text'] );
		}		
	}
	
	
	// should be the same as on the server
	protected static $key = "myBlockedEmailUtils";
	const SEPARATOR = ";";
	const EXPIRY_INTERVAL = 2592000; // 30 days in seconds
	
	public static function createBlockEmailStr ( $email )
	{
		KalturaLog::debug(__METHOD__ . "($email)");
		return  $email . self::SEPARATOR . kString::expiryHash( $email , self::$key , self::EXPIRY_INTERVAL );
	}
}
