<?php

require_once 'Configuration.php';
require_once 'KettleRunner.php';
require_once 'DWHInspector.php';
require_once 'MySQLRunner.php';
require_once 'KalturaTestCase.php';
require_once 'CycleProcessTestCase.php';
require_once 'ComparedTable.php';
require_once 'ApiCall.php';

class ApiCallsTest extends CycleProcessTestCase
{
	protected function getDSTablesToFactTables()
        {
                $dsTableToFactTables = array();
                $dsTablesToFactTables["ds_api_calls"]="dwh_fact_api_calls";
                $dsTablesToFactTables["ds_incomplete_api_calls"]="dwh_fact_incomplete_api_calls";
                $dsTablesToFactTables["ds_errors"]="dwh_fact_errors";
                return $dsTableToFactTables;
        }

	protected function getFetchParams()
        {
                global $CONF;
                return array(self::GENERATE_PARAM_FETCH_LOGS_DIR=>$CONF->APICallsLogsDir,
                                        self::GENERATE_PARAM_FETCH_WILD_CARD=>$CONF->APICallsWildcard,
                                        'FetchMethod' =>$CONF->APICallsFetchMethod,
                                        'ProcessID'=>$CONF->APICallsProcessID,
                                        'FetchJob'=>$CONF->EtlBasePath.'/common/fetch_files.kjb',
                                        'FetchFTPServer'=>$CONF->APICallsFTPServer,
                                        'FetchFTPPort'=>$CONF->APICallsFTPPort,
                                        'FetchFTPUser'=>$CONF->APICallsFTPUser,
                                        'FetchFTPPassword'=>$CONF->APICallsFTPPassword,
                                        'TempDestination'=>$CONF->ExportPath.'/dwh_inbound/fms_streaming',
                                        self::GENERATE_PARAM_IS_ARCHIVED=>'True');
        }

        protected function getProcessParams()
        {
                global $CONF;

                return array('ProcessID'=>$CONF->APICallsProcessID,
                             'ProcessJob'=>$CONF->EtlBasePath.'/api_calls/process/process_api_calls.kjb');
        }

