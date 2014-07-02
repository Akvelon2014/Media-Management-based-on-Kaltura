DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner`(date_val DATE)
BEGIN
	SET @s = CONCAT('UPDATE aggr_managment SET start_time = NOW()
	WHERE aggr_name = ''partner_usage'' AND aggr_day = ''',date_val,'''');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	CALL calc_aggr_day_partner_bandwidth(date_val);
	CALL calc_aggr_day_partner_storage(date_val);
	CALL calc_aggr_day_partner_streaming(date_val);
	CALL calc_aggr_day_partner_usage_totals(date_val);
	
	SET @s = CONCAT('UPDATE aggr_managment SET is_calculated = 1,end_time = NOW()
	WHERE aggr_name = ''partner_usage'' AND aggr_day = ''',date_val,'''');
	
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;