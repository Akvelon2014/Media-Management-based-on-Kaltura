DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `calc_partner_storage_data_last_month`$$

CREATE DEFINER=`root`@`localhost` FUNCTION `calc_partner_monthly_storage`(p_month_id INT ,p_partner_id INT) RETURNS DECIMAL(19,4)
    DETERMINISTIC
BEGIN
	DECLARE avg_cont_aggr_storage DECIMAL(19,4);

        SELECT  calc_partner_storage_data_time_range (date(p_month_id*100+1)*1,last_day(p_month_id*100+1)*1,p_partner_id)
	INTO avg_cont_aggr_storage;
        RETURN avg_cont_aggr_storage;
END$$

DELIMITER ;
