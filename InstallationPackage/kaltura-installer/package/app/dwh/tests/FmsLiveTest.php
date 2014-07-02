<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'FmsTestCase.php';

class FmsLiveTest extends FmsTestCase
{

	public static function SetUpBeforeClass()
        {
                parent::SetUpBeforeClass();
		self::loadLiveEntries();
        }

	public static function tearDownAfterClass()
	{
		self::deleteLiveEntries();	
	}

	private static function loadLiveEntries()
	{
		$lines = file('source/insert_live_entries.sql');
		foreach ($lines as $line)
		{
			MySQLRunner::execute($line);
		}
	}

	private static function deleteLiveEntries()
	{
		$lines = file('source/delete_live_entries.sql');
                foreach ($lines as $line)
                {
                        MySQLRunner::execute($line);
                }
	}

	protected function getFetchParams()
	{
		global $CONF;
		return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->FMSLiveStreamingLogsDir,
					self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->FMSLiveStreamingFileWildcard,
					'FetchMethod' =>$CONF->FMSLiveStreamingFetchMethod,
					'ProcessID'=>$CONF->FMSLiveStreamingProcessID,
					'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
					'FetchFTPServer'=>$CONF->FMSLiveStreamingFtpHost,
					'FetchFTPPort'=>$CONF->FMSLiveStreamingFtpPort,
					'FetchFTPUser'=>$CONF->FMSLiveStreamingFtpUser,
					'FetchFTPPassword'=>$CONF->FMSLiveStreamingFtpPassword,
					'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/fms_streaming',
					self::GENERATE_PARAM_IS_ARCHIVED=>'True');
	}

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->FMSLiveStreamingProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/fms_streaming/process/process_fms_events.kjb',
			     'FMSYieldMapping'=>$CONF->EtlBasePath.'/fms_streaming/process/yield_fms_live_streaming_data.ktr');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->FMSLiveStreamingProcessID);
        }

	public function getLinePartnerAndEntry($line, $partnerID, $entryID = null)
	{
		$partnerID = 0;
		$entryID = "";
		$xSnameIndex = 26;
		$fmsFields = explode("\t", $line);
		if (count($fmsFields) > $xSnameIndex)
                {
			preg_match('/^(.*)_\d+@\d+$/', $fmsFields[$xSnameIndex], $matches);
			if (count($matches)>1)
			{
				$entryID = $matches[1];
				$partnerID = DWHInspector::getPartnerIDByEntryID($entryID);	
				return true;
			}
			
		}
		return false;

	}
}
?>
