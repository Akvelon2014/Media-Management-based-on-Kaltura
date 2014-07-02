DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `recalc_aggr_day`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `recalc_aggr_day`(date_val DATE,p_aggr_name VARCHAR(100))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	DECLARE v_aggr_id_field VARCHAR(100);
	DECLARE v_hourly_aggr_table VARCHAR(100);

	SELECT aggr_table,hourly_aggr_table, aggr_id_field
	INTO  v_aggr_table,v_hourly_aggr_table, v_aggr_id_field
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = p_aggr_name;	
	
	IF (v_aggr_table <> '') THEN 
		SET @s = CONCAT('delete from ',v_aggr_table,'
			where date_id = DATE(''',date_val,''')*1');
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;
	
	IF (v_hourly_aggr_table <> '') THEN 
		SET @s = CONCAT('delete from ',v_hourly_aggr_table,'
			where date_id = DATE(''',date_val,''')*1');
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;	
	END IF;
	
	SET @s = CONCAT('UPDATE aggr_managment SET is_calculated = 0 
	WHERE aggr_name = ''',p_aggr_name,''' AND aggr_day = ''',date_val,'''');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	CALL calc_aggr_day(date_val,p_aggr_name);
    END$$

DELIMITER ;