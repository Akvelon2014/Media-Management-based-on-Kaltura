<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';

class TranscodingErrorTest extends KalturaTestCase
{
public function testTranscodingErrors()
    {
		$this->compare('/transcoding_errors/load_transcoding_errors.ktr');
    }
	

	private function compare($job)
	{
		global $CONF;
		
		$before = new DateTime(date("Y-m-d"));
		$start = new DateTime(date("Y-m-d"));
                $start->sub(new DateInterval("P30D"));

		KettleRunner::execute('/../tests/execute_dim.ktr', array('TransformationName'=>$CONF->EtlBasePath.$job,'LastUpdatedAt'=>$start->format('Y/m/d')." 00:00:00"));
		
		$sourceDB = new MySQLRunner($CONF->MonitoringDbHostName,$CONF->MonitoringDbPort, $CONF->MonitoringDbUser, $CONF->MonitoringDbPassword);		
		$sourceRows = $sourceDB ->run("SELECT count(*) amount FROM monmon.monitor_entry where updated_at>='".$start->format('Y-m-d')."' and updated_at<='".$before->format('Y-m-d')."'");
		
		$targetDB = new MySQLRunner($CONF->DbHostName,$CONF->DbPort, $CONF->DbUser, $CONF->DbPassword);
		$targetRows = $targetDB->run("SELECT count(*) amount FROM kalturadw.dwh_fact_errors e, kalturadw.dwh_dim_error_object_types t where error_time>='".$start->format('Y-m-d')."' and error_time<='".$before->format('Y-m-d')."' and e.error_object_type_id = t.error_object_type_id and t.error_object_type_name = 'Transcoding'");		

		#$this->assertGreaterThan(0, $targetRows[0]['amount']);
		$this->assertEquals($sourceRows[0]['amount'], $targetRows[0]['amount']);
	}
}
?>
