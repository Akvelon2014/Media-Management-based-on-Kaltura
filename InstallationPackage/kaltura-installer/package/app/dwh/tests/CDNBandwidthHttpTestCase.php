<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'ComparedTable.php';

abstract class CDNBandwidthHttpTestCase extends CycleProcessTestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}
	
	protected function getDSTablesToFactTables()
        {
                $dsTableToFactTables = array();
                $dsTablesToFactTables["ds_bandwidth_usage"]="dwh_fact_bandwidth_usage";
                return $dsTableToFactTables;
        }

	public function testGenerate()
	{
		parent::testGenerate();
	}

	public function testProcess()
	{
		parent::testProcess();

		global $CONF;

                $cycleID = DWHInspector::getCycle('LOADED');
		
		$files = DWHInspector::getFiles($cycleID);
		foreach($files as $fileID)
		{
			$filename =  $CONF->ProcessPath."/".$cycleID.'/'.DWHInspector::getFileName($fileID);
		
			// compare rows in ds_bandwidth_usage to rows in file
                        $this->assertEquals(DWHInspector::countRows('kalturadw_ds.ds_bandwidth_usage',$fileID),$this->countRows($filename, array($this, 'validBWLine')));
                        $this->assertEquals(DWHInspector::countRows('kalturadw_ds.ds_bandwidth_usage',$fileID, 'and bandwidth_source_id = '. $this->getBandwidthSourceID()),$this->countRows($filename, array($this, 'validBWLine')));

                        // compare bandwidth_bytes in ds_bandwidth_usage to bandwidth bytes consumed in file
                        $this->assertEquals(DWHInspector::sumRows('kalturadw_ds.ds_bandwidth_usage',$fileID,"bandwidth_bytes"),$this->sumBytes($filename, array($this, 'validBWLine'), $this->getBWRegex()));

			// compare bw consumption per partner
                        $bwPartners = $this->countBWEventsPerPartner($filename); 
                        $this->assertEquals(count($bwPartners), DWHInspector::countDistinct('kalturadw_ds.ds_bandwidth_usage',$fileID,'partner_id'));

                        foreach($bwPartners as $partner=>$val)
                        {
                                $res = DWHInspector::sumRows('kalturadw_ds.ds_bandwidth_usage',$fileID,'bandwidth_bytes', ' and partner_id=\''.$partner.'\'');
                                $this->assertEquals($res, $val);
                        }	

			// make sure there are very little invalid lines
			$this->assertEquals($this->countInvalidLines($filename, array($this, 'validBWLine'), array($this, 'ignoredInvalidBWLine')), DWHInspector::countRows('kalturadw_ds.invalid_ds_lines',$fileID));
		}
	}
	
	protected abstract function ignoredInvalidBWLine($line);
	protected abstract function getBWRegex();
	protected abstract function getBandwidthSourceID();
	
	public function validBWLine($line)
        {
                return (preg_match($this->getBWRegex(), $line) > 0);
        }

	private function countBWEventsPerPartner($file)
        {
                return $this->countPerRegex($file, $this->getBWRegex(),array($this, 'validBWLine'));
        }

	public function testTransfer()
	{
		parent::testTransfer();
	}

	public function testAggregation($sourceId)
	{
		parent::testAggregation();
		$this->compareAggregation(array(new ComparedTable('partner_id', 'kalturadw.dwh_fact_bandwidth_usage', '(bandwidth_bytes/1024)')), 
					  array(new ComparedTable('partner_id', 'kalturadw.dwh_hourly_partner_usage', 'if(bandwidth_source_id='.$sourceId.', ifnull(count_bandwidth_kb, 0),0)')), 1);

		$this->compareAggregation(array(new ComparedTable('bandwidth_source_id', 'kalturadw.dwh_fact_bandwidth_usage', '(bandwidth_bytes/1024)')),
                                          array(new ComparedTable('bandwidth_source_id', 'kalturadw.dwh_hourly_partner_usage', 'if(bandwidth_source_id='.$sourceId.', ifnull(count_bandwidth_kb, 0),0)')), 1);

		$this->compareAggregation(array(new ComparedTable('location_id', 'kalturadw.dwh_fact_bandwidth_usage', '(bandwidth_bytes/1024)'),
                                                new ComparedTable('location_id', 'kalturadw.dwh_fact_fms_sessions', '(total_bytes/1024)')),
                                          array(new ComparedTable('location_id', 'kalturadw.dwh_hourly_events_devices', 'ifnull(count_bandwidth_kb, 0)')), 1);
                $this->compareAggregation(array(new ComparedTable('country_id', 'kalturadw.dwh_fact_bandwidth_usage', '(bandwidth_bytes/1024)'),
                                                new ComparedTable('country_id', 'kalturadw.dwh_fact_fms_sessions', '(total_bytes/1024)')),
                                          array(new ComparedTable('country_id', 'kalturadw.dwh_hourly_events_devices', 'ifnull(count_bandwidth_kb, 0)')), 1);
	}	
}

?>
