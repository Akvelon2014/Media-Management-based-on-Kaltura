<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';

class StorageTest extends KalturaTestCase
{
	const DATE_ID = 20110801;
	private $partnerId;
	private $expected;
	private $delta;
	
	public function setUp()
	{
		$this->expected = array();
		$this->delta = 0;
		$this->partnerId = DWHInspector::createNewPartner();
		$this->createNewEntry($this->partnerId);
	}

	private function createNewEntry($partnerId, $count=10)
	{
		for($i = 0;$i<$count;$i++)
		{
			$entryId = DWHInspector::createNewEntry($this->partnerId, $i, self::DATE_ID);
			$this->createNewFlavor($partnerId, $entryId);
		}
	}
	
	private function createNewFlavor($partnerId, $entryId, $count=10)
	{
		$rows = MySQLRunner::execute("SELECT ifnull(MAX(id) + 2,0) as id FROM kalturadw.dwh_dim_file_sync");
		$fileSyncId = floatval($rows[0]["id"]);

		for($i = 0;$i<$count;$i++)
		{
			$flavorId = $entryId."_".$i;
			MySQLRunner::execute("INSERT INTO kalturadw.dwh_dim_flavor_asset (partner_id, entry_id, id, updated_at) VALUES(?,'?','?', DATE(?))", array(0=>$partnerId,1=>$entryId,2=>$flavorId, 3=>self::DATE_ID));
			
			$fileSize = rand(100,10000);

			MySQLRunner::execute("INSERT INTO kalturadw.dwh_dim_file_sync (partner_id, object_type, object_sub_type, object_id, file_size, id, updated_at, ready_at, original, status, version) 
						VALUES(?,4,1,'?',?, ?, DATE(?), DATE(?), 1, 2, 1)", array(0=>$partnerId,1=>$flavorId, 2=>$fileSize, 3=>($fileSyncId + $i), 4=>self::DATE_ID, 5=>self::DATE_ID));
			
			if(!array_key_exists($entryId,$this->expected))
			{
					$this->expected[$entryId]=0;
			}
			$this->expected[$entryId] += $fileSize;
			$this->delta += $fileSize;
		}
	}
	
	public function testCalcEntrySizes()
	{
		MySQLRunner::execute("CALL kalturadw.calc_entries_sizes(?)",array(0=>self::DATE_ID));
		$this->compare(self::DATE_ID);
	}
	
	public function testDeleteEntry()
	{
		MySQLRunner::execute("CALL kalturadw.calc_entries_sizes(?)",array(0=>self::DATE_ID));
		$this->deleteEntry(self::DATE_ID+1);
		
		MySQLRunner::execute("CALL kalturadw.calc_entries_sizes(?)",array(0=>self::DATE_ID+1));
		$this->compare(self::DATE_ID+1);		
	}
	
	private function deleteEntry($dateId)
	{
		foreach($this->expected as $entryId=>$entrySize)
		{
			$rows = MySQLRunner::execute("SELECT sum(entry_additional_size_kb) size FROM kalturadw.dwh_fact_entries_sizes WHERE entry_id = '?'",array(0=>$entryId));
			$size = floatval($rows[0]["size"]);

			echo "delete " .$entryId . " with size " . $size . "\n";

			MySQLRunner::execute("UPDATE kalturadw.dwh_dim_entries SET entry_status_id = 3, modified_at=DATE(?) WHERE entry_id = '?'", array(0=>$dateId,1=>$entryId));
			$this->expected[$entryId] = 0;
			$this->delta = -$size*1024;
			return;
		}
	}
	
	public function testUpdateEntrySize()
	{
		MySQLRunner::execute("CALL kalturadw.calc_entries_sizes(?)",array(0=>self::DATE_ID));		
		MySQLRunner::execute("CALL kalturadw.calc_entries_sizes(?)",array(0=>self::DATE_ID+1));		
		$this->updateEntrySize(self::DATE_ID+2);
		
		MySQLRunner::execute("CALL kalturadw.calc_entries_sizes(?)",array(0=>self::DATE_ID+2));
		$this->compare(self::DATE_ID+2);

	}
	
	private function updateEntrySize($dateId)
	{
		$rows = MySQLRunner::execute("SELECT MAX(id) + 2 as id FROM kalturadw.dwh_dim_file_sync");
		$fileSyncId = floatval($rows[0]["id"]);
		
		foreach($this->expected as $entryId=>$entrySize)
		{
			$size = 4096;
			$flavorId = $entryId."_0";
			
			MySQLRunner::execute("INSERT INTO kalturadw.dwh_dim_file_sync (partner_id, object_type, object_sub_type, object_id, file_size, id, updated_at, ready_at, original, status, version) 
						SELECT partner_id, object_type, object_sub_type, object_id, file_size + ? , ?, DATE(?), DATE(?), original, status, 2 FROM kalturadw.dwh_dim_file_sync
						WHERE object_id = '?'", array(0=>$size, 1=>$fileSyncId, 2=>$dateId, 3=>$dateId, 4=>$flavorId));
			$this->expected[$entryId] += $size;
			$this->delta = $size;
			$fileSyncId++;

			MySQLRunner::execute("UPDATE kalturadw.dwh_dim_flavor_asset SET updated_at = DATE(?) WHERE id = '?'", array(0=>$dateId, 1=>$flavorId));
			return;
		}
	}
	
	private function compare($dateId)
	{
		echo "Partner : ".$this->partnerId." Date : ".$dateId."\n";
		$rows = MySQLRunner::execute("SELECT entry_id, sum(entry_additional_size_kb) size FROM kalturadw.dwh_fact_entries_sizes WHERE entry_size_date_id <= ? AND partner_id = ? GROUP BY entry_id" , array(0=>$dateId, 1=>$this->partnerId));
		
		$this->assertEquals(count($this->expected), count($rows));

		$expectedTotal = 0;		
		foreach($rows as $row)
		{
			$size = floatval($row["size"]);
			echo "x:" .$row["entry_id"]." ".round($this->expected[$row["entry_id"]]/1024,3) ." " .$size . "\n";
			$this->assertLessThan(0.01,abs(round($this->expected[$row["entry_id"]]/1024,3) - $size));
			$expectedTotal += $size;			
		}
		
		$rows = MySQLRunner::execute("SELECT sum(aggr_storage_mb) size FROM kalturadw.dwh_hourly_partner_usage WHERE date_id = ? AND partner_id = ?" , array(0=>$dateId, 1=>$this->partnerId));
		$actualTotal = floatval($rows[0]["size"]);
		echo "total: expected " .round($expectedTotal/1024,3) ." actual " .$actualTotal . "\n";
		$this->assertLessThan(0.01,abs(round($expectedTotal/1024,3) - $actualTotal));
		
		$rows = MySQLRunner::execute("SELECT sum(added_storage_mb) - sum(deleted_storage_mb) size FROM kalturadw.dwh_hourly_partner_usage WHERE date_id = ? AND partner_id = ?" , array(0=>$dateId, 1=>$this->partnerId));
		$actualDelta = floatval($rows[0]["size"]);
		echo "delta: expected " .round($this->delta/1024/1024,3) ." actual " .$actualDelta. "\n";
		$this->assertLessThan(0.01,abs(round($this->delta/1024/1024,3) - $actualDelta));

	}
}
?>

