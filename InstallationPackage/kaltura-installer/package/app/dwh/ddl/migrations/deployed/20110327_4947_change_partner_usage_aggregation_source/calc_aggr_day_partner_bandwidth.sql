DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_bandwidth`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_bandwidth`(date_val DATE)
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	SELECT aggr_table INTO  v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'partner';
	
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
            hour_id,
    		count_bandwidth)
   		SELECT partner_id,',DATE(date_val) ,' date_id, 0 hour_id,
			SUM(bandwidth_bytes)/1024 count_bandwidth
		FROM dwh_fact_bandwidth_usage 
		WHERE activity_date_id=DATE(''',date_val,''')*1
		GROUP BY partner_id
    	ON DUPLICATE KEY UPDATE
    		count_bandwidth=VALUES(count_bandwidth);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;