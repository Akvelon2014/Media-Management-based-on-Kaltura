# add the implementation function first
DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `kalturadw`.`add_partition_for_fact_table`$$
CREATE DEFINER=`etl`@`localhost` PROCEDURE  `kalturadw`.`add_partition_for_fact_table`(table_name varchar(100))
BEGIN
	DECLARE p_name,p_value VARCHAR(100);
	DECLARE p_date,_current_date DATETIME;
	DECLARE p_continue BOOL;

	SELECT NOW()
		INTO _current_date;

	SET p_continue = TRUE;

	WHILE (p_continue) DO
		SELECT EXTRACT( YEAR_MONTH FROM FROM_DAYS(MAX(partition_description))) n,
		       TO_DAYS(FROM_DAYS(MAX(partition_description)) + INTERVAL 1 MONTH ) v,
				FROM_DAYS(MAX(partition_description))
		INTO p_name,p_value, p_date
		FROM `information_schema`.`partitions`
		WHERE `partitions`.`TABLE_NAME` = table_name;
		IF (_current_date > p_date - INTERVAL 1 MONTH AND p_name is not null) THEN
			SET @s = CONCAT('alter table kalturadw.',table_name,' ADD PARTITION (partition p_' ,p_name ,' values less than (', p_value ,'))');
			PREPARE stmt FROM  @s;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		ELSE
			SET p_continue = FALSE;
		END IF;
	END WHILE;
END $$


DROP PROCEDURE IF EXISTS `kalturadw`.`add_partition_for_table`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `kalturadw`.`add_partition_for_table`(table_name VARCHAR(40))
BEGIN
	DECLARE p_name,p_value VARCHAR(100);
	DECLARE p_date,_current_date DATETIME;
	DECLARE p_continue BOOL;
	
	SELECT NOW()
		INTO _current_date;

	SET p_continue = TRUE;

	WHILE (p_continue) DO
		SELECT EXTRACT( YEAR_MONTH FROM MAX(partition_description)) n,
			   (MAX(partition_description) + INTERVAL 1 MONTH)*1  v,
			   STR_TO_DATE(MAX(partition_description),'%Y%m%d')
		INTO p_name,p_value, p_date
		FROM `information_schema`.`partitions` 
		WHERE `partitions`.`TABLE_NAME` = table_name;

		IF (_current_date > p_date - INTERVAL 1 MONTH AND p_name is not null) THEN
			SET @s = CONCAT('alter table kalturadw.' , table_name , ' ADD PARTITION (partition p_' ,p_name ,' values less than (', p_value ,'))');
			PREPARE stmt FROM  @s;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		ELSE
			SET p_continue = FALSE;
		END IF;
	END WHILE;
END$$


DROP PROCEDURE IF EXISTS `kalturadw`.`add_partitions`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE  `kalturadw`.`add_partitions`()
BEGIN
  CALL kalturadw.add_partition_for_fact_table('dwh_fact_events');
  CALL kalturadw.add_partition_for_fact_table('dwh_fact_fms_session_events');
  CALL kalturadw.add_partition_for_fact_table('dwh_fact_fms_sessions');
  
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_entry');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_domain');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_country');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_widget');
    CALL kalturadw.add_partition_for_table('dwh_aggr_events_uid');		
	CALL kalturadw.add_partition_for_table('dwh_aggr_partner');		

	CALL kalturadw.add_partition_for_table('dwh_aggr_partner_daily_usage');
END $$


DELIMITER ;

# call the CALL kalturadw.add_partitions() for the first time
CALL kalturadw.add_partitions();
