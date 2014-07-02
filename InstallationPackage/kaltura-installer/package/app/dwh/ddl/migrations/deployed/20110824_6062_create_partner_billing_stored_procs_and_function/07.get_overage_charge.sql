DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `get_overage_charge`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION `get_overage_charge`(max_amount DECIMAL(19,4), actual_amount DECIMAL(19,4), charge_units INT(11), charge_usd_per_unit DECIMAL(19,4)) RETURNS DECIMAL(19,4)
    NO SQL
BEGIN
	RETURN GREATEST(0,IFNULL(CEILING((actual_amount - max_amount)/charge_units)*charge_usd_per_unit,0));
    END$$

DELIMITER ;
