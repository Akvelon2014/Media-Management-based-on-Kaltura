DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `empty_cycle_partition`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `empty_cycle_partition`(p_cycle_id VARCHAR(10))
BEGIN
	CALL drop_cycle_partition(p_cycle_id);
	CALL add_cycle_partition(p_cycle_id);
END$$

DELIMITER ;
