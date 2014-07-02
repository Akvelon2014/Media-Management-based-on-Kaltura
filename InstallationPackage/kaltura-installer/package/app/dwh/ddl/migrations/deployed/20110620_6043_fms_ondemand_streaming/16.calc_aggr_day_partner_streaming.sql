DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_streaming`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_streaming`(date_val DATE)
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	
	SELECT aggr_table INTO v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'partner';
       
	SET @s = CONCAT('
    	INSERT INTO kalturadw.',v_aggr_table,'
    		(partner_id, 
    		date_id, 
		hour_id,
		count_streaming) /* Bytes */
   		SELECT 	session_partner_id, 
			session_date_id,
			0 hour_id,
			SUM(total_bytes) count_streaming /* Bytes */
		FROM kalturadw.dwh_fact_fms_sessions f, kalturadw.dwh_dim_bandwidth_source d
		WHERE 	f.bandwidth_source_id = d.bandwidth_source_id
			and session_date_id=DATE(''',date_val,''')*1
			and d.is_live = 1 /* Only live streaming */
		GROUP BY session_partner_id, session_date_id
    	ON DUPLICATE KEY UPDATE
            count_streaming=VALUES(count_streaming);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;
