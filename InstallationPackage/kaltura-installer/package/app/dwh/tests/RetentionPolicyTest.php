<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';

class RetentionPolicyTest extends KalturaTestCase
{
    const COUNT = 10;

    private $talbe_name;

    public function setUp()
    {
        $this->table_name = 'retention_policy_spoof';
        $this->createFactAndArchiveTables($this->table_name);
    }

    private function createFactAndArchiveTables($table_name)
    {
        MySQLRunner::execute("DROP TABLE IF EXISTS kalturadw.".$table_name);
        MySQLRunner::execute("DROP TABLE IF EXISTS kalturadw.".$table_name."_archive");
    
        MySQLRunner::execute("CREATE TABLE kalturadw.".$table_name." (date_id int, value int) 
                            ENGINE=InnoDB
                            PARTITION BY RANGE (date_id) (
                                PARTITION p0 VALUES LESS THAN (0)
                            )");

        MySQLRunner::execute("CREATE TABLE kalturadw.".$table_name."_archive (date_id int, value int) 
                            ENGINE=Archive
                            PARTITION BY RANGE (date_id) (
                                PARTITION p0 VALUES LESS THAN (0)
                            )");
    }

    private function addPartition($table_name, $date)
    {
	$limit = new DateTime($date->format("Y-m-d"));
	$limit->add(new DateInterval('P1D'));
        MySQLRunner::execute("ALTER TABLE kalturadw.".$table_name." ADD PARTITION (PARTITION p".$date->format("Ymd")." VALUES LESS THAN (".$limit->format("Ymd")."))");

	for($i=0;$i<self::COUNT;$i++)
	{
		MySQLRunner::execute("INSERT INTO kalturadw.".$table_name." VALUES(".$date->format("Ymd").",".$i.")");
	}
    }         

    private function executeArchive()
    {
        MySQLRunner::execute("CALL kalturadw.move_innodb_to_archive");
    }

    private function updateRetentionPolicy($table_name, $archive, $delete)
    {
        MySQLRunner::execute("DELETE FROM kalturadw_ds.retention_policy WHERE table_name='".$table_name."'");
        MySQLRunner::execute("INSERT INTO kalturadw_ds.retention_policy (table_name, archive_start_days_back, archive_delete_days_back) 
                            VALUES ('".$table_name."',".$archive.",".$delete.")");
    }

    private function partitionExists($table_name, $date)
    {
        $rows = MySQLRunner::execute("SELECT partition_name FROM information_schema.partitions 
                                    WHERE table_name = '".$table_name."' AND partition_name = 'p".$date->format("Ymd")."'");
         return count($rows)>0;
    }

    private function countRows($table_name,$date)
    {
	$rows = MySQLRunner::execute("SELECT * FROM kalturadw.".$table_name." WHERE date_id = ".$date->format("Ymd"));
	return count($rows);
    }

    public function testRetentionNothingChanged()
    {
        $archive = 60;
        $delete = 100;
        $this->updateRetentionPolicy($this->table_name, $archive, $delete);

        $date = new DateTime();
        $date->sub(new DateInterval('P30D'));

        $this->addPartition($this->table_name, $date);
	$this->addPartition($this->table_name, new DateTime());

        $this->executeArchive();

        $this->assertTrue($this->partitionExists($this->table_name,new DateTime()));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",new DateTime()));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name,new DateTime()));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",new DateTime()));
        
	$this->assertTrue($this->partitionExists($this->table_name,$date));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",$date));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name,$date));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",$date));
    }

    public function testRetentionMovedPartition()
    {
        $archive = 60;
        $delete = 100;
        $this->updateRetentionPolicy($this->table_name, $archive, $delete);

        $date = new DateTime();
        $date->sub(new DateInterval('P70D'));

        $this->addPartition($this->table_name, $date);

	$archived = new DateTime();
	$archived->sub(new DateInterval('P40D'));

	$this->addPartition($this->table_name, $archived);

	$notMoved = new DateTime();
	$notMoved->sub(new DateInterval('P20D'));

	$this->addPartition($this->table_name, $notMoved);

        $this->executeArchive();

        $this->assertTrue($this->partitionExists($this->table_name,$notMoved));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",$notMoved));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name,$notMoved));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",$notMoved));

        $this->assertTrue($this->partitionExists($this->table_name,$archived));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",$archived));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name,$archived));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",$archived));

        $this->assertFalse($this->partitionExists($this->table_name,$date));
        $this->assertTrue($this->partitionExists($this->table_name."_archive",$date));
	$this->assertEquals(0, $this->countRows($this->table_name,$date));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name."_archive",$date));
    }

    public function testRetentionDroppedPartitionFromArchive()
    {
        $this->testRetentionMovedPartition();
                
        $archive = 30;
        $delete = 50;
        $this->updateRetentionPolicy($this->table_name, $archive, $delete);

        $date = new DateTime();
        $date->sub(new DateInterval('P70D'));

	$archived = new DateTime();
	$archived->sub(new DateInterval('P40D'));
	
	$notMoved = new DateTime();
	$notMoved->sub(new DateInterval('P20D'));
	
        $this->executeArchive();

        $this->assertTrue($this->partitionExists($this->table_name,$notMoved));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",$notMoved));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name,$notMoved));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",$notMoved));

        $this->assertFalse($this->partitionExists($this->table_name,$archived));
        $this->assertTrue($this->partitionExists($this->table_name."_archive",$archived));
	$this->assertEquals(0, $this->countRows($this->table_name,$archived));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name."_archive",$archived));

        $this->assertFalse($this->partitionExists($this->table_name,$date));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",$date));
	$this->assertEquals(0, $this->countRows($this->table_name,$date));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",$date));
    }

    public function testRetentionDroppedPartitionFromFact()
    {
        $archive = 60;
        $delete = 100;
        $this->updateRetentionPolicy($this->table_name, $archive, $delete);

        $date = new DateTime();
        $date->sub(new DateInterval('P110D'));

        $this->addPartition($this->table_name, $date);
	$this->addPartition($this->table_name, new DateTime());

        $this->executeArchive();

        $this->assertTrue($this->partitionExists($this->table_name,new DateTime()));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",new DateTime()));
	$this->assertEquals(self::COUNT, $this->countRows($this->table_name,new DateTime()));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",new DateTime()));

        $this->assertFalse($this->partitionExists($this->table_name,$date));
        $this->assertFalse($this->partitionExists($this->table_name."_archive",$date));
	$this->assertEquals(0, $this->countRows($this->table_name,$date));
	$this->assertEquals(0, $this->countRows($this->table_name."_archive",$date));
    }

}

?>
