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
		count_streaming) /* KB */
   		SELECT 	session_partner_id, 
			session_date_id,
			0 hour_id,
			SUM(total_bytes) count_streaming /* KB */
		FROM kalturadw.dwh_fact_fms_sessions 
		WHERE session_date_id=DATE(''',date_val,''')*1
		GROUP BY session_partner_id, session_date_id
    	ON DUPLICATE KEY UPDATE
            count_streaming=VALUES(count_streaming);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;