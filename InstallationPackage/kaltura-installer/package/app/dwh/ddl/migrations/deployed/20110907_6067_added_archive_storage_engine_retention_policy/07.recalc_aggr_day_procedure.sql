DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `recalc_aggr_day`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `recalc_aggr_day`(p_date_val DATE, p_hour_id INT(11),p_aggr_name VARCHAR(100))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	DECLARE v_aggr_id_field VARCHAR(100);
	DECLARE v_ignore DATE;
		
	SELECT date(now() - interval archive_delete_days_back day)
	INTO v_ignore
	FROM kalturadw_ds.retention_policy
	WHERE table_name = 'dwh_fact_events';	
	
	IF (p_date_val >= v_ignore) THEN -- not so old, we don't have any data
	
		SELECT aggr_table, aggr_id_field
		INTO  v_aggr_table, v_aggr_id_field
		FROM kalturadw_ds.aggr_name_resolver
		WHERE aggr_name = p_aggr_name;	
		
		IF (v_aggr_table <> '') THEN 
			SET @s = CONCAT('delete from ',v_aggr_table,'
				where date_id = DATE(''',p_date_val,''')*1 and hour_id = ',p_hour_id);
			PREPARE stmt FROM  @s;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;	
		END IF;
		
		SET @s = CONCAT('INSERT INTO aggr_managment(aggr_name, aggr_day, aggr_day_int, hour_id, is_calculated)
		VALUES(''',p_aggr_name,''',''',p_date_val,''',''',date(p_date_val)*1,''',',p_hour_id,',0)
		ON DUPLICATE KEY UPDATE is_calculated = 0');

		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		CALL calc_aggr_day(p_date_val,p_hour_id,p_aggr_name);
	
	END IF; -- end skip old aggregations
	
END$$
	
DELIMITER ;
