<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'CDNBandwidthHttpTestCase.php';

class LLNTest extends CDNBandwidthHttpTestCase
{
	protected function getFetchParams()
	{
		global $CONF;
		
		return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->BandwidthUsageLLNLogsDir,
					self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->BandwidthUsageLLNWildCard,
					'FetchMethod' =>$CONF->BandwidthUsageLLNFetchMethod,
					'ProcessID'=>$CONF->BandwidthUsageLLNProcessID,
					'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
					'FetchFTPServer'=>$CONF->BandwidthUsageLLNFTPServer,
					'FetchFTPPort'=>$CONF->BandwidthUsageLLNFTPPort,
					'FetchFTPUser'=>$CONF->BandwidthUsageLLNFTPUser,
					'FetchFTPPassword'=>$CONF->BandwidthUsageLLNFTPPassword,
					'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/bandwidth_usage',
					self::GENERATE_PARAM_IS_ARCHIVED=>'True');
	}

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->BandwidthUsageLLNProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/bandwidth_usage/process/bandwidth_usage_process_cycle.kjb',
                             'BandwidthSourceName'=>'LLN');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->BandwidthUsageLLNProcessID);
        }

	protected function getBWRegex()
	{
		return '/^[^ ]+ [^ ]+ [^ ]+ \[[^]]+\] "[^ ]+ http:\/\/[^\.]+\.kaltura\.com\/p\/([0-9]+)\/.* [^\/]+\/[^"]+" [^ ]+ (\d+) "[^"]+" "(.+)"/';
	}

        protected function getBandwidthSourceID()
        {
                return 2;
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
		parent::testAggregation(2);
	}	

}
?>
