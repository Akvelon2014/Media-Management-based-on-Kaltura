DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `post_aggregation_devices`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `post_aggregation_devices`(date_val DATE, p_hour_id INT(11))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	
	SELECT aggr_table INTO v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'devices';
	
	IF (p_hour_id = 0) THEN 
		SET @s = CONCAT('INSERT INTO ',v_aggr_table,'
				(partner_id, date_id, hour_id, country_id, location_id, count_bandwidth_kb)
				SELECT 	partner_id, DATE(''',date_val,''')*1 date_id, ', p_hour_id, ', country_id,location_id, IFNULL(SUM(bandwidth_bytes), 0) / 1024 count_bandwidth_kb
				FROM dwh_fact_bandwidth_usage  b
				WHERE b.activity_date_id = DATE(''',date_val,''')*1
		GROUP BY partner_id, country_id,location_id
		ON DUPLICATE KEY UPDATE
			count_bandwidth_kb=VALUES(count_bandwidth_kb);
		');
	 
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET @s = CONCAT('INSERT INTO ',v_aggr_table,'
				(partner_id, date_id, hour_id, country_id, location_id, count_bandwidth_kb)
				SELECT 	session_partner_id, DATE(''',date_val,''')*1 date_id, ', p_hour_id, ', session_client_country_id,session_client_location_id, IFNULL(SUM(total_bytes), 0) / 1024 count_bandwidth_kb
				FROM dwh_fact_fms_sessions  b
				WHERE b.session_date_id = DATE(''',date_val,''')*1
		GROUP BY session_partner_id, session_client_country_id,session_client_location_id
		ON DUPLICATE KEY UPDATE
			count_bandwidth_kb=count_bandwidth_kb+VALUES(count_bandwidth_kb);
		');
	 
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;
	
END$$

DELIMITER ;
