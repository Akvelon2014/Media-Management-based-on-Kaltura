<?php
/**
 * 
 * @package Scheduler
 * @subpackage Conversion
 *
 */
class KOperationEngineInletArmada  extends KSingleOutputOperationEngine
{
	protected $taskConfig = null;
/*
	protected $url=null;
	protected $login=null;
	protected $passw=null;
	protected $prio=5;
*/
	public function __construct($cmd, $outFilePath)
	{
		parent::__construct($cmd,$outFilePath);
//		$this->prio=5;
		KalturaLog::info(": cmd($cmd), outFilePath($outFilePath)");
	}

	/*************************************
	 * 
	 */
	protected function getCmdLine()
	{
		$exeCmd =  parent::getCmdLine();
		KalturaLog::info(print_r($this,true));
		return $exeCmd;
	}

	/*************************************
	 * 
	 */
	public function operate(kOperator $operator = null, $inFilePath, $configFilePath = null)
	{
		KalturaLog::debug("operator==>".print_r($operator,1));

$encodingTemplateId;
$encodingTemplateName;
$cloneAndUpadate=false;
$srcPrefixWindows;
$srcPrefixLinux;
$trgPrefixWindows;

			// ---------------------------------
			// Evaluate and set various Inlet Armada session params
		if($this->taskConfig->params->InletStorageRootWindows) $srcPrefixWindows = $this->taskConfig->params->InletStorageRootWindows;
		if($this->taskConfig->params->InletStorageRootLinux)   $srcPrefixLinux = $this->taskConfig->params->InletStorageRootLinux;
		if($this->taskConfig->params->InletTmpStorageWindows)  $trgPrefixWindows = $this->taskConfig->params->InletTmpStorageWindows;

		$url = $this->taskConfig->params->InletArmadaUrl;
		$login = $this->taskConfig->params->InletArmadaLogin;
		$passw = $this->taskConfig->params->InletArmadaPassword;
		if($this->taskConfig->params->InletArmadaPriority)
			$priority = $this->taskConfig->params->InletArmadaPriority;
		else
			$priority = 5;
			// ----------------------------------
			
		$inlet = new InletAPIWrap($url);
		KalturaLog::debug(print_r($inlet,1));
		$rvObj=new XmlRpcData;
		
		$rv=$inlet->userLogon($login, $passw, $rvObj);
		if(!$rv) {
			throw new KOperationEngineException("Inlet failure: login, rv(".(print_r($rvObj,true)).")");
		}
		KalturaLog::debug("userLogon - ".print_r($rvObj,1));
		
		$paramsMap = KDLUtils::parseParamStr2Map($operator->extra);
		foreach($paramsMap as $key=>$param){
			switch($key){
				case 'encodingTemplate':
				case 'encodingTemplateId':
					$encodingTemplateId=$param;
					break;
				case 'encodingTemplateName':
					$encodingTemplateId = $this->lookForJobTemplateId($inlet, $param);
					$encodingTemplateName=$param;
					break;
				case 'priority':
					$priority=$param;
					break;
				case 'cloneAndUpadate':
					$cloneAndUpadate=$param;
					break;
				default:
					break;
			}
		}
		
			// Adjust linux file path to Inlet Armada Windows path
		if(isset($srcPrefixWindows) && isset($srcPrefixLinux)) {
			$srcPrefixLinux = $this->addLastSlashInFolderPath($srcPrefixLinux, "/");
			$srcPrefixWindows = $this->addLastSlashInFolderPath($srcPrefixWindows, "\\");
			$srcFileWindows  = str_replace($srcPrefixLinux, $srcPrefixWindows, $inFilePath);
		}
		else
			$srcFileWindows  = $inFilePath;
			
		if(isset($trgPrefixWindows)){
			$trgPrefixLinux = $this->addLastSlashInFolderPath($this->taskConfig->params->localTempPath, "/");
			$trgPrefixWindows = $this->addLastSlashInFolderPath($trgPrefixWindows, "\\");
			$outFileWindows = str_replace($trgPrefixLinux, $trgPrefixWindows, $this->outFilePath);
		}
		else
			$outFileWindows = $this->outFilePath;
			
		$rv=$inlet->jobAdd(			
				$encodingTemplateId,		// job template id
				$srcFileWindows,		// String job_source_file, 
				$outFileWindows,		// String job_destination_file, 
				$priority,				// Int priority, 
				$srcFileWindows,			// String description, 
				array(),"",
				$rvObj);						
		if(!$rv) {
			throw new KOperationEngineException("Inlet failure: add job, rv(".print_r($rvObj,1).")");
		}
		KalturaLog::debug("jobAdd - encodingTemplate($encodingTemplateId), inFile($srcFileWindows), outFile($outFileWindows),rv-".print_r($rvObj,1));
		
		$jobId=$rvObj->job_id;
		$attemptCnt=0;
		while ($jobId) {
			sleep(60);
			$rv=$inlet->jobList(array($jobId),$rvObj);
			if(!$rv) {
				throw new KOperationEngineException("Inlet failure: job list, rv(".print_r($rvObj,1).")");
			}
			switch($rvObj->job_list[0]->job_state){
			case InletArmadaJobStatus::CompletedSuccess:
				$jobId=null;
				break;
			case InletArmadaJobStatus::CompletedUnknown:
			case InletArmadaJobStatus::CompletedFailure:
				throw new KOperationEngineException("Inlet failure: job, rv(".print_r($rvObj,1).")");
				break;
			}
			if($attemptCnt%10==0) {
				KalturaLog::debug("waiting for job completion - ".print_r($rvObj,1));
			}
			$attemptCnt++;
		}
//KalturaLog::debug("XXX taskConfig=>".print_r($this->taskConfig,1));
		KalturaLog::debug("Job completed successfully - ".print_r($rvObj,1));

		if($trgPrefixWindows) {
			$trgPrefixLinux = $this->addLastSlashInFolderPath($this->taskConfig->params->sharedTempPath, "/");
			$outFileLinux = str_replace($trgPrefixWindows, $trgPrefixLinux, $rvObj->job_list[0]->job_output_file);
//KalturaLog::debug("XXX str_replace($trgPrefixWindows, ".$trgPrefixLinux.", ".$rvObj->job_list[0]->job_output_file.")==>$outFileLinux");
		}
		else
			$outFileLinux = $rvObj->job_list[0]->job_output_file;
			
		if($outFileLinux!=$this->outFilePath) {
			KalturaLog::debug("copy($outFileLinux, ".$this->outFilePath.")");
			kFile::moveFile($outFileLinux, $this->outFilePath, true);
			//copy($outFileLinux, $this->outFilePath);
		}
	}

