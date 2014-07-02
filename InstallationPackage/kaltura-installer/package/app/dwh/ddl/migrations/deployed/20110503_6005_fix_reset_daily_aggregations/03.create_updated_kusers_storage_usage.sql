DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `create_updated_kusers_storage_usage`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `create_updated_kusers_storage_usage`(max_date DATE)
BEGIN
	TRUNCATE TABLE kalturadw_ds.updated_kusers_storage_usage;
	
	UPDATE kalturadw.aggr_managment SET start_time = NOW() WHERE is_calculated = 0 AND aggr_day < DATE(max_date) AND aggr_name = 'storage_usage_kuser_sync';
	
	INSERT INTO kalturadw_ds.updated_kusers_storage_usage 
	SELECT u.kuser_id , SUM(s.entry_additional_size_kb) storage_kb FROM 
		(SELECT DISTINCT entry_id FROM kalturadw.dwh_fact_entries_sizes s 
				INNER JOIN (SELECT DISTINCT aggr_day_int FROM kalturadw.aggr_managment WHERE is_calculated = 0 AND aggr_day < max_date AND aggr_name = 'storage_usage_kuser_sync') aggr_managment
				ON (s.entry_size_date_id = aggr_managment.aggr_day_int)) updated_entries, 
		kalturadw.dwh_fact_entries_sizes s, 
		kalturadw.dwh_dim_entries u
	WHERE s.entry_id = u.entry_id 
	AND u.entry_id = updated_entries.entry_id
	GROUP BY u.kuser_id;
END$$

DELIMITER ;
