DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_streaming`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_streaming`(date_val DATE)
BEGIN
	/*Temporary until dwh_hourly_partner's usage columns are dropped - we use to store data which is loaded to two different tables instead of one.*/
	DROP TABLE IF EXISTS temp_aggr_live_streaming;
	CREATE TEMPORARY TABLE temp_aggr_live_streaming(
		partner_id      	INT(11) NOT NULL,
		date_id     		INT(11) NOT NULL,
		hour_id	 		TINYINT(4) NOT NULL,
		bandwidth_source_id    	INT(11) NOT NULL,
		count_bandwidth_bytes 	DECIMAL(19,4) NOT NULL
	) ENGINE = MEMORY;
	
      
	INSERT INTO 
		temp_aggr_live_streaming
    		(partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_bytes)
   	SELECT 	
		session_partner_id, session_date_id, 0 hour_id, f.bandwidth_source_id, SUM(total_bytes) /* Bytes */
	FROM 
		kalturadw.dwh_fact_fms_sessions f, kalturadw.dwh_dim_bandwidth_source d
	WHERE 	
		f.bandwidth_source_id = d.bandwidth_source_id
		AND session_date_id=DATE(date_val)*1
		AND d.is_live = 1 /* Only live streaming */
	GROUP BY 
		session_partner_id, session_date_id, bandwidth_source_id;
	
	INSERT INTO 
		kalturadw.dwh_hourly_partner_usage
    		(partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_kb)
   	SELECT 	
		partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_bytes/1024
	FROM 
		temp_aggr_live_streaming
    	ON DUPLICATE KEY UPDATE
		count_bandwidth_kb=VALUES(count_bandwidth_kb);
    	
	/*Temporary until dwh_hourly_partner's usage columns are dropped - we use to store data which is loaded to two different tables instead of one.*/
 	INSERT INTO 
		kalturadw.dwh_hourly_partner
    		(partner_id, date_id, hour_id, count_streaming)
   	SELECT 	
		partner_id, date_id, hour_id, SUM(count_bandwidth_bytes)
	FROM 
		temp_aggr_live_streaming
	GROUP BY 
		partner_id, date_id, bandwidth_source_id
    	ON DUPLICATE KEY UPDATE
		count_streaming=VALUES(count_streaming);
END$$

DELIMITER ;
