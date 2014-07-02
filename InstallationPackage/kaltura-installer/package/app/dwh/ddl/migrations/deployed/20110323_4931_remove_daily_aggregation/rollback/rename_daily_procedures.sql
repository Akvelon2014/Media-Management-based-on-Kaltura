DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_aggr_partner_20110325`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `daily_procedure_dwh_aggr_partner`(date_val DATE,aggr_name VARCHAR(100))
BEGIN
	DECLARE aggr_table VARCHAR(100);
	DECLARE aggr_id_field VARCHAR(100);
	SET aggr_table = kalturadw.resolve_aggr_name(aggr_name,'aggr_table');
	SET aggr_id_field = kalturadw.resolve_aggr_name(aggr_name,'aggr_id_field');
	
	 
	SET @s = CONCAT('
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
    		count_video, 
    		count_image, 
    		count_audio, 
    		count_mix,
    		count_playlist)
    	SELECT  
    		partner_id,date_id,
    		SUM(count_video) sum_count_video,
    		SUM(count_image) sum_count_image,
    		SUM(count_audio) sum_count_audio,
    		SUM(count_mix) sum_count_mix,
    		SUM(count_playlist) sum_playlist
    	FROM (
    		SELECT partner_id,en.created_date_id date_id,
    			COUNT(IF(entry_media_type_id = 1, 1,NULL)) count_video,
    			COUNT(IF(entry_media_type_id = 2, 1,NULL)) count_image,
    			COUNT(IF(entry_media_type_id = 5, 1,NULL)) count_audio,
    			COUNT(IF(entry_media_type_id = 6, 1,NULL)) count_mix,
    			COUNT(IF(entry_type_id = 5, 1,NULL)) count_playlist
    		FROM kalturadw.dwh_dim_entries  en 
    		WHERE (en.entry_media_type_id IN (1,2,5,6) OR en.entry_type_id IN (5) ) /*entry_media_type_id / entry_type_id %*/
    			AND en.created_date_id=DATE(''',date_val,''')*1
    		GROUP BY partner_id,en.created_date_id
    	) AS a
    	GROUP BY partner_id,date_id
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
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
    		count_bandwidth, /* KB */
    		count_storage) /* MB */
   		SELECT partner_id,pa.activity_date_id date_id,
			SUM(IF(partner_activity_id = 1, amount ,NULL)) count_bandwidth, /* KB */
			SUM(IF(partner_activity_id = 3 AND partner_sub_activity_id=301, amount,NULL)) count_storage /* MB */
		FROM kalturadw.dwh_fact_partner_activities  pa 
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
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
		count_streaming) /* KB */
   		SELECT 	session_partner_id, 
			session_date_id,
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
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
    		aggr_storage ,   /* MB */
			aggr_bandwidth, /* KB */
            aggr_streaming   /* KB */) 
		SELECT 
			a.partner_id,
			a.date_id,
			SUM(b.count_storage) aggr_storage,
			SUM(b.count_bandwidth) aggr_bandwidth,
            SUM(b.count_streaming) aggr_streaming
		FROM dwh_aggr_partner a , dwh_aggr_partner b 
		WHERE 
			a.partner_id=b.partner_id
			AND a.date_id=DATE(''',date_val,''')*1
			AND a.date_id>=b.date_id
		GROUP BY
			a.date_id,
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
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
    		count_users)
    	SELECT  
    		partner_id,ku.created_date_id,
    		COUNT(1)
    	FROM kalturadw.dwh_dim_kusers  ku
    	WHERE 
    		ku.created_date_id=DATE(''',date_val,''')*1
   		GROUP BY partner_id,ku.created_date_id
    	ON DUPLICATE KEY UPDATE
    		count_users=VALUES(count_users) ;
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	 
	SET @s = CONCAT('
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
    		count_widgets)
    	SELECT  
    		partner_id,wd.created_date_id,
    		COUNT(1)
    	FROM kalturadw.dwh_dim_widget  wd
    	WHERE 
    		wd.created_date_id=DATE(''',date_val,''')*1
   		GROUP BY partner_id,wd.created_date_id
    	ON DUPLICATE KEY UPDATE
    		count_widgets=VALUES(count_widgets) ;
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	
	
END$$

DELIMITER ;

DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_aggr_events_widget_20110325`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `daily_procedure_dwh_aggr_events_widget`(date_val DATE,aggr_name VARCHAR(100))
BEGIN
	DECLARE aggr_table VARCHAR(100);
	DECLARE aggr_id_field VARCHAR(100);
	DECLARE aggr_id_field_str VARCHAR(100);


	SET aggr_table = kalturadw.resolve_aggr_name(aggr_name,'aggr_table');
	SET aggr_id_field = kalturadw.resolve_aggr_name(aggr_name,'aggr_id_field');
	
	IF ( aggr_id_field <> "" ) THEN
		SET aggr_id_field_str = CONCAT (',',aggr_id_field);
	ELSE
		SET aggr_id_field_str = "";
	END IF;

	 
	SET @s = CONCAT('
    	INSERT INTO kalturadw.',aggr_table,'
    		(partner_id, 
    		date_id, 
			widget_id,
     		count_widget_loads)
    	SELECT  
    		partner_id,event_date_id,widget_id,
    		SUM(IF(event_type_id=1,1,NULL)) count_widget_loads
		FROM kalturadw.dwh_fact_events  ev
		WHERE event_type_id IN (1) /*event types %*/
			AND event_date_id = DATE(''',date_val,''')*1
		GROUP BY partner_id,DATE(event_time)*1',aggr_id_field_str,'
    	ON DUPLICATE KEY UPDATE
    		count_widget_loads=VALUES(count_widget_loads);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_aggr_partner_daily_usage_20110325`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `daily_procedure_dwh_aggr_partner_daily_usage`(date_val DATE)
BEGIN
	
	SET @s = CONCAT('
    	INSERT INTO kalturadw.dwh_aggr_partner_daily_usage
    		(partner_id, 
    		date_id, 
    		sum_storage_mb   , /* MB */
    		sum_bandwidth_mb ,  /* MB */
            sum_streaming_mb   /* MB */
    		)
		SELECT 
			a.partner_id,
			a.date_id,
			a.aggr_storage,
			floor(a.aggr_bandwidth/1024),
            floor(a.aggr_streaming/1024)
		FROM dwh_aggr_partner a 
		WHERE 
			a.date_id=DATE(''',date_val,''')*1
		ON DUPLICATE KEY UPDATE
			sum_storage_mb=VALUES(sum_storage_mb),
			sum_bandwidth_mb=VALUES(sum_bandwidth_mb),
            sum_streaming_mb=VALUES(sum_streaming_mb);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	


	
	
	SET @prev_date_id=kalturadw.calc_prev_date_id(DATE(date_val)*1);
	SET @s = CONCAT('
        INSERT IGNORE INTO dwh_aggr_partner_daily_usage
        (partner_id,date_id,sum_storage_mb)
        SELECT 
        	l_aggr_p_d.partner_id,
        	DATE(''',date_val,''')*1,
        	l_aggr_p_d.sum_storage_mb
        FROM 
        	dwh_aggr_partner_daily_usage l_aggr_p_d  
        WHERE
        	l_aggr_p_d.sum_storage_mb IS NOT NULL AND 
        	l_aggr_p_d.date_id=',@prev_date_id,' AND 
        	l_aggr_p_d.partner_id NOT IN 
        	(
        		SELECT r_aggr_p_d.partner_id
        		FROM dwh_aggr_partner_daily_usage r_aggr_p_d 
        		WHERE r_aggr_p_d.date_id=DATE(''',date_val,''')*1
        	);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	
	


	
	SET @s = CONCAT('
    	INSERT INTO kalturadw.dwh_aggr_partner_daily_usage
    		(partner_id, 
    		date_id, 
    		sum_monthly_bandwidth_mb   /* MB */
    		)
		SELECT 
			a.partner_id,
			a.date_id,
			FLOOR(SUM(b.count_bandwidth)/1024) sum_monthly_bandwidth_mb
		FROM dwh_aggr_partner a ,dwh_aggr_partner b
		WHERE 
			a.partner_id=b.partner_id AND
			a.date_id=DATE(''',date_val,''')*1 AND
			a.date_id>=b.date_id AND
			kalturadw.calc_month_id(b.date_id)=kalturadw.calc_month_id(a.date_id)
		GROUP BY 
			b.partner_id	
		/* added to fix a bug in MySQL < 5.1.39 for insert .. select out of same (empty) partition */
		UNION  SELECT -1,DATE(''',date_val,''')*1,-1
		ON DUPLICATE KEY UPDATE
			sum_monthly_bandwidth_mb=VALUES(sum_monthly_bandwidth_mb);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	


	
	SET @s = CONCAT('
   		INSERT INTO kalturadw.dwh_aggr_partner_daily_usage
    		(partner_id, 
    		date_id, 
    		calculated_monthly_storage_mb   /* MB */
    		)
		SELECT 
			du.partner_id partner_id,
			DATE(''',date_val,''')*1,
			SUM(du.sum_storage_mb)/DAY(LAST_DAY(DATE(du.date_id))) calc_monthly_storage_mb
		FROM 
			dwh_aggr_partner_daily_usage du
		WHERE
			kalturadw.calc_month_id(du.date_id) = kalturadw.calc_month_id(DATE(''',date_val,''')*1)
			AND du.date_id<=DATE(''',date_val,''')*1
		GROUP BY 
			du.partner_id
		/* added to fix a bug in MySQL < 5.1.39 for insert .. select out of same (empty) partition */
		UNION  SELECT -1,DATE(''',date_val,''')*1,-1
		ON DUPLICATE KEY UPDATE
			calculated_monthly_storage_mb=VALUES(calculated_monthly_storage_mb);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	

	
	SET @s = CONCAT('
   		INSERT INTO kalturadw.dwh_aggr_partner_daily_usage
    		(partner_id, 
    		date_id, 
    		calculated_storage_mb   /* MB */
    		)
		SELECT 
			du.partner_id partner_id,
			DATE(''',date_val,''')*1,
			SUM(du.sum_storage_mb)/DAY(LAST_DAY(DATE(du.date_id))) calc_storage_mb
		FROM 
			dwh_aggr_partner_daily_usage du
		WHERE
			du.date_id<=DATE(''',date_val,''')*1
		GROUP BY 
			du.partner_id
		/* added to fix a bug in MySQL < 5.1.39 for insert .. select out of same (empty) partition */
		UNION  SELECT -1,DATE(''',date_val,''')*1,-1
		ON DUPLICATE KEY UPDATE
			calculated_storage_mb=VALUES(calculated_storage_mb);
    	');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	


END$$

DELIMITER ;

DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_aggr_partner_daily_usage_loop_20110325`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `daily_procedure_dwh_aggr_partner_daily_usage_loop`(from_date DATE,to_date DATE)
BEGIN
DECLARE _count INT DEFAULT 0;

DECLARE _current_date DATE;

    increment: LOOP
		SET _current_date = ADDDATE(from_date, INTERVAL _count DAY); 
		IF _current_date > to_date THEN
			LEAVE increment;
		END IF;
		SET _count = _count + 1;

		CALL kalturadw.daily_procedure_dwh_aggr_partner_daily_usage(_current_date);
    END LOOP increment;
    SELECT _count;

END$$

DELIMITER ;