	/*************************************
	 * 
	 */
	public function configure(KSchedularTaskConfig $taskConfig, KalturaConvartableJobData $data, KalturaClient $client)
	{
		parent::configure($taskConfig, $data, $client);
		
		$this->taskConfig = $taskConfig;
		
		$errStr=null;
		if(!$taskConfig->params->InletArmadaUrl)
			$errStr="InletArmadaUrl";
		if(!$taskConfig->params->InletArmadaLogin){
			if($errStr) 
				$errStr.=",InletArmadaLogin";
			else
				$errStr="InletArmadaLogin";
		}
		if(!$taskConfig->params->InletArmadaPassword){
			if($errStr) 
				$errStr.=",InletArmadaPassword";
			else
				$errStr="InletArmadaPassword";
		}
		
		if($errStr)
			throw new KOperationEngineException("Inlet failure: missing credentials - $errStr");//, url(".$taskConfig->params->InletArmadaUrl."), login(."$taskConfig->params->InletArmadaLogin."),passw(".$taskConfig->params->InletArmadaPassword.")");
/*		
		$this->url =	$taskConfig->params->InletArmadaUrl;
		$this->login =	$taskConfig->params->InletArmadaLogin;
		$this->passw =	$taskConfig->params->InletArmadaPassword;
		if($taskConfig->params->InletArmadaPriority)
			$this->prio =	$taskConfig->params->InletArmadaPriority;
		else
			$this->prio = 5;
*/
		KalturaLog::info("taskConfig-->".print_r($taskConfig,true)."\ndata->".print_r($data,true));
	}

	/*************************************
	 * 
	 */
	private function addLastSlashInFolderPath($pathStr, $slashCh)
	{
		if($pathStr[strlen($pathStr)-1]!=$slashCh)
			return $pathStr.$slashCh;
		else
			return $pathStr;
	}
	
	/*************************************
	 * 
	 */
	private function lookForJobTemplateId($inlet, $name)
	{
	$rvObj=new XmlRpcData;
		$rv=$inlet->templateGroupList($rvObj);
		if(!$rv) {
			throw new KOperationEngineException("Inlet failure: templateGroupList, rv(".print_r($rvObj,1).")");
		}
		$templateDescObj=$this->templateGroupListToJobTemplate($rvObj->template_group_list, $name);
		return $templateDescObj->template_id;
	}
	
	/*************************************
	 * 
	 */
	private function templateGroupListToJobTemplate($groupList, $val, $fieldName="template_description")
	{
		foreach ($groupList as $grp) {
			foreach ($grp->templates as $tpl) {
				if($tpl->$fieldName==$val) {
					return $tpl;
				}
			}
		}
		return null;
	}
}
