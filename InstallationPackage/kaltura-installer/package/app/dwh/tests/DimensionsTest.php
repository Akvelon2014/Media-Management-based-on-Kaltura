<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';

class DimensionsTest extends KalturaTestCase
{
    public function testUpdatePartners()
    {
		$this->compare('/dimensions/update_partners.ktr','partner','dwh_dim_partners');
    }
	
	public function testUpdateEntries()
    {
		$this->compare('/dimensions/update_entries.ktr','entry','dwh_dim_entries');
    }

	public function testUpdateFlavorAsset()
    {
		$this->compare('/dimensions/update_flavor_asset.ktr','flavor_asset','dwh_dim_flavor_asset');
    }

	public function testUpdateFileSync()
    {
		$this->compare('/dimensions/update_file_sync.ktr','file_sync','dwh_dim_file_sync');
    }
	
	public function testUpdateMediaInfo()
    {
		$this->compare('/dimensions/update_media_info.ktr','media_info','dwh_dim_media_info');
    }

	public function testUpdateFlavorParams()
    {
		$this->compare('/dimensions/update_flavor_params.ktr','flavor_params','dwh_dim_flavor_params');
    }

	public function testUpdateFlavorParamsOutput()
    {
		$this->compare('/dimensions/update_flavor_params_output.ktr','flavor_params_output','dwh_dim_flavor_params_output');
    }

	public function testUpdateKusers()
    {
		$this->compare('/dimensions/update_kusers.ktr','kuser','dwh_dim_kusers');
    }
	
	public function testUpdateUIConf()
    {
		$this->compare('/dimensions/update_ui_conf.ktr','ui_conf','dwh_dim_ui_conf');
    }
	
	public function testUpdateWidget()
    {
		$this->compare('/dimensions/update_widget.ktr','widget','dwh_dim_widget');
    }
	
	public function testUpdateFlavorParamsConversionProfile()
    {
		$this->compare('/dimensions/update_flavor_params_conversion_profile.ktr','flavor_params_conversion_profile','dwh_dim_flavor_params_conversion_profile');
    }

	public function testUpdateBatchJob()
    {
		$this->compare('/dimensions/update_batch_job.ktr','batch_job','dwh_dim_batch_job');
    }
	
	private function compare($job, $source, $target)
	{
		global $CONF;
		
		$before = new DateTime(date("Y-m-d"));
		$start = new DateTime(date("Y-m-d"));
                $start->sub(new DateInterval("P30D"));
		$end = new DateTime(date("Y-m-d"));
                $end->add(new DateInterval("P1D"));
		KettleRunner::execute('/../tests/execute_dim.ktr', array('TransformationName'=>$CONF->EtlBasePath.$job,'LastUpdatedAt'=>$start->format('Y/m/d')." 00:00:00", 'OperationalReplicationSyncedAt'=>$end->format('Y/m/d')." 00:00:00"));
		
		$sourceDB = new MySQLRunner($CONF->OpDbHostName,$CONF->OpDbPort, $CONF->OpDbUser, $CONF->OpDbPassword);		
		$sourceRows = $sourceDB ->run("SELECT count(*) amount FROM kaltura.".$source." where updated_at>='".$start->format('Y-m-d')."' and created_at<='".$before->format('Y-m-d')."'");
		
		$targetDB = new MySQLRunner($CONF->DbHostName,$CONF->DbPort, $CONF->DbUser, $CONF->DbPassword);
		$targetRows = $targetDB->run("SELECT count(*) amount FROM kalturadw.".$target." where updated_at>='".$start->format('Y-m-d')."' and created_at<='".$before->format('Y-m-d')."'");		
		
		#$this->assertGreaterThan(0, $targetRows[0]['amount']);
		$this->assertEquals($sourceRows[0]['amount'], $targetRows[0]['amount']);
	}
	
}
?>
