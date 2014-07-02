DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `move_innodb_to_archive`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `move_innodb_to_archive`()
BEGIN
	DECLARE v_partition VARCHAR(256);
	DECLARE v_column VARCHAR(256);
	DECLARE v_from_archive DATE;
	DECLARE v_date_val INT;
	DECLARE v_table_name VARCHAR(256);
	DECLARE v_archive_name VARCHAR(256);
	DECLARE v_exists INT DEFAULT 0;
	DECLARE done INT DEFAULT 0;
	DECLARE c_partitions 
	CURSOR FOR 
	SELECT partition_name, p.table_name, CONCAT(p.table_name,'_archive') archive_name, partition_expression column_name, DATE(partition_description)*1 date_val
	FROM information_schema.PARTITIONS p, kalturadw_ds.retention_policy r
	WHERE LENGTH(partition_description) = 8 
    AND DATE(partition_description)*1 IS NOT NULL
    AND partition_description <= DATE(NOW() - INTERVAL r.archive_start_days_back DAY)*1
	AND p.table_name = r.table_name
	ORDER BY date_val;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	
	OPEN c_partitions;
	
	read_loop: LOOP
		FETCH c_partitions INTO v_partition, v_table_name, v_archive_name, v_column, v_date_val;
		IF done THEN
		  LEAVE read_loop;
		END IF;
		
		SELECT COUNT(*)
		INTO v_exists
		FROM information_schema.PARTITIONS p
		WHERE p.partition_description = v_date_val
		AND p.table_name = v_archive_name;
		
		IF (v_exists > 0) THEN 
			SET @s = CONCAT('ALTER TABLE ',v_archive_name,' DROP PARTITION ', v_partition);
		
                        PREPARE stmt FROM @s;
                        EXECUTE stmt;
                        DEALLOCATE PREPARE stmt;
		END IF;
		
		SET @s = CONCAT('ALTER TABLE ',v_archive_name,' ADD PARTITION (PARTITION ',v_partition,' VALUES LESS THAN (',v_date_val,'))');
		
		PREPARE stmt FROM @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET @s = CONCAT('INSERT INTO ',v_archive_name,' SELECT * FROM ',v_table_name,' WHERE ', v_column ,' < ',v_date_val);
		
		PREPARE stmt FROM @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET @s = CONCAT('ALTER TABLE ',v_table_name,' DROP PARTITION ',v_partition);
		
		PREPARE stmt FROM @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		UPDATE kalturadw_ds.retention_policy
		SET archive_last_partition = DATE(v_date_val)
		WHERE table_name = v_table_name;
		
	END LOOP;
	
END$$

DELIMITER ;