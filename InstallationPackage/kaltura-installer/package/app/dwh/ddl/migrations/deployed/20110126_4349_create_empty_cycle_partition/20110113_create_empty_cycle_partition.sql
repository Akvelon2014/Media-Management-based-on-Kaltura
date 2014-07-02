DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `empty_cycle_partition`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `empty_cycle_partition`(p_cycle_id VARCHAR(10))
BEGIN
	DECLARE table_name VARCHAR(32);
	
	SELECT source_table INTO table_name FROM kalturadw_ds.staging_areas, kalturadw_ds.cycles
	WHERE staging_areas.process_id = cycles.process_id
	AND cycles.cycle_id = p_cycle_id;
	
	CALL kalturadw_ds.empty_ods_partition(p_cycle_id, table_name);
END$$

DELIMITER ;
