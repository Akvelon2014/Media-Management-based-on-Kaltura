DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_bandwidth`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_bandwidth`(date_val DATE)
BEGIN
	/*Temporary until dwh_hourly_partner's usage columns are dropped - we use to store data which is loaded to two different tables instead of one.*/
	DROP TABLE IF EXISTS temp_aggr_bandwidth;
	CREATE TEMPORARY TABLE temp_aggr_bandwidth (
		partner_id      	INT(11) NOT NULL,
		date_id     		INT(11) NOT NULL,
		hour_id	 		TINYINT(4) NOT NULL,
		bandwidth_source_id    	INT(11) NOT NULL,
		count_bandwidth_kb 	DECIMAL(19,4) NOT NULL
	) ENGINE = MEMORY;
	
	/*HTTP*/
    	INSERT INTO 
		temp_aggr_bandwidth
    		(partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_kb)
   	SELECT 
		partner_id, MAX(activity_date_id), 0 hour_id, bandwidth_source_id, SUM(bandwidth_bytes)/1024 count_bandwidth
	FROM 
		dwh_fact_bandwidth_usage 
	WHERE 
		activity_date_id=DATE(date_val)*1
	GROUP BY 
		partner_id, bandwidth_source_id;
	
    	/*FMS/RTMP*/
	INSERT INTO 
    		temp_aggr_bandwidth
    		(partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_kb)
 	SELECT 	
		session_partner_id, MAX(session_date_id), 0 hour_id, f.bandwidth_source_id, SUM(total_bytes)/1024 count_bandwidth 
	FROM 
		kalturadw.dwh_fact_fms_sessions f, kalturadw.dwh_dim_bandwidth_source d
	WHERE 	
		f.bandwidth_source_id = d.bandwidth_source_id
		AND session_date_id=DATE(date_val)*1
		AND d.is_live = 0 /* Only fms on demand */
	GROUP BY 
		session_partner_id, bandwidth_source_id;
	
	INSERT INTO 
		kalturadw.dwh_hourly_partner_usage
    		(partner_id, date_id, bandwidth_source_id, hour_id, count_bandwidth_kb)
   	SELECT 
		partner_id, date_id, bandwidth_source_id, hour_id, count_bandwidth_kb 
	FROM 
		temp_aggr_bandwidth 
	ON DUPLICATE KEY UPDATE
		count_bandwidth_kb=VALUES(count_bandwidth_kb);
	
	/*Temporary until dwh_hourly_partner's usage columns are dropped*/
    	INSERT INTO 
		kalturadw.dwh_hourly_partner
    		(partner_id, date_id, hour_id, count_bandwidth)
   	SELECT 	
		partner_id, date_id, hour_id, SUM(count_bandwidth_kb)
	FROM 
		temp_aggr_bandwidth 
	GROUP BY 
		partner_id, date_id, hour_id
	ON DUPLICATE KEY UPDATE
		count_bandwidth=VALUES(count_bandwidth);
END$$

DELIMITER ;
