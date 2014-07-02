DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `drop_cycle_partition`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `drop_cycle_partition`(p_cycle_id VARCHAR(10))
BEGIN
	DECLARE table_name VARCHAR(32);
	DECLARE p_exists INT;
	DECLARE done INT DEFAULT 0;
	DECLARE staging_areas_cursor CURSOR FOR SELECT source_table 
						FROM kalturadw_ds.staging_areas, kalturadw_ds.cycles 
						WHERE staging_areas.process_id = cycles.process_id AND cycles.cycle_id = p_cycle_id;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	OPEN staging_areas_cursor;	
	read_loop: LOOP
		FETCH staging_areas_cursor INTO table_name;
		IF done THEN
			LEAVE read_loop;
		END IF;
		
		SELECT COUNT(*) INTO p_exists
		FROM information_schema.PARTITIONS 
		WHERE partition_name = CONCAT('p_',p_cycle_id)
		AND table_name = table_name
		AND table_schema = 'kalturadw_ds';
		
		IF(p_exists>0) THEN
			SET @s = CONCAT('alter table kalturadw_ds.',table_name,' drop PARTITION  p_' ,p_cycle_id);
			PREPARE stmt FROM  @s;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		END IF;
	END LOOP;
	CLOSE staging_areas_cursor;
END$$

DELIMITER ;
