<?php
require_once 'Configuration.php';
require_once 'MySQLRunner.php';
require_once 'ApiCall.php';

class DWHInspector
{
	public static function getCycle($status)
	{
		$res = MySQLRunner::execute("SELECT cycle_id FROM kalturadw_ds.cycles WHERE status = '?'",array(0=>$status));
		
		if(1!=count($res))
		{
			print("No cycle found in status - $status\n");
			exit(1);
		}
		
		foreach ($res as $row)
		{
			return $row["cycle_id"];
		}
	}
	
	public static function getFiles($cycleId)
	{
		$res = MySQLRunner::execute("SELECT file_id FROM kalturadw_ds.files WHERE cycle_id = ? AND file_status = 'IN_CYCLE'",array(0=>$cycleId));
		$files = array();
		foreach ($res as $row)
		{
			$files[]=$row["file_id"];
		}
		return $files;
	}	
	
	public static function getFileName($fileId)
	{
		$res = MySQLRunner::execute("SELECT file_name FROM kalturadw_ds.files WHERE file_id = ?",array(0=>$fileId));
		$files = array();
		foreach ($res as $row)
		{
			return $row["file_name"];
		}
	}
	
	public static function countRows($tableName, $fileID, $extra='', $join_table=null,$joined_key=null, $key_in_join_table=null)
	{
		return self::aggregateRows($tableName, $fileID, "count", "*", $extra, $join_table, $joined_key, $key_in_join_table);
	}

        public static function sumRows($tableName, $fileID, $aggregatedColumn, $extra='', $join_table=null,$joined_key=null, $key_in_join_table=null)
        {
                return self::aggregateRows($tableName, $fileID, "sum", $aggregatedColumn, $extra, $extra, $join_table, $joined_key, $key_in_join_table);
        }
	
        public static function countDistinct($table_name,$fileId,$select,$join_table=null,$joined_key=null, $key_in_join_table=null)
        {
		$key_in_join_table = $key_in_join_table ?: $joined_key;
                $join_syntax = $join_table != null && $joined_key != null ? "INNER JOIN ".$join_table." ON (".$table_name.".".$joined_key."=".$join_table.".".$key_in_join_table.")" : "";
                $res = MySQLRunner::execute("SELECT count(distinct ".$select.") amount FROM ".$table_name. " " . $join_syntax . " " . " WHERE file_id like '?' ",array(0=>$fileId));
                foreach($res as $row)
                {
                        return (int) $row["amount"];
                }
        }


	public static function aggregateRows($table_name, $fileID, $aggregateFunction, $aggregatedColumn, $extra='', $join_table=null,$joined_key=null, $key_in_join_table=null)
	{
		$key_in_join_table = $key_in_join_table ?: $joined_key;
                $join_syntax = $join_table != null && $joined_key != null ? "INNER JOIN ".$join_table." ON (".$table_name.".".$joined_key."=".$join_table.".".$key_in_join_table.")" : "";
		$res = MySQLRunner::execute("SELECT ".$aggregateFunction."(".$aggregatedColumn.") amount FROM ".$table_name. " " . $join_syntax . " " . " WHERE file_id like '?' ".$extra,array(0=>$fileID));
		foreach ($res as $row)
		{
			return $row["amount"];
		}
	}	
	
	public static function markAllAsAggregated()
	{
		MySQLRunner::execute("UPDATE kalturadw.aggr_managment SET data_insert_time = date(19700101)");
	        MySQLRunner::execute("UPDATE kalturadw_ds.parameters SET date_value = now() where id = 2");
	}
	
	public static function getAggregations($dateId, $hourId, $getAllAggregations = 0)
	{
		$rows = MySQLRunner::execute("SELECT DISTINCT aggr_name FROM kalturadw.aggr_managment WHERE date_id = ? AND hour_id = ? AND (1 = ? or ifnull(start_time,date(19700101) < data_insert_time))", 
																					array(0=>$dateId,1=>$hourId, 2=>$getAllAggregations));
		$res = array();
		foreach ($rows as $row)
		{
			$res[] = $row["aggr_name"];
		}
		return $res;
	}
	
