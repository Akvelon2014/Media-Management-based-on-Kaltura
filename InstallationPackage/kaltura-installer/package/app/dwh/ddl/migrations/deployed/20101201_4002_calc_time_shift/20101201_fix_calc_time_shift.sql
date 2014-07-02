DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `calc_time_shift`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION `calc_time_shift`(date_id INT, hour_id INT, time_shift INT) RETURNS INT(11)
    NO SQL
BEGIN
	RETURN DATE_FORMAT((date_id + INTERVAL hour_id HOUR + INTERVAL time_shift HOUR), '%Y%m%d')*1;
    END$$

DELIMITER ;