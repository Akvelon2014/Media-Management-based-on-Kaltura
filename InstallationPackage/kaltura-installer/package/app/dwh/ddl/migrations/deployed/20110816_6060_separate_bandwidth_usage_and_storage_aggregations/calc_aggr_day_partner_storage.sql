DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_storage`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_storage`(date_val DATE)
BEGIN
	DELETE FROM kalturadw.dwh_hourly_partner_usage WHERE date_id = date(date_val)*1 and IFNULL(count_bandwidth_kb,0) = 0 and (ifnull(count_storage_mb,0) > 0 or ifnull(aggr_storage_mb,0) > 0);
        UPDATE kalturadw.dwh_hourly_partner_usage SET count_storage_mb = null, aggr_storage_mb=null WHERE date_id = date(date_val)*1 and IFNULL(count_bandwidth_kb,0) > 0;
	
	DROP TABLE IF EXISTS temp_aggr_storage;
	CREATE TEMPORARY TABLE temp_aggr_storage(
		partner_id      	INT(11) NOT NULL,
		date_id     		INT(11) NOT NULL,
		hour_id	 		TINYINT(4) NOT NULL,
		count_storage_mb	DECIMAL(19,4) NOT NULL
	) ENGINE = MEMORY;
      
	INSERT INTO 	temp_aggr_storage (partner_id, date_id, hour_id, count_storage_mb)
   	SELECT 		partner_id, MAX(entry_size_date_id), 0 hour_id, SUM(entry_additional_size_kb)/1024 count_storage_mb
	FROM 		dwh_fact_entries_sizes
	WHERE		entry_size_date_id=DATE(date_val)*1
	GROUP BY 	partner_id;
	
	INSERT INTO 	kalturadw.dwh_hourly_partner_usage (partner_id, date_id, hour_id, bandwidth_source_id, count_storage_mb)
	SELECT		partner_id, date_id, hour_id, 1, count_storage_mb
	FROM		temp_aggr_storage
	ON DUPLICATE KEY UPDATE count_storage_mb=VALUES(count_storage_mb);
	
	INSERT INTO 	kalturadw.dwh_hourly_partner_usage (partner_id, date_id, hour_id, bandwidth_source_id, aggr_storage_mb)
	SELECT 		a.partner_id, a.date_id, a.hour_id, 1, SUM(b.count_storage_mb)
	FROM 		temp_aggr_storage a, dwh_hourly_partner_usage b 
	WHERE 		a.partner_id=b.partner_id AND DATE(date_val)*1 >=b.date_id AND b.hour_id = 0 AND b.bandwidth_source_id = 1 AND b.count_storage_mb<>0
	GROUP BY 	a.date_id, a.hour_id, a.partner_id
	ON DUPLICATE KEY UPDATE aggr_storage_mb=VALUES(aggr_storage_mb);
END$$

DELIMITER ;
