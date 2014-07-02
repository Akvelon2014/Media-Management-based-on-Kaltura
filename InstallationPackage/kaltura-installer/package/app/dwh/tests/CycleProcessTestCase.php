<?php
require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';

abstract class CycleProcessTestCase extends KalturaTestCase
{
	const GENERATE_PARAM_FETCH_WILD_CARD = "FetchWildcard";
	const GENERATE_PARAM_FETCH_LOGS_DIR = "FetchLogsDir";
	const GENERATE_PARAM_IS_ARCHIVED = "IsArchived";
	const TRANSFER_PARAM_PROCESS_ID = "ProcessID";

	protected $fetchParams;
	
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}
	
	public function testGenerate()
	{
        	global $CONF;
		$this->fetchParams = $this->getFetchParams();
		$this->copyFilesByRegex($CONF->RuntimePath.'/tests/source', $this->fetchParams[self::GENERATE_PARAM_FETCH_LOGS_DIR], $this->fetchParams[self::GENERATE_PARAM_FETCH_WILD_CARD]);

		KettleRunner::execute($this->getGenerateJob(), $this->fetchParams);

		$cycleID = DWHInspector::getCycle('GENERATED');
		$this->isCycleDirExists($cycleID);
		$this->isCycleDirExists($cycleID,$this->fetchParams[self::GENERATE_PARAM_IS_ARCHIVED], 'originals');
		$this->isFilesRegistered($cycleID);
		$this->isFilesCreatedForProcess($cycleID);

		if ($this->fetchParams[self::GENERATE_PARAM_IS_ARCHIVED])
		{	
			$this->isFilesArchived($cycleID);
		}
	}

        protected function getGenerateJob()
        {
                return '/cycles/get_files_and_generate_cycle.kjb';
        }
	
	protected abstract function getFetchParams();
        protected abstract function getProcessParams();
        protected abstract function getTransferParams();
        protected abstract function getDSTablesToFactTables();

	private function copyFilesByRegex($fromDir, $outDir, $regex)
        {
                foreach ($this->getFilesByRegex($fromDir,$regex) as $file)
                {
                        copy($fromDir."/".$file, $outDir."/".$file);
                }
        }

	private function getFilesByRegex($fromDir, $regex)
        {
                $file_list = array();
		foreach (scandir($fromDir) as $file)
		{
                        if (is_file($fromDir . "/" . $file))
                        {
                                $file_list[] = $file;
                        }
                }
                return preg_grep("/^$regex$/", $file_list);
        }
	
	private function isFilesRegistered($cycleID)
        {
		global $CONF;

                $files = DWHInspector::getFiles($cycleID);
                $dbFileCount = count($files);
		$sourceDirFileCount = count($this->getFilesByRegex($this->fetchParams[self::GENERATE_PARAM_FETCH_LOGS_DIR], $this->fetchParams[self::GENERATE_PARAM_FETCH_WILD_CARD]));
                $this->assertEquals($sourceDirFileCount, $dbFileCount);
        }
	
	private function isFilesArchived($cycleID)
        {
                global $CONF;

                $sourceDirFileCount = count($this->getFilesByRegex($this->fetchParams[self::GENERATE_PARAM_FETCH_LOGS_DIR], $this->fetchParams[self::GENERATE_PARAM_FETCH_WILD_CARD]));
		$originalsDirFileCount = count(scandir("$CONF->CyclePath/originals/$cycleID")) - 2;
                $this->assertEquals($sourceDirFileCount, $originalsDirFileCount);
        }

	private function isFilesCreatedForProcess($cycleID)
        {
                global $CONF;
                $sourceDirFileCount = count($this->getFilesByRegex($this->fetchParams[self::GENERATE_PARAM_FETCH_LOGS_DIR], $this->fetchParams[self::GENERATE_PARAM_FETCH_WILD_CARD]));
		$processDirFileCount = count(scandir($CONF->ProcessPath."/".$cycleID)) - 2;
                $this->assertEquals($sourceDirFileCount, $processDirFileCount);
        }

	private function isCycleDirExists($cycleId, $exists = true, $path = 'process')
        {
                global $CONF;
                                
                $dir = "$CONF->CyclePath/$path/$cycleId";
		$this->assertEquals($exists, is_dir($dir),$dir);
        }

	public function testProcess()
        {
                global $CONF;
                
                $cycleID = DWHInspector::getCycle('GENERATED');
                
                KettleRunner::execute($this->getProcessJob(), $this->getProcessParams());

                $this->assertEquals($cycleID,DWHInspector::getCycle('LOADED'));
                $this->isCycleDirExists($cycleID);
        }

	protected function getProcessJob()
        {
                return '/cycles/process_cycle.kjb';
        }

	public function testTransfer()
        {
                $cycleID = DWHInspector::getCycle('LOADED');

                $ds_lines = array();
                $files = DWHInspector::getFiles($cycleID);
		$dsTablesToFactTables = $this->getDSTablesToFactTables();
                foreach($files as $fileID)
                {
			foreach (array_keys($dsTablesToFactTables) as $dsTable) 
			{
				$ds_lines[$fileID][$dsTable] = DWHInspector::countRows('kalturadw_ds.'.$dsTable,$fileID);
			}
		}
	
                DWHInspector::markAllAsAggregated();
	
		$transferParams = $this->getTransferParams();
		KettleRunner::execute($this->getTransferJob(), $transferParams);

                $this->assertEquals($cycleID,DWHInspector::getCycle('DONE'));
                $this->isCycleDirExists($cycleID, false);

                $files = DWHInspector::getFiles($cycleID);
                foreach($files as $fileID)
                {
			foreach ($dsTablesToFactTables as $dsTable => $factTable)
                        {
                        	// compare rows in ds_events and dwh_fact_events
                        	$this->assertEquals($ds_lines[$fileID][$dsTable], DWHInspector::countRows('kalturadw.'.$factTable,$fileID));
                        	// make sure ds_events was emptied
                        	$this->assertEquals(0,DWHInspector::countRows('kalturadw_ds.'.$dsTable,$fileID));
			}
                }

		foreach(DWHInspector::getAggrDatesAndHours($cycleID) as $table => $arr)
                {
	                $minDateID = DWHInspector::getResetAggregationsMinDateID($cycleID, $table);
			$postTransferAggregationTypes = DWHInspector::getPostTransferAggregationTypes($transferParams[self::TRANSFER_PARAM_PROCESS_ID], $table);
                        foreach ($arr as $dateID => $hours)
                        {
				foreach ($hours as $hourID)
                                {
					foreach ($postTransferAggregationTypes as $aggrType)
					{
						$filter = 'aggr_name = \'' . $aggrType . '\' and date_id = ' . $dateID . ' and hour_id = '. $hourID . ' and ifnull(start_time,date(19700101)) < data_insert_time';
						$rowExists = DWHInspector::rowExists('kalturadw.aggr_managment',$filter);
						$this->assertEquals($dateID >= $minDateID, $rowExists, "Row Date: $dateID $hourID $aggrType . Min Date: $minDateID");
					}
				}
                        }
                }
        }

        private function getTransferJob()
        {
                return '/cycles/transfer_cycle.kjb';
        }

	public function testAggregation()
        {
                KettleRunner::execute($this->getAggregationJob());
        }

	private function getAggregationJob()
        {
                return '/aggregation/perform_aggregations.kjb';
        }

	protected function countRows($file, $validationCallback)
        {
                $lines = file($file);
                $counter = 0;
                foreach($lines as $line)
                {
                        $line = urldecode($line);
                        if (call_user_func($validationCallback, $line))
                        {
                                $counter++;
                        }
                }
                return $counter;
        }

	protected function countInvalidLines($file, $validationCallback, $ignoreInvalidLineCallback)
	{
		$lines = file($file);
                $counter = 0;
                foreach($lines as $line)
                {
                        $line = urldecode($line);
                        if (!call_user_func($validationCallback, $line) && !call_user_func($ignoreInvalidLineCallback, $line))
                        {
                                $counter++;
                        }
                }
                return $counter;
	}

        protected function sumBytes($file, $validationCallback, $regex)
        {
                $lines = file($file);
                $sum = 0;
                foreach($lines as $line)
                {
                        $line = urldecode($line);
                        if (call_user_func($validationCallback, $line))
                        {
                                preg_match($regex, $line, $matches);
                                $sum+=$matches[2];
                        }
                }
                return $sum;
        }
}
?>
