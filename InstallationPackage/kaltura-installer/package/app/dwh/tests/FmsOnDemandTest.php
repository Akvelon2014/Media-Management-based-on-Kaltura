<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'FmsTestCase.php';

class FmsOnDemandTest extends FmsTestCase
{
	protected function getFetchParams()
	{
		global $CONF;
		return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->FMSOnDemandStreamingLogsDir,
					self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->FMSOnDemandStreamingFileWildcard,
					'FetchMethod' =>$CONF->FMSOnDemandStreamingFetchMethod,
					'ProcessID'=>$CONF->FMSOnDemandStreamingProcessID,
					'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
					'FetchFTPServer'=>$CONF->FMSOnDemandStreamingFtpHost,
					'FetchFTPPort'=>$CONF->FMSOnDemandStreamingFtpPort,
					'FetchFTPUser'=>$CONF->FMSOnDemandStreamingFtpUser,
					'FetchFTPPassword'=>$CONF->FMSOnDemandStreamingFtpPassword,
					'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/fms_streaming',
					self::GENERATE_PARAM_IS_ARCHIVED=>'True');
	}

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->FMSOnDemandStreamingProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/fms_streaming/process/process_fms_events.kjb',
			     'FMSYieldMapping'=>$CONF->EtlBasePath.'/fms_streaming/process/yield_fms_ondemand_streaming_data.ktr');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->FMSOnDemandStreamingProcessID);
        }


	public function getLinePartnerAndEntry($line, $partnerID, $entryID = null)
	{
		$partnerID = -1;
		$entryID = -1;

		$xSnameIndex = 26;
		$fmsFields = explode("\t", $line);
		if (count($fmsFields) > $xSnameIndex)
                {
			preg_match('/^\/?p\/([0-9]+)\/(.*)/', $fmsFields[$xSnameIndex], $matches);
			if (count($matches)>2)
			{
				$partnerID = $matches[1];
				preg_match('/.*\/(flavorId|entry_id)\/([01]_,?[^\.,\/]+)?(.*\/)?([01]_,?[^\.,\/]+).*/',$matches[2],$objectsMatches);
		        	if (count($objectsMatches)>4)
	        	        {
	                        	$objectType=$objectsMatches[1];
		                        $objectID = $objectsMatches[2] ? $objectsMatches[2] : $objectsMatches[4];
	       	                        $objectID = substr($objectID,2,1) == ',' ? substr($objectID,0,2) + substr($objectID,3) : $objectID;
				      	if($objectType == "flavorId")
	                        	{
                                		$entryIDResult = DWHInspector::getEntryIDByFlavorID($objectID);
						if ($entryIDResult != null)
						{
		                                	$entryID = $entryIDResult;
						} 
        		                }
                		        else
	                	        {
        	                	        $entryID = $objectID;
	                	        }
				return true;
				}
			}
		}
		return false;

	}
}
?>
