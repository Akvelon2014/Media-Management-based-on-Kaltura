DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_hourly_events_widget`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `post_aggregation_widget`(date_val DATE)
BEGIN
	DECLARE v_aggr_table VARCHAR(100);

    SELECT aggr_table INTO  v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'widget';
	
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

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_hourly_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `post_aggregation_partner`(date_val DATE)
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	
	SELECT aggr_table INTO v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'partner';
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
            hour_id,
    		count_video, 
    		count_image, 
    		count_audio, 
    		count_mix,
    		count_playlist)
    	SELECT  
    		partner_id,date_id,hour_id,
    		SUM(count_video) sum_count_video,
    		SUM(count_image) sum_count_image,
    		SUM(count_audio) sum_count_audio,
    		SUM(count_mix) sum_count_mix,
    		SUM(count_playlist) sum_playlist
    	FROM (
    		SELECT partner_id,en.created_date_id date_id,HOUR(en.created_at) hour_id,
    			COUNT(IF(entry_media_type_id = 1, 1,NULL)) count_video,
    			COUNT(IF(entry_media_type_id = 2, 1,NULL)) count_image,
    			COUNT(IF(entry_media_type_id = 5, 1,NULL)) count_audio,
    			COUNT(IF(entry_media_type_id = 6, 1,NULL)) count_mix,
    			COUNT(IF(entry_type_id = 5, 1,NULL)) count_playlist
    		FROM dwh_dim_entries  en 
    		WHERE (en.entry_media_type_id IN (1,2,5,6) OR en.entry_type_id IN (5) ) 
    			AND en.created_date_id=DATE(''',date_val,''')*1
    		GROUP BY partner_id,en.created_date_id, HOUR(en.created_at)
    	) AS a
    	GROUP BY partner_id,date_id, hour_id
    	ON DUPLICATE KEY UPDATE
    		count_video=VALUES(count_video), 
    		count_image=VALUES(count_image),
    		count_audio=VALUES(count_audio),
    		count_mix=VALUES(count_mix),
    		count_playlist=VALUES(count_playlist);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
            hour_id,
    		count_users)
    	SELECT  
    		partner_id,ku.created_date_id, HOUR(ku.created_at),
    		COUNT(1)
    	FROM dwh_dim_kusers  ku
    	WHERE 
    		ku.created_date_id=DATE(''',date_val,''')*1
   		GROUP BY partner_id,ku.created_date_id, HOUR(ku.created_at)
    	ON DUPLICATE KEY UPDATE
    		count_users=VALUES(count_users) ;
        ');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	 
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
   		(partner_id, 
    		date_id,
            hour_id,
    		count_widgets)
    	SELECT  
    		partner_id,wd.created_date_id,HOUR(wd.created_at),
    		COUNT(1)
        FROM dwh_dim_widget  wd
    	WHERE 
    		wd.created_date_id=DATE(''',date_val,''')*1
   		GROUP BY partner_id,wd.created_date_id,HOUR(wd.created_at)
    	ON DUPLICATE KEY UPDATE
    		count_widgets=VALUES(count_widgets) ;
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;