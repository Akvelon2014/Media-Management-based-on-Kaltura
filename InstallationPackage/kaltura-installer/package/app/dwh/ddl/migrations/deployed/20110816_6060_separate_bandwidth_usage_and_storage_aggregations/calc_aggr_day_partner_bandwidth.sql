DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_bandwidth`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_bandwidth`(p_date_val DATE)
BEGIN
	DELETE FROM kalturadw.dwh_hourly_partner_usage WHERE date_id = date(p_date_val)*1 and IFNULL(count_bandwidth_kb,0) > 0 and ifnull(count_storage_mb,0) = 0 and ifnull(aggr_storage_mb,0) = 0;
        UPDATE kalturadw.dwh_hourly_partner_usage SET count_bandwidth_kb = null WHERE date_id = date(p_date_val)*1 and (ifnull(count_storage_mb,0) > 0 or ifnull(aggr_storage_mb,0) > 0);
	
   	INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_kb)
   	SELECT partner_id, MAX(activity_date_id), 0 hour_id, bandwidth_source_id, SUM(bandwidth_bytes)/1024 count_bandwidth
	FROM dwh_fact_bandwidth_usage 
	WHERE activity_date_id=DATE(p_date_val)*1
	GROUP BY partner_id, bandwidth_source_id
	ON DUPLICATE KEY UPDATE	count_bandwidth_kb=VALUES(count_bandwidth_kb);
	
	
    	INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, date_id, hour_id, bandwidth_source_id, count_bandwidth_kb)
 	SELECT session_partner_id, MAX(session_date_id), 0 hour_id, f.bandwidth_source_id, SUM(total_bytes)/1024 count_bandwidth 
	FROM kalturadw.dwh_fact_fms_sessions f, kalturadw.dwh_dim_bandwidth_source d
	WHERE f.bandwidth_source_id = d.bandwidth_source_id AND session_date_id=DATE(p_date_val)*1
	GROUP BY session_partner_id, bandwidth_source_id
	ON DUPLICATE KEY UPDATE	count_bandwidth_kb=VALUES(count_bandwidth_kb);
	
        UPDATE aggr_managment SET is_calculated = 1, end_time = NOW() WHERE aggr_name = 'bandwidth_usage' AND aggr_day_int = DATE(p_date_val)*1;
END$$

DELIMITER ;