	public static function getAggrDatesAndHours($cycleId)
	{
		$res = array();
		$staging_areas = MySQLRunner::execute("SELECT target_table, aggr_date_field, hour_id_field FROM kalturadw_ds.cycles c, kalturadw_ds.staging_areas s WHERE cycle_id = ? and c.process_id = s.process_id", array(0=>$cycleId));
		foreach ($staging_areas as $staging_area)
		{
			$date_id_column = $staging_area["aggr_date_field"];
			$hour_id_column = $staging_area["hour_id_field"];
			$table_name = $staging_area["target_table"];
			
			$rows = MySQLRunner::execute("SELECT DISTINCT ?, ? FROM ? WHERE file_id in (SELECT file_id FROM kalturadw_ds.files WHERE cycle_id = ? AND file_status = 'IN_CYCLE')",array(0=>$date_id_column, 1=>$hour_id_column, 2=>$table_name, 3=>$cycleId));
		
			$res[$table_name] = array();
			foreach ($rows as $row)
			{
				$date_id = $row[$date_id_column];
				$hour_id = $row[$hour_id_column];
				if (!array_key_exists($date_id, $res[$table_name]))
				{
					$res[$table_name][$date_id]=array();
				}
				$res[$table_name][$date_id][] = $hour_id; 
			}
		}
		return $res;
	}

	public static function getPostTransferAggregationTypes($processID, $factTable = '')
	{
		$rows = MySQLRunner::execute("SELECT post_transfer_aggregations FROM kalturadw_ds.staging_areas WHERE process_id = ? and ('' = '?' or target_table = '?') ", array(0=>$processID, 1=>$factTable, 2=>$factTable));
		$aggrTypes = array();
		foreach ($rows as $row)
		{
			preg_match_all("/'([^']+)'/", $row["post_transfer_aggregations"], $matches);
			foreach ($matches[1] as $aggrType)
			{
				$aggrTypes[$aggrType] = 1;
			}
		}
		return array_keys($aggrTypes);
	}
	
	public static function cleanDB()
	{
		global $CONF;
		
		putenv('KETTLE_HOME='.Configuration::$KETTLE_HOME);
		passthru($CONF->RuntimePath.'/setup/dwh_drop_databases.sh -d '.$CONF->RuntimePath.' -h '.$CONF->DbHostName);
		passthru('export KETTLE_HOME='.Configuration::$KETTLE_HOME.';'.$CONF->RuntimePath.'/setup/dwh_setup.sh -d '.$CONF->RuntimePath.' -h '.$CONF->DbHostName);
	}
	
	public static function groupBy($tables, $filter = '1=1')
	{
		$res = array();

		foreach ($tables as $table)
		{
			$rows = MySQLRunner::execute('SELECT '.$table->getTableKey().', sum('.$table->getTableMeasure().') amount FROM '.$table->getTableName().' WHERE '. $filter .' GROUP BY '.$table->getTableKey());
		
			foreach ($rows as $row)
			{
				if (array_key_exists($row[$table->getTableKey()],  $res))
                                {
                                         $res[$row[$table->getTableKey()]]+=$row["amount"];
                                }
                                else
                                {
                                        $res[$row[$table->getTableKey()]]=$row["amount"];
                                }
			}
		}		
		return $res;
	}
	
