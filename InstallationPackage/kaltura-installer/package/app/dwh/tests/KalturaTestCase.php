<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'ComparedTable.php';

abstract class KalturaTestCase extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		self::cleanOldCycles();	
		self::register();
	}

	private static function cleanOldCycles()
    {
            global $CONF;
            self::rrmdir($CONF->CyclePath.'/process/');
            self::rrmdir($CONF->CyclePath.'/originals/');
            DWHInspector::purgeCycles();    
    }
	
	private static function rrmdir($dir) 
	{ 
		if (is_dir($dir)) 
		{		
			$objects = scandir($dir); 
			foreach ($objects as $object) 
			{ 
				if ($object != "." && $object != "..") 
				{ 
					if (filetype($dir."/".$object) == "dir")
					{
						self::rrmdir($dir."/".$object); 
						rmdir($dir."/".$object);
					} else 
					{
						unlink($dir."/".$object); 
					}
				}
			}
			if($objects!=null)
			{
				reset($objects); 			
			}
		} 		
	}

        public static function register()
        {
                KettleRunner::execute('/common/register_etl_server.ktr');
		MySQLRunner::execute("CALL kalturadw.populate_time_dim('2011-01-01 00:00:00','2011-08-01 00:00:00')");
        }

	public static function refreshBISourcesTables()
	{
		global $CONF;
		$start = new DateTime(date("Y-m-d"));
		KettleRunner::execute('/../tests/execute_dim.ktr', array('TransformationName'=>$CONF->EtlBasePath.'dimensions/refresh_bisources_tables.ktr','LastUpdatedAt'=>$start->format('Y/m/d')." 00:00:00"));
	}

	public function countPerRegex($file, $regex, $validationCallback)
        {
                $lines = file($file);
                $items = array();
                foreach($lines as $line)
                {
                        $line = urldecode($line);
                        if(call_user_func($validationCallback, $line) && preg_match($regex, $line, $matches))
                        {
                                $item = $matches[1];
                                if(!array_key_exists($item,$items))
                                {
                                        $items[$item]=0;
                                }
                                if (count($matches)>2) 
                                {
                                        $items[$item]+=$matches[2];     
                                }
                                else
                                {
                                        $items[$item]++;
                                }
                        }
                }
                return $items;
        }

	public function compareAggregation($factTables, $aggrTables, $maxDiffInPercent = 0, $factFilter = '1=1', $aggrFilter = '1=1')
        {
                $aggrGroups = DWHInspector::groupBy($aggrTables, $aggrFilter);
                $factGroups = DWHInspector::groupBy($factTables, $factFilter);

                foreach($factGroups as $id=>$measure)
                {
			if(!array_key_exists($id,$aggrGroups))
                        {
                                $this->assertEquals(0, $measure, "For the following group (larger than 0 in fact, doesn't exist in aggregation): " .$id. ". Fact = ".$measure);
                        }
                        else if ($aggrGroups[$id] == 0)
                        {
                                $this->assertEquals(0, $measure,  "For the following group:" .$id. ".Aggr = ". $aggrGroups[$id] .". Fact = ".$measure);
                        }
                        else
                        {
                                $this->assertLessThanOrEqual($maxDiffInPercent,  abs(100 - ($measure / $aggrGroups[$id] * 100)),  "For the following group:" .$id. ".Aggr = ". $aggrGroups[$id] .". Fact = ".$measure);
                        }
                }
        }
}
?>
