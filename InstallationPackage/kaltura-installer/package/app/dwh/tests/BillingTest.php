<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';

class BillingTest extends KalturaTestCase
{
	const BW_MONTH = 20110101;
	const ST_MONTH = 20110201;
	const BW_ST_MONTH = 20110301;
	const PLAY_MONTH = 20110401;
	const ENTRY_MONTH = 20110501;

	private $partnerId;

	public function setUp()
	{
	    parent::refreshBISourcesTables();
		$this->partnerId = DWHInspector::createNewPartner();
		$this->createBilling();
		$this->simulateUsage();
	}

	private function createBilling()
	{
		MySQLRunner::execute("INSERT INTO kalturadw.dwh_dim_partners_billing (partner_id, 
					updated_at,					
					max_monthly_bandwidth_kb,
					charge_monthly_bandwidth_kb_usd,
					charge_monthly_bandwidth_kb_unit,
					max_monthly_storage_mb ,
					charge_monthly_storage_mb_usd,
					charge_monthly_storage_mb_unit,
					max_monthly_total_usage_mb,
					charge_monthly_total_usage_mb_usd,
					charge_monthly_total_usage_mb_unit,
					max_monthly_entries,
					charge_monthly_entries_usd,
					charge_monthly_entries_unit,
					max_monthly_plays ,
					charge_monthly_plays_usd,
					charge_monthly_plays_unit)
					VALUES(?, date(20100101), 300, 5, 50, 300, 5, 50, 500, 5, 20, 2, 5, 2, 100, 5, 100)",array(0=>$this->partnerId));
	
	}

	public function simulateUsage()
	{
		# Month with overage  bandwidth
		MySQLRunner::execute("INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, 
			date_id, hour_id, bandwidth_source_id, count_bandwidth_kb , aggr_storage_mb)
			VALUES(?, ?,0,8,250,0)",array(0=>$this->partnerId, 1=>self::BW_MONTH));

		MySQLRunner::execute("INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, 
			date_id, hour_id, bandwidth_source_id, count_bandwidth_kb , aggr_storage_mb)
			VALUES(?, ?,0,9,250,0)",array(0=>$this->partnerId, 1=>self::BW_MONTH));

		# Month with overage storage
        for($i=0;$i<28;$i++)
        {
            MySQLRunner::execute("INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, 
                date_id, hour_id, bandwidth_source_id, count_bandwidth_kb , aggr_storage_mb)
                VALUES(?, ?,0,8,0,500)",array(0=>$this->partnerId, 1=>self::ST_MONTH+$i));
        }
        
        # Month with overage storage + bandwidth
		for($i=0;$i<31;$i++)
        {
            MySQLRunner::execute("INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, 
                date_id, hour_id, bandwidth_source_id, count_bandwidth_kb , aggr_storage_mb)
                VALUES(?, ?,0,8,290*1024/31,290)",array(0=>$this->partnerId, 1=>self::BW_ST_MONTH+$i));
        }
    
		# Month with overage plays
		MySQLRunner::execute("INSERT INTO kalturadw.dwh_hourly_partner (partner_id, 
			date_id, hour_id, count_plays)
			VALUES(?, ?,0,500)",array(0=>$this->partnerId, 1=>self::PLAY_MONTH));
 
		# Month with overage entries
		for($i=0; $i<10; $i++)
		{
			DWHInspector::createNewEntry($this->partnerId, $i , self::ENTRY_MONTH);
		}
	}

	public function testOverage()
	{
		$this->compare(self::BW_MONTH, 300, 500, 20, 300, 0, 0 ,500, 0, 0, null, null, null, null, null, null);
		$this->compare(self::ST_MONTH, 300, 0, 0, 300, 500, 20 ,500, 500, 0, null, null, null, null, null, null);
		$this->compare(self::BW_ST_MONTH, 300, 290*1024, 29670, 300, 290, 0 ,500, 580, 20, null, null, null, null, null, null);
		$this->compare(self::PLAY_MONTH, null, null, null, null, null, null ,null, null, null, null, null, null, 100, 500, 20);
		$this->compare(self::ENTRY_MONTH, null, null, null, null, null, null ,null, null, null, 2, 10, 20, null, null, null);
	}

	private function compare($dateId, 
			$includedBW, $usedBW, $overageBW, 
			$includedST, $usedST, $overageST, 
			$includedBWST, $usedBWST, $overageBWST, 
			$includedEntry, $usedEntry, $overageEntry, 
			$includedPlay, $usedPlay, $overagePlay)
	{
		$monthId = intval($dateId/100);
		$rows = MySQLRunner::execute("CALL kalturadw.calc_partner_overage(?)", array(0=>$monthId));
		$this->assertGreaterThan(0,count($rows));
	
		for($i=0;$i<count($rows);$i++)
		{
			if($rows[$i]["publisher_id"] == $this->partnerId)
			{
				$this->assertEquals($includedBW,$rows[$i]["included_bandwidth_kb"]);
				$this->assertEquals($usedBW,$rows[$i]["actual_bandwidth_kb"]);
				$this->assertEquals($overageBW,$rows[$i]["charge_overage_bandwidth_kb"]);
		
				$this->assertEquals($includedST,$rows[$i]["included_storage_mb"]);
				$this->assertEquals($usedST,$rows[$i]["actual_storage_mb"]);
				$this->assertEquals($overageST,$rows[$i]["charge_overage_storage_mb"]);

				$this->assertEquals($includedBWST,$rows[$i]["included_total_usage_mb"]);
				$this->assertEquals($usedBWST,$rows[$i]["actual_total_usage_mb"]);
				$this->assertEquals($overageBWST,$rows[$i]["charge_overage_total_usage_mb"]);
		
				$this->assertEquals($includedEntry,$rows[$i]["included_entries"]);
				$this->assertEquals($usedEntry,$rows[$i]["actual_entries"]);
				$this->assertEquals($overageEntry,$rows[$i]["charge_overage_entries"]);

				$this->assertEquals($includedPlay,$rows[$i]["included_plays"]);
				$this->assertEquals($usedPlay,$rows[$i]["actual_plays"]);
				$this->assertEquals($overagePlay,$rows[$i]["charge_overage_plays"]);
				return;
			}
		}
		#not found partner, fail test
		$this->fail();
	}
}
?>
