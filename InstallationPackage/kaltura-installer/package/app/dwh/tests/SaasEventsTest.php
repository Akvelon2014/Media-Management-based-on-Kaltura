<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'EventTestCase.php';

class SaasEventTest extends EventTestCase
{
        protected function getFetchParams()
        {
                global $CONF;

                return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->EventsLogsDir,
                                        self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->EventsWildcard,
                                        'FetchMethod' =>$CONF->EventsFetchMethod,
                                        'ProcessID'=>$CONF->EventsProcessID,
                                        'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
                                        'FetchFTPServer'=>$CONF->EventsFTPServer,
                                        'FetchFTPPort'=>$CONF->EventsFTPPort,
                                        'FetchFTPUser'=>$CONF->EventsFTPUser,
                                        'FetchFTPPassword'=>$CONF->EventsFTPPassword,
                                        'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/events',
                                        self::GENERATE_PARAM_IS_ARCHIVED=>'True');
        }

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->EventsProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/events/process/process_events.kjb',
			     'TextFileInputMappingName'=>'read_events_input_file.ktr');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->EventsProcessID);
        }

        protected function getDSTablesToFactTables()
        {
                $dsTableToFactTables = array();
                $dsTablesToFactTables["ds_events"]="dwh_fact_events";
                $dsTablesToFactTables["ds_bandwidth_usage"]="dwh_fact_bandwidth_usage";
                return $dsTableToFactTables;
        }

}
?>
