DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_bandwidth`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_bandwidth`(date_val DATE)
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	SELECT aggr_table INTO  v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'partner';
	
	DROP TABLE IF EXISTS temp_aggr_bandwidth;
	CREATE TEMPORARY TABLE temp_aggr_bandwidth (
		partner_id      INT(11) NOT NULL,
		date_id     	INT(11) NOT NULL,
		hour_id	 	TINYINT(4) NOT NULL,
		count_bandwidth BIGINT(11) UNSIGNED,
		PRIMARY KEY (partner_id, date_id, hour_id)
	) ENGINE = MEMORY;
	
	
    	INSERT INTO temp_aggr_bandwidth
    		(partner_id, 
    		date_id, 
		hour_id,
    		count_bandwidth)
   		SELECT partner_id, MAX(activity_date_id), 0 hour_id,
			SUM(bandwidth_bytes)/1024 count_bandwidth
		FROM dwh_fact_bandwidth_usage 
		WHERE activity_date_id=DATE(date_val)*1
		GROUP BY partner_id;
    		
	INSERT INTO temp_aggr_bandwidth
    		(partner_id, 
    		date_id, 
		hour_id,
		count_bandwidth)
   		SELECT 	session_partner_id, 
			MAX(session_date_id),
			0 hour_id,
			SUM(total_bytes)/1024 count_bandwidth 
		FROM kalturadw.dwh_fact_fms_sessions f, kalturadw.dwh_dim_bandwidth_source d
		WHERE 	f.bandwidth_source_id = d.bandwidth_source_id
			AND session_date_id=DATE(date_val)*1
			AND d.is_live = 0 /* Only fms on demand */
		GROUP BY session_partner_id
	ON DUPLICATE KEY UPDATE
            count_bandwidth=VALUES(count_bandwidth)+count_bandwidth;
	
	SET @s = CONCAT('
    	INSERT INTO kalturadw.',v_aggr_table,'
    		(partner_id, 
    		date_id, 
		hour_id,
		count_bandwidth)
   		SELECT 	partner_id, 
			date_id,
			hour_id,
			count_bandwidth 
		FROM temp_aggr_bandwidth
    	ON DUPLICATE KEY UPDATE
            count_bandwidth=VALUES(count_bandwidth);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;
