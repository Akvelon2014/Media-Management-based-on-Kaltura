DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `drop_ods_partition`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `drop_ods_partition`(
	partition_number VARCHAR(10), p_table_name VARCHAR(32)
	)
BEGIN
	DECLARE p_exists INT;
	
	SELECT COUNT(*) INTO p_exists
	FROM information_schema.partitions 
	WHERE partition_name = CONCAT('p_',partition_number)
	AND table_name = p_table_name
	AND table_schema = 'kalturadw_ds';
	
	IF(p_exists>0) THEN
		SET @s = CONCAT('alter table kalturadw_ds.',p_table_name,' drop PARTITION  p_' ,
				partition_number );
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;		
	END IF;
END$$

DELIMITER ;