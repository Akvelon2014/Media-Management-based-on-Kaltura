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
   		SELECT partner_id,', DATE(date_val),' date_id, 0 hour_id, SUM(entry_additional_size_kb)/1024 count_storage
		FROM dwh_fact_entries_sizes
		WHERE entry_size_date_id =DATE(''',date_val,''')*1
		GROUP BY partner_id
		ON DUPLICATE KEY UPDATE
    		count_storage=VALUES(count_storage);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	
END$$

DELIMITER ;