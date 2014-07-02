DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_usage_totals`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_usage_totals`(date_val DATE)
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
    		aggr_storage ,  /* MB */ 
		aggr_bandwidth, /* KB */
		aggr_streaming) /* KB */
		SELECT 
			a.partner_id,
			a.date_id,
            a.hour_id,
			SUM(b.count_storage) aggr_storage,
			SUM(b.count_bandwidth) aggr_bandwidth,
			SUM(b.count_streaming) aggr_streaming
		FROM dwh_hourly_partner a , dwh_hourly_partner b 
		WHERE 
			a.partner_id=b.partner_id
			AND a.date_id=DATE(''',date_val,''')*1
			AND a.date_id >=b.date_id
            AND a.hour_id = 0 AND b.hour_id = 0
		GROUP BY
			a.date_id,
            a.hour_id,
			a.partner_id
		ON DUPLICATE KEY UPDATE
			aggr_storage=VALUES(aggr_storage),
			aggr_bandwidth=VALUES(aggr_bandwidth),
			aggr_streaming=VALUES(aggr_streaming);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	
END$$

DELIMITER ;