        protected function getTransferParams()
        {
                global $CONF;

                return array(self::TRANSFER_PARAM_PROCESS_ID=>$CONF->APICallsProcessID);
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
		
			// compare rows in full api_calls to the ones in the file to rows in file
                        $this->assertEquals(DWHInspector::countRows('kalturadw_ds.ds_api_calls',$fileID),count($this->getFileApiFullCalls($filename)));
			// compare rows in partial api_calls to the ones in the file to rows in file
                        $this->assertEquals(DWHInspector::countRows('kalturadw_ds.ds_incomplete_api_calls',$fileID),count($this->getFileApiIncompleteCalls($filename)));
		
			// compare number of distinct actions and number of rows per action
			$collection = $this->getFullCallsPerEntity($filename, "ACTION", "unknown");
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_api_calls',$fileID,'action_name','kalturadw.dwh_dim_api_actions','action_id'), count($collection));
	                foreach($collection as $objectID=>$val)
	                {
	                        $res = DWHInspector::countRows('kalturadw_ds.ds_api_calls',$fileID," and  action_name = '$objectID'", 'kalturadw.dwh_dim_api_actions','action_id');
	                        $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val action_name: $objectID");
	                }
			
			// compare number of distinct serivces and number of rows per service
			$collection = $this->getFullCallsPerEntity($filename, "SERVICE", "unknown");
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_api_calls',$fileID,'service_name','kalturadw.dwh_dim_api_actions','action_id'), count($collection));
			foreach($collection as $objectID=>$val)
                        {
                                $res = DWHInspector::countRows('kalturadw_ds.ds_api_calls',$fileID," and  service_name = '$objectID'", 'kalturadw.dwh_dim_api_actions','action_id');
                                $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val service_name: $objectID");
                        }

			// compare number of distinct partners and number of rows per partner
			$collection = $this->getFullCallsPerEntity($filename, "PARTNER_ID");
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_api_calls',$fileID,'partner_id'), count($collection));
                        foreach($collection as $objectID=>$val)
                        {
                                $res = DWHInspector::countRows('kalturadw_ds.ds_api_calls',$fileID," and  partner_id = '$objectID'");
                                $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val partner_id: $objectID");
                        }

			$collection = $this->sumFullCallsPerEntity($filename, "PARTNER_ID", "DURATION");
			foreach($collection as $partnerID=>$duration)
			{
				$res = DWHInspector::sumRows('kalturadw_ds.ds_api_calls', $fileID, 'duration_msecs', " and  partner_id = '$partnerID'");
				$maxDiffInPercent = 1;
				$this->assertLessThanOrEqual($maxDiffInPercent,  abs(100 - ($duration / $res * 100)),  "Diff is bigger than $maxDiffInPercent percent - Expected(db): $res, Actual(file): $duration partner_id: $partnerID");
			}

			// compare number of distinct tags and number of rows per tags
                        $collection = $this->getFullCallsPerEntity($filename, "CLIENT_TAG", "unknown");
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_api_calls',$fileID,'client_tag_name', 'kalturadw.dwh_dim_client_tags', 'client_tag_id'),count($collection));
			foreach($collection as $objectID=>$val)
                        {
                                $res = DWHInspector::countRows('kalturadw_ds.ds_api_calls',$fileID," and  client_tag_name = '$objectID'", 'kalturadw.dwh_dim_client_tags', 'client_tag_id');
                                $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val client_tag_name: $objectID");
                        }

			// compare number of distinct is_admins and number of rows per is_admin
                        $collection = $this->getFullCallsPerEntity($filename, "IS_ADMIN");
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_api_calls',$fileID,'is_admin'),count($collection));
			foreach($collection as $objectID=>$val)
                        {
                                $res = DWHInspector::countRows('kalturadw_ds.ds_api_calls',$fileID," and  is_admin = '$objectID'");
                                $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val is_admin: $objectID");

                        }

			// compare number of distinct partners and number of rows per partner
                        $collection = $this->getFullCallsPerEntity($filename, "PARTNER_ID", '', true);
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_errors',$fileID,'partner_id'), count($collection));
                        foreach($collection as $objectID=>$val)
                        {
                                $res = DWHInspector::countRows('kalturadw_ds.ds_errors',$fileID," and partner_id = '$objectID'");
                                $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val partner_id: $objectID");
                        }


			$collection = $this->getFullCallsPerEntity($filename, "ERROR_CODE", '', true);
                        $this->assertEquals(DWHInspector::countDistinct('kalturadw_ds.ds_errors',$fileID,'error_code_name', 'kalturadw.dwh_dim_error_codes', 'error_code_id'), count($collection));
                        foreach($collection as $objectID=>$val)
                        {
                                $res = DWHInspector::countRows('kalturadw_ds.ds_errors',$fileID," and error_code_name = '$objectID'", 'kalturadw.dwh_dim_error_codes', 'error_code_id');
                                $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val error_code_name: $objectID");
                        }
			
			
			// make sure there are very little invalid lines
                        $this->assertEquals($this->countInvalidLines($filename,
                                                                        array($this, 'validLine'),
                                                                        array($this, 'ignoredLine')),                                                                        
                                        DWHInspector::countRows('kalturadw_ds.invalid_ds_lines',$fileID));
		}
	}

	public function validLine($line)
	{
		return APICall::validLine($line);
	}

	public function ignoredLine($line)
        {
                return APICall::ignoredLine($line);
        }
	
	public function testTransfer()
	{
		$cycleID = DWHInspector::getCycle('LOADED');
		$files = DWHInspector::getFiles($cycleID);

                $unifiedApiCalls = DWHInspector::getUnifiedAPICalls($cycleID);
                $errornousUnifiedApiCalls = DWHInspector::getUnifiedAPICalls($cycleID, true);

		parent::testTransfer();
		foreach ($unifiedApiCalls as $call)
		{
			$this->assertEquals(DWHInspector::countRows('kalturadw.dwh_fact_api_calls', '%', "and CONCAT(session_id,'_',request_index) = '". $call->getID()."'"), 1, "APICall ID ". $call->getID());
			$this->assertEquals(DWHInspector::countRows('kalturadw.dwh_fact_incomplete_api_calls', '%', "and CONCAT(session_id,'_',request_index) = '". $call->getID()."'"), 0, "APICall ID ". $call->getID());
			$this->assertEquals(DWHInspector::countRows('kalturadw.dwh_fact_errors', '%', "and error_object_id = '". $call->getID()."'"), 1, "APICall ID ". $call->getID());
		}
	}

	public function testAggregation()
	{
		parent::testAggregation();
		$this->compareAggregation(array(new ComparedTable('partner_id', 'kalturadw.dwh_fact_api_calls', 'success')),
                                          array(new ComparedTable('partner_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_success, 0)')));
		$this->compareAggregation(array(new ComparedTable('partner_id', 'kalturadw.dwh_fact_api_calls', '*')),
                                          array(new ComparedTable('partner_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_calls, 0)')));
		$this->compareAggregation(array(new ComparedTable('partner_id', 'kalturadw.dwh_fact_api_calls', 'duration_msecs')),
                                          array(new ComparedTable('partner_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(sum_duration_msecs, 0)')));
		$this->compareAggregation(array(new ComparedTable('partner_id', 'kalturadw.dwh_fact_api_calls', 'is_in_multi_request')),
                                          array(new ComparedTable('partner_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_is_in_multi_request, 0)')));
		$this->compareAggregation(array(new ComparedTable('partner_id', 'kalturadw.dwh_fact_api_calls', 'is_admin')),
                                          array(new ComparedTable('partner_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_is_admin, 0)')));
		$this->compareAggregation(array(new ComparedTable('action_id', 'kalturadw.dwh_fact_api_calls', 'success')),
                                          array(new ComparedTable('action_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_success, 0)')));
                $this->compareAggregation(array(new ComparedTable('action_id', 'kalturadw.dwh_fact_api_calls', '*')),
                                          array(new ComparedTable('action_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_calls, 0)')));
                $this->compareAggregation(array(new ComparedTable('action_id', 'kalturadw.dwh_fact_api_calls', 'duration_msecs')),
                                          array(new ComparedTable('action_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(sum_duration_msecs, 0)')));
                $this->compareAggregation(array(new ComparedTable('action_id', 'kalturadw.dwh_fact_api_calls', 'is_in_multi_request')),
                                          array(new ComparedTable('action_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_is_in_multi_request, 0)')));
                $this->compareAggregation(array(new ComparedTable('action_id', 'kalturadw.dwh_fact_api_calls', 'is_admin')),
                                          array(new ComparedTable('action_id', 'kalturadw.dwh_hourly_api_calls', 'ifnull(count_is_admin, 0)')));

		$this->compareAggregation(array(new ComparedTable('error_code_id', 'kalturadw.dwh_fact_errors', '*')),
                                          array(new ComparedTable('error_code_id', 'kalturadw.dwh_hourly_errors', 'ifnull(count_errors, 0)')));
	}	

	private function AssertEntity($filename, $entityName, $fileID, $tableEntityName)
	{
		$collection = call_user_func($countPerEntityCallBack, $filename);
                $this->assertEquals(count($collection), DWHInspector::countDistinct('kalturadw_ds.ds_api_', $fileID, $tableEntityName), $countPerEntityCallBack[1]);

                foreach($collection as $objectID=>$val)
                {
                	$res = DWHInspector::countRows('kalturadw_ds.ds_fms_session_events',$fileID," and $tableEntityName = '$objectID'");
                        $this->assertEquals($res, $val, "Expected(db): $res, Actual(file): $val $tableEntityName: $objectID");
                }
	}
	

	private function getFileApiFullCalls($file)
        {
                $calls = array();
                $requestStarts = array();
                $requestEnds = array();
                $lines = file($file);
                foreach($lines as $line)
                {
                        if (!APICall::ignoredLine($line) && APICall::validLine($line))
                        {
                                $id = APICall::getLineID($line);
                                if (strpos($line, 'request_start')>-1)
                                {
                                        if (array_key_exists($id, $requestEnds))
                                        {
                                                $requestEnds[$id]->update($line);
                                                $calls[$id]=$requestEnds[$id];
                                        }
                                        else
                                        {
                                                $requestStarts[$id] = APICall::CreateAPICallByLine($line);
                                        }
                                }
                                else if (array_key_exists($id, $requestStarts))
                                {
                                        $requestStarts[$id]->update($line);
                                        $calls[$id]=$requestStarts[$id];
                                }
                                else
                                {
                                        $requestEnds[$id] = APICall::CreateAPICallByLine($line);
                                }
                        }
                }
                return $calls;
        }

	private function getFileApiIncompleteCalls($file)
	{
		$requestStarts = array();
                $requestEnds = array();
                $lines = file($file);
                foreach($lines as $line)
                {
			if (!APICall::ignoredLine($line) && APICall::validLine($line))
                        {
                                $id = APICall::getLineID($line);
                                if (strpos($line, 'request_start')>-1)
                                {
                                        if (array_key_exists($id, $requestEnds))
                                        {
                                                unset($requestEnds[$id]);
                                                continue;
                                        }
                                        $requestStarts[$id] = APICall::CreateAPICallByLine($line);
                                }
                                else if (strpos($line, 'request_end')>-1)
                                {
                                        if (array_key_exists($id, $requestStarts))
                                        {
                                                unset($requestStarts[$id]);
                                                continue;
                                        }
                                        $requestEnds[$id] = APICall::CreateAPICallByLine($line);
                                }
                        }
                }
                return array_merge($requestStarts, $requestEnds);
	}

	public static function getFullCallsPerEntity($file, $entityName, $defaultValue = '', $onlyErrornousCalls = false, $aggregatedMeasure = '')
        {
                $calls = self::getFileApiFullCalls($file);
		$errorCodeIndexer = 'ERROR_CODE';
                $collection = array();
                foreach($calls as $callID => $call)
                {
			if ($onlyErrornousCalls && in_array($call->$errorCodeIndexer, array('', '0')))
			{
				continue;
			}
                        $objectID = $call->$entityName;
                        $objectID = $objectID == '' ? $defaultValue : $objectID;
			if (!array_key_exists($objectID, $collection))
                        {
                                $collection[$objectID] = $aggregatedMeasure == '' ? 1 : $call->$aggregatedMeasure;
                        }
                        else
                        {
                                $collection[$objectID] += $aggregatedMeasure == '' ? 1 : $call->$aggregatedMeasure;
                        }
                }
                return $collection;
        }

	public static function sumFullCallsPerEntity($file, $entityName, $aggregatedMeasure)
	{
		return self::getFullCallsPerEntity($file, $entityName, $defaultValue = '', $onlyErrornousCalls = false, $aggregatedMeasure);
	}

}

?>
