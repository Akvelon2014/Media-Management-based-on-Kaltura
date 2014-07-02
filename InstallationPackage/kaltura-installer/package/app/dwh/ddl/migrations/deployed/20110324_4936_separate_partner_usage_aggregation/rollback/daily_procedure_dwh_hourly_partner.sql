DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_hourly_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `daily_procedure_dwh_hourly_partner`(date_val DATE,p_aggr_name VARCHAR(100))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	
	SELECT aggr_table INTO  v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = p_aggr_name;
	
	IF ( v_aggr_id_field <> "" ) THEN
		SET v_aggr_id_field_str = CONCAT (',',v_aggr_id_field);
	ELSE
		SET v_aggr_id_field_str = "";
	END IF;
       
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
    		count_bandwidth, 
    		count_storage)
   		SELECT partner_id,pa.activity_date_id date_id, 0 hour_id,
			SUM(IF(partner_activity_id = 1, amount ,NULL)) count_bandwidth, 
			SUM(IF(partner_activity_id = 3 AND partner_sub_activity_id=301, amount,NULL)) count_storage
		FROM dwh_fact_partner_activities  pa 
		WHERE 
			pa.activity_date_id=DATE(''',date_val,''')*1
		GROUP BY partner_id,pa.activity_date_id
    	ON DUPLICATE KEY UPDATE
    		count_bandwidth=VALUES(count_bandwidth), 
    		count_storage=VALUES(count_storage);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET @s = CONCAT('
    	INSERT INTO kalturadw.',v_aggr_table,'
    		(partner_id, 
    		date_id, 
		hour_id,
		count_streaming) /* KB */
   		SELECT 	session_partner_id, 
			session_date_id,
			0 hour_id,
			SUM(total_bytes) count_streaming /* KB */
		FROM kalturadw.dwh_fact_fms_sessions 
		WHERE session_date_id=DATE(''',date_val,''')*1
		GROUP BY session_partner_id, session_date_id
    	ON DUPLICATE KEY UPDATE
            count_streaming=VALUES(count_streaming);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
		
    SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
            hour_id,
    		aggr_storage ,  /* MB */ 
		aggr_bandwidth, /* KB */
		aggr_streaming) /* KB */
		SELECT 
			a.partner_id,
			a.date_id,
            a.hour_id,
			SUM(b.count_storage) aggr_storage,
			SUM(b.count_bandwidth) aggr_bandwidth,
			SUM(b.count_streaming) aggr_streaming
		FROM dwh_hourly_partner a , dwh_hourly_partner b 
		WHERE 
			a.partner_id=b.partner_id
			AND a.date_id=DATE(''',date_val,''')*1
			AND a.date_id >=b.date_id
            AND a.hour_id = 0 AND b.hour_id = 0
		GROUP BY
			a.date_id,
            a.hour_id,
			a.partner_id
		ON DUPLICATE KEY UPDATE
			aggr_storage=VALUES(aggr_storage),
			aggr_bandwidth=VALUES(aggr_bandwidth),
			aggr_streaming=VALUES(aggr_streaming);
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