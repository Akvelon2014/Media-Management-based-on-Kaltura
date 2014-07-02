<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'CDNBandwidthHttpTestCase.php';

class Level3Test extends CDNBandwidthHttpTestCase
{
	protected function getFetchParams()
	{
		global $CONF;
		
		return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->BandwidthUsageLevel3LogsDir,
					self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->BandwidthUsageLevel3WildCard,
					'FetchMethod' =>$CONF->BandwidthUsageLevel3FetchMethod,
					'ProcessID'=>$CONF->BandwidthUsageLevel3ProcessID,
					'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
					'FetchFTPServer'=>$CONF->BandwidthUsageLevel3FTPServer,
					'FetchFTPPort'=>$CONF->BandwidthUsageLevel3FTPPort,
					'FetchFTPUser'=>$CONF->BandwidthUsageLevel3FTPUser,
					'FetchFTPPassword'=>$CONF->BandwidthUsageLevel3FTPPassword,
					'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/bandwidth_usage',
					self::GENERATE_PARAM_IS_ARCHIVED=>'True');
	}

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->BandwidthUsageLevel3ProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/bandwidth_usage/process/bandwidth_usage_process_cycle.kjb',
                             'BandwidthSourceName'=>'level3');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->BandwidthUsageLevel3ProcessID);
        }

	protected function getBWRegex()
	{
		return '/^[^ ]+ [^ ]+ [^ ]+ .*\/p\/(\d+)\/[^ ]* [^ ]+ [^ ]+ [^ ]+ [^ ]+ (\d+) [^ ]+ [^ ]+ "[^"]+" ".+" ".+"/';
	}
	
	protected function getBandwidthSourceID()
	{	
		return 3;
	}
	
	protected function ignoredInvalidBWLine($line)
        {
                return false;
        }

	public function testGenerate()
	{
		parent::testGenerate();
	}

	public function testProcess()
	{
		parent::testProcess();
	}
	
	public function testTransfer()
	{
		parent::testTransfer();
	}

	public function testAggregation()
	{
		parent::testAggregation(3);
	}	

}
?>
