DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner`(date_val DATE)
BEGIN
	UPDATE aggr_managment SET start_time = NOW()
	WHERE aggr_name = 'partner_usage' AND aggr_day = DATE(date_val)*1;
		
	CALL calc_aggr_day_partner_bandwidth(date_val);
	CALL calc_aggr_day_partner_storage(date_val);
	CALL calc_aggr_day_partner_streaming(date_val);
	CALL calc_aggr_day_partner_usage_totals(date_val);
	
	UPDATE aggr_managment SET is_calculated = 1,end_time = NOW()
	WHERE aggr_name = 'partner_usage' AND aggr_day = date_val;
END$$

DELIMITER ;
