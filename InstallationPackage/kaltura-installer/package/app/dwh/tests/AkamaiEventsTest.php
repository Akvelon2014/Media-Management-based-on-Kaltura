<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'EventTestCase.php';

class AkamaiEventTest extends EventTestCase
{
        protected function getFetchParams()
        {
                global $CONF;

                return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->AkamaiEventsLogsDir,
                                        self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->AkamaiEventsWildcard,
                                        'FetchMethod' =>$CONF->AkamaiEventsFetchMethod,
                                        'ProcessID'=>$CONF->AkamaiEventsProcessID,
                                        'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
                                        'FetchFTPServer'=>$CONF->AkamaiEventsFTPServer,
                                        'FetchFTPPort'=>$CONF->AkamaiEventsFTPPort,
                                        'FetchFTPUser'=>$CONF->AkamaiEventsFTPUser,
                                        'FetchFTPPassword'=>$CONF->AkamaiEventsFTPPassword,
                                        'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/events',
                                        self::GENERATE_PARAM_IS_ARCHIVED=>'True');
        }

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->AkamaiEventsProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/events/process/process_events.kjb',
			     'TextFileInputMappingName'=>'read_akamai_events_input_file.ktr');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->AkamaiEventsProcessID);
        }

        protected function getDSTablesToFactTables()
        {
                $dsTableToFactTables = array();
                $dsTablesToFactTables["ds_events"]="dwh_fact_events";
                return $dsTableToFactTables;
        }


}
?>
