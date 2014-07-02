DELIMITER $$

DROP FUNCTION IF EXISTS `kalturadw`.`calc_month_id`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION `kalturadw`.`calc_month_id`(date_id INT(11)) 
	RETURNS INT DETERMINISTIC
BEGIN
	RETURN FLOOR(date_id/100);
    END$$

DELIMITER ;
