DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_storage`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_storage`(date_val DATE)
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	SELECT aggr_table INTO v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'partner';
      
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
		hour_id,
    		count_storage)
   		SELECT partner_id,pa.activity_date_id date_id, 0 hour_id, SUM(amount) count_storage
		FROM dwh_fact_partner_activities  pa 
		WHERE 	pa.activity_date_id=DATE(''',date_val,''')*1
			and partner_activity_id = 3 
			AND partner_sub_activity_id=301
		GROUP BY partner_id,pa.activity_date_id
		ON DUPLICATE KEY UPDATE
    		count_storage=VALUES(count_storage);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	
END$$

DELIMITER ;