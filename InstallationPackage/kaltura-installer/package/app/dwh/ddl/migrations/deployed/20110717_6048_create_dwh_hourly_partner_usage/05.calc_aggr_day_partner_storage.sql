DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_storage`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_storage`(date_val DATE)
BEGIN
	/*Temporary until dwh_hourly_partner's usage columns are dropped - we use to store data which is loaded to two different tables instead of one.*/
	DROP TABLE IF EXISTS temp_aggr_storage;
	CREATE TEMPORARY TABLE temp_aggr_storage(
		partner_id      	INT(11) NOT NULL,
		date_id     		INT(11) NOT NULL,
		hour_id	 		TINYINT(4) NOT NULL,
		count_storage_mb	DECIMAL(19,4) NOT NULL
	) ENGINE = MEMORY;
      
	INSERT INTO 
		temp_aggr_storage
    		(partner_id, date_id, hour_id, count_storage_mb)
   	SELECT 
		partner_id, MAX(entry_size_date_id), 0 hour_id, SUM(entry_additional_size_kb)/1024 count_storage_mb
	FROM 
		dwh_fact_entries_sizes
	WHERE 
		entry_size_date_id=DATE(date_val)*1
	GROUP BY 
		partner_id;
	
	INSERT INTO 
		kalturadw.dwh_hourly_partner_usage
		(partner_id, date_id, hour_id, bandwidth_source_id, count_storage_mb)
	SELECT
		partner_id, date_id, hour_id, 1 /*www bandwidth_source_id*/, count_storage_mb
	FROM
		temp_aggr_storage
	ON DUPLICATE KEY UPDATE 
		count_storage_mb=VALUES(count_storage_mb);
	
	/*Temporary until dwh_hourly_partner's usage columns are dropped - we use to store data which is loaded to two different tables instead of one.*/
	INSERT INTO 
		kalturadw.dwh_hourly_partner
		(partner_id, date_id, hour_id, count_storage)
	SELECT
		partner_id, date_id, hour_id, count_storage_mb
	FROM
		temp_aggr_storage
	ON DUPLICATE KEY UPDATE 
		count_storage=VALUES(count_storage);
END$$

DELIMITER ;