	public static function createEntriesFromFact()
	{
		MySQLRunner::execute('INSERT INTO kalturadw.dwh_dim_entries (entry_id, entry_media_type_id)
				SELECT DISTINCT entry_id, 1 FROM kalturadw.dwh_fact_events',array());
	}

	public static function purgeCycles($purgeData=true)
	{
		MySQLRunner::execute('TRUNCATE TABLE kalturadw_ds.files', array());		
		MySQLRunner::execute('TRUNCATE TABLE kalturadw_ds.cycles', array());
		if ($purgeData)
		{
			self::purgeData();
		}
	}

	private static function purgeData()
	{
		MySQLRunner::execute('DELETE FROM kalturadw_ds.ds_events', array());
                self::dropTablePartitions('kalturadw_ds','ds_events');
                MySQLRunner::execute('DELETE FROM kalturadw_ds.ds_bandwidth_usage', array());
                self::dropTablePartitions('kalturadw_ds','ds_bandwidth_usage');
                MySQLRunner::execute('DELETE FROM kalturadw_ds.ds_fms_session_events', array());
                self::dropTablePartitions('kalturadw_ds','ds_fms_session_events');
                MySQLRunner::execute('DELETE FROM kalturadw_ds.invalid_ds_lines', array());
                MySQLRunner::execute('DELETE FROM kalturadw_ds.invalid_event_lines', array());
                MySQLRunner::execute('DELETE FROM kalturadw_ds.invalid_fms_event_lines', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_dim_entries', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_fact_events', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_fact_bandwidth_usage', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_fact_fms_session_events', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_fact_fms_sessions', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_entry', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_entry_plays_views', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_country', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_domain', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_domain_referrer', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_uid', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_widget', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_events_devices', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_partner', array());
                MySQLRunner::execute('DELETE FROM kalturadw.dwh_hourly_partner_usage', array());
                MySQLRunner::execute('UPDATE kalturadw_ds.retention_policy SET archive_start_days_back = 2000 where archive_start_days_back < 180 ', array());
	}

	public static function cleanEtlServers()
	{
		MySQLRunner::execute("TRUNCATE TABLE kalturadw_ds.etl_servers");
	}
	
	public static function getEntryIDByFlavorID($flavorID)
	{
		$rows = MySQLRunner::execute("select entry_id from kalturadw.dwh_dim_flavor_asset where id = '?' limit 1", array(0=>$flavorID));
		if (count($rows) > 0)
		{
			return $rows[0]["entry_id"];
		}
	}

	public static function getPartnerIDByEntryID($entryID)
        {
                $rows = MySQLRunner::execute("select partner_id from kalturadw.dwh_dim_entries where entry_id = '?' limit 1", array(0=>$entryID));
                if (count($rows) > 0)
                {
                        return $rows[0]["partner_id"];
                }
        }
	
	public static function getFullDSFMSSessions($fileID,$illegalPartnersCSV)
	{
		$rows = MySQLRunner::execute("SELECT session_id, partner_id, (cs_dis_bytes - cs_con_bytes + sc_dis_bytes - sc_con_bytes) total_bytes FROM ( ".
						"SELECT session_id, MAX(partner_id) partner_id, ".
						"SUM(IF(event_type='connect', client_to_server_bytes, 0)) cs_con_bytes, ".
						"SUM(IF(event_type='disconnect', client_to_server_bytes, 0)) cs_dis_bytes, ".
						"SUM(IF(event_type='connect', server_to_client_bytes, 0)) sc_con_bytes, ".
						"SUM(IF(event_type='disconnect', server_to_client_bytes, 0)) sc_dis_bytes ".
						"FROM kalturadw_ds.ds_fms_session_events f, kalturadw.dwh_dim_fms_event_type dim ".
						"WHERE f.event_type_id = dim.event_type_id and file_id = ? ".
						"GROUP BY session_id ".
						"HAVING MAX(IF(event_type = 'connect', 1, 0))+MAX(IF(event_type = 'disconnect', 1, 0))+MAX(IF(partner_id NOT IN (?),1,0))=3) a ".
						"WHERE (cs_dis_bytes - cs_con_bytes + sc_dis_bytes - sc_con_bytes) > 0 ", array(0=>$fileID,1=>$illegalPartnersCSV));
		$res = array();
                foreach ($rows as $row)
                {
			$res[$row["session_id"]]["partnerID"] = $row["partner_id"];
                        $res[$row["session_id"]]["totalBytes"] = $row["total_bytes"];
                }
                return $res;
	}

	public static function getFactFMSSessions($fileID)
	{
		$rows = MySQLRunner::execute("SELECT DISTINCT s.session_id, s.session_partner_id, s.total_bytes ".
					     "FROM kalturadw.dwh_fact_fms_session_events e, kalturadw.dwh_fact_fms_sessions s ".
					     "WHERE e.session_id = s.session_id AND file_id = ?", array(0=>$fileID));
		$res = array();
                foreach ($rows as $row)
                {
                        $res[$row["session_id"]]["partnerID"] = $row["session_partner_id"];
			$res[$row["session_id"]]["totalBytes"] = $row["total_bytes"];
                }
                return $res;
	}

	public static function createNewPartner()
    	{
		$rows = MySQLRunner::execute("SELECT ifnull(MIN(partner_id),0) - 10 as id FROM kalturadw.dwh_dim_partners;");
		$partnerId = $rows[0]["id"];
		MySQLRunner::execute("INSERT INTO kalturadw.dwh_dim_partners (partner_id, partner_name) VALUES(?, 'TEST_PARTNER') ", array(0=>$partnerId));
		return $partnerId;
    	}

	public static function createNewEntry($partnerId, $entryIndex, $dateId)
	{
		$entryId = "TEST_".$partnerId."_".$entryIndex;
		MySQLRunner::execute("INSERT INTO kalturadw.dwh_dim_entries (partner_id, entry_id, entry_name, entry_status_id, entry_type_id, created_at, updated_at) VALUES(?,'?','?',2, 1, DATE(?), DATE(?))", 
					array(0=>$partnerId,1=>$entryId,2=>$entryId,3=>$dateId, 4=>$dateId));
		return $entryId;
	}

	public static function getUnifiedAPICalls($cycleID, $onlyErrornousCalls = false)
	{
		$errornousFilter = $onlyErrornousCalls ? 'AND IFNULL(ds.error_code_id,f.error_code_id) IS NOT NULL' : '';

		$rows = MySQLRunner::execute("SELECT ds.session_id, ds.request_index, ds.user_ip FROM kalturadw_ds.ds_incomplete_api_calls ds, kalturadw.dwh_fact_incomplete_api_calls f ".
				     "WHERE ds.session_id = f.session_id ".
				     "AND ds.request_index = f.request_index ".
				     "AND ds.user_ip = f.user_ip ".
				     "AND ds.cycle_id=? ".
		 		     "AND IFNULL(ds.api_call_date_id, f.api_call_date_id) IS NOT NULL ".
			             "AND IFNULL(ds.duration_msecs, f.duration_msecs) IS NOT NULL $onlyErrornousCalls", array(0=>$cycleID));
				
		$res = array();
		foreach ($rows as $row)
                {
                        $res[] = APICall::CreateAPICallByID($row["session_id"], $row["request_index"],$row["user_ip"]);
                }
                return $res;
	}

	public static function rowExists($table_name, $filter = '1=1')
	{
		$rows = MySQLRunner::execute("SELECT * FROM $table_name WHERE $filter");
		return count($rows) != 0;
	}
	
	public static function getResetAggregationsMinDateID($cycleID, $factTable = '')
	{
		$sql= "SELECT min(reset_aggregations_min_date)*1 min_date_id " . 
						"FROM kalturadw_ds.staging_areas s, kalturadw_ds.cycles c ". 
						"WHERE s.process_id = c.process_id ".
						"AND c.cycle_id = $cycleID and (target_table = '$factTable' or '' = '$factTable')";
		$rows = MySQLRunner::execute($sql);
		if (count($rows) > 0)
		{
			return $rows[0]["min_date_id"];
		}
		else
		{
			return 19700101;
		}
	}

	public static function registerFile($fileName, $processId, $fileSizeKb, $compressionSuffix = '', $subdir = '.')
	{
		$sql = "CALL kalturadw_ds.register_file('$fileName', $processId, $fileSizeKb, '$compressionSuffix', '$subdir')";
		MySQLRunner::execute($sql);
	}

	public static function registerEtlServer($etlServerName, $lbConstant=1)
	{
		MySQLRunner::execute("INSERT INTO kalturadw_ds.etl_servers (etl_server_name, lb_constant) VALUES ('$etlServerName', $lbConstant)");
	}

	public static function isFileRegistered($fileName, $processId, $fileSize, $compressionSuffix, $subdir, $etlServerName)
	{
		$sql = "SELECT * FROM kalturadw_ds.files f, kalturadw_ds.cycles c, kalturadw_ds.etl_servers es ".
			"WHERE f.cycle_id = c.cycle_id and c.assigned_server_id = es.etl_server_id ".
			"AND f.file_name = '$fileName' and f.process_id = $processId and f.file_size_kb = $fileSize and compression_suffix = '$compressionSuffix' and subdir = '$subdir' and etl_server_name = '$etlServerName'";
		$rows = MySQLRunner::execute($sql);
		return (count($rows) == 1);
	}
	
	public static function dropTablePartitions($tableSchema, $tableName, $initialPartition = 'p_0')
	{
		$sql = "SELECT partition_name FROM information_schema.PARTITIONS WHERE table_schema = '$tableSchema' and table_name = '$tableName' AND partition_name <> '$initialPartition'";	
		$rows = MySQLRunner::execute($sql);
		
		foreach ($rows as $row)
		{
			$sql = "ALTER TABLE $tableSchema.$tableName DROP PARTITION " . $row["partition_name"];
			MySQLRunner::execute($sql);
		}
	}
}
?>
