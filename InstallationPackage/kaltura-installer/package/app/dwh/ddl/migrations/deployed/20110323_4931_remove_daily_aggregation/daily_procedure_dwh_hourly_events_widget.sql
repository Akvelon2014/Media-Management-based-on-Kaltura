DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_hourly_events_widget`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `daily_procedure_dwh_hourly_events_widget`(date_val DATE,p_aggr_name VARCHAR(100))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);

    SELECT aggr_table INTO  v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = p_aggr_name;
	
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
            hour_id,
			widget_id,
     		count_widget_loads)
    	SELECT  
    		partner_id,event_date_id,HOUR(event_time),widget_id,
    		SUM(IF(event_type_id=1,1,NULL)) count_widget_loads
		FROM dwh_fact_events  ev
		WHERE event_type_id IN (1) 
			AND event_date_id = DATE(''',date_val,''')*1
		GROUP BY partner_id,DATE(event_time)*1,HOUR(event_time), widget_id
    	ON DUPLICATE KEY UPDATE
    		count_widget_loads=VALUES(count_widget_loads);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;