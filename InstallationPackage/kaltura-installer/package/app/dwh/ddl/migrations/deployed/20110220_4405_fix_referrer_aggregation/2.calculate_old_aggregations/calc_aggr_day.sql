DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day`(p_date_val DATE,p_aggr_name VARCHAR(100))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	DECLARE v_hourly_aggr_table VARCHAR(100);
	DECLARE v_aggr_id_field VARCHAR(100);
	DECLARE v_aggr_id_field_str VARCHAR(100);
	DECLARE v_aggr_join_stmt VARCHAR(200);
	DECLARE extra VARCHAR(100);
	
	SELECT aggr_table,hourly_aggr_table, aggr_id_field, aggr_join_stmt
	INTO  v_aggr_table,v_hourly_aggr_table, v_aggr_id_field, v_aggr_join_stmt
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = p_aggr_name;
	
	IF ( v_aggr_id_field <> "" ) THEN
		SET v_aggr_id_field_str = CONCAT (',',v_aggr_id_field);
	ELSE
		SET v_aggr_id_field_str = "";
	END IF;
	

	SET @s = CONCAT('UPDATE aggr_managment SET start_time = NOW()
	WHERE aggr_name = ''',p_aggr_name,''' AND aggr_day = ''',p_date_val,'''');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	IF ( v_aggr_table <> "" ) THEN
    		SET @s = CONCAT('INSERT INTO ',v_aggr_table,'
			(partner_id
			,date_id 
			',v_aggr_id_field_str,' 
			,sum_time_viewed 
			,count_time_viewed 
			,count_plays 
			,count_loads 
			,count_plays_25 
			,count_plays_50 
			,count_plays_75 
			,count_plays_100 
			,count_edit
			,count_viral 
			,count_download 
			,count_report
			,count_buf_start
			,count_buf_end
		,count_open_full_screen
		,count_close_full_screen
		,count_replay
		,count_seek
		,count_open_upload
		,count_save_publish 
		,count_close_editor
			,count_pre_bumper_played
		,count_post_bumper_played
		,count_bumper_clicked
		,count_preroll_started
		,count_midroll_started
		,count_postroll_started
		,count_overlay_started
		,count_preroll_clicked
		,count_midroll_clicked
		,count_postroll_clicked
		,count_overlay_clicked
		,count_preroll_25
		,count_preroll_50
		,count_preroll_75
		,count_midroll_25
		,count_midroll_50
		,count_midroll_75
		,count_postroll_25
		,count_postroll_50
		,count_postroll_75
			) 
		SELECT  partner_id,date_id',v_aggr_id_field_str,',
		SUM(time_viewed) sum_time_viewed,
		COUNT(time_viewed) count_time_viewed,
		SUM(count_plays) count_plays,
		SUM(count_loads) count_loads,
		SUM(count_plays_25) count_plays_25,
		SUM(count_plays_50) count_plays_50,
		SUM(count_plays_75) count_plays_75,
		SUM(count_plays_100) count_plays_100,
		SUM(count_edit) count_edit,
		SUM(count_viral) count_viral,
		SUM(count_download) count_download,
		SUM(count_report) count_report,
		SUM(count_buf_start) count_buf_start,
		SUM(count_buf_end) count_buf_end,
	    SUM(count_open_full_screen) count_open_full_screen,
	    SUM(count_close_full_screen) count_close_full_screen,
	    SUM(count_replay) count_replay,
	    SUM(count_seek) count_seek,
	    SUM(count_open_upload) count_open_upload,
	    SUM(count_save_publish) count_save_publish,
	    SUM(count_close_editor) count_close_editor,
	    SUM(count_pre_bumper_played) count_pre_bumper_played,
	    SUM(count_post_bumper_played) count_post_bumper_played,
	    SUM(count_bumper_clicked) count_bumper_clicked,
	    SUM(count_preroll_started) count_preroll_started,
	    SUM(count_midroll_started) count_midroll_started,
	    SUM(count_postroll_started) count_postroll_started,
	    SUM(count_overlay_started) count_overlay_started,
	    SUM(count_preroll_clicked) count_preroll_clicked,
	    SUM(count_midroll_clicked) count_midroll_clicked,
	    SUM(count_postroll_clicked) count_postroll_clicked,
	    SUM(count_overlay_clicked) count_overlay_clicked,
	    SUM(count_preroll_25) count_preroll_25,
	    SUM(count_preroll_50) count_preroll_50,
	    SUM(count_preroll_75) count_preroll_75,
	    SUM(count_midroll_25) count_midroll_25,
	    SUM(count_midroll_50) count_midroll_50,
	    SUM(count_midroll_75) count_midroll_75,
	    SUM(count_postroll_25) count_postroll_25,
	    SUM(count_postroll_50) count_postroll_50,
	    SUM(count_postroll_75) count_postroll_75
		FROM (
			SELECT ev.partner_id,DATE(ev.event_time)*1 date_id',v_aggr_id_field_str,',ev.session_id,
				MAX(IF(ev.event_type_id IN(4,5,6,7),current_point,NULL))/60000  time_viewed,
				COUNT(IF(ev.event_type_id = 2, 1,NULL)) count_loads,
				COUNT(IF(ev.event_type_id = 3, 1,NULL)) count_plays,
				COUNT(IF(ev.event_type_id = 4, 1,NULL)) count_plays_25,
				COUNT(IF(ev.event_type_id = 5, 1,NULL)) count_plays_50,
				COUNT(IF(ev.event_type_id = 6, 1,NULL)) count_plays_75,
				COUNT(IF(ev.event_type_id = 7, 1,NULL)) count_plays_100,
				COUNT(IF(ev.event_type_id = 8, 1,NULL)) count_edit ,
				COUNT(IF(ev.event_type_id = 9, 1,NULL)) count_viral ,
				COUNT(IF(ev.event_type_id = 10, 1,NULL)) count_download ,
				COUNT(IF(ev.event_type_id = 11, 1,NULL)) count_report,
				COUNT(IF(ev.event_type_id = 12, 1,NULL)) count_buf_start ,
				COUNT(IF(ev.event_type_id = 13, 1,NULL)) count_buf_end	,            
		    COUNT(IF(ev.event_type_id = 14, 1,NULL)) count_open_full_screen	,            
		    COUNT(IF(ev.event_type_id = 15, 1,NULL)) count_close_full_screen,            
		    COUNT(IF(ev.event_type_id = 16, 1,NULL)) count_replay	,            
		    COUNT(IF(ev.event_type_id = 17, 1,NULL)) count_seek	,            
		    COUNT(IF(ev.event_type_id = 18, 1,NULL)) count_open_upload	,            
		    COUNT(IF(ev.event_type_id = 19, 1,NULL)) count_save_publish	,            
		    COUNT(IF(ev.event_type_id = 20, 1,NULL)) count_close_editor	,            
				COUNT(IF(ev.event_type_id = 21, 1,NULL)) count_pre_bumper_played , 
				COUNT(IF(ev.event_type_id = 22, 1,NULL)) count_post_bumper_played	, 
				COUNT(IF(ev.event_type_id = 23, 1,NULL)) count_bumper_clicked 	, 
				COUNT(IF(ev.event_type_id = 24, 1,NULL)) count_preroll_started 	, 
				COUNT(IF(ev.event_type_id = 25, 1,NULL)) count_midroll_started 	, 
				COUNT(IF(ev.event_type_id = 26, 1,NULL)) count_postroll_started, 
				COUNT(IF(ev.event_type_id = 27, 1,NULL)) count_overlay_started, 
				COUNT(IF(ev.event_type_id = 28, 1,NULL)) count_preroll_clicked,
		    COUNT(IF(ev.event_type_id = 29, 1,NULL)) count_midroll_clicked , 
				COUNT(IF(ev.event_type_id = 30, 1,NULL)) count_postroll_clicked	, 
				COUNT(IF(ev.event_type_id = 31, 1,NULL)) count_overlay_clicked 	, 
				COUNT(IF(ev.event_type_id = 32, 1,NULL)) count_preroll_25 	, 
				COUNT(IF(ev.event_type_id = 33, 1,NULL)) count_preroll_50 	, 
				COUNT(IF(ev.event_type_id = 34, 1,NULL)) count_preroll_75, 
				COUNT(IF(ev.event_type_id = 35, 1,NULL)) count_midroll_25, 
				COUNT(IF(ev.event_type_id = 36, 1,NULL)) count_midroll_50	,
				COUNT(IF(ev.event_type_id = 37, 1,NULL)) count_midroll_75	, 
				COUNT(IF(ev.event_type_id = 38, 1,NULL)) count_postroll_25 	, 
				COUNT(IF(ev.event_type_id = 39, 1,NULL)) count_postroll_50 	, 
				COUNT(IF(ev.event_type_id = 40, 1,NULL)) count_postroll_75 	
			FROM dwh_fact_events as ev ',v_aggr_join_stmt,' 
			WHERE ev.event_type_id BETWEEN 2 AND 40 
				AND ev.event_date_id  = DATE(''',p_date_val,''')*1
		    AND ev.event_time BETWEEN DATE(''',p_date_val,''') AND DATE(''',p_date_val,''') + INTERVAL 1 DAY
				AND ev.entry_media_type_id IN (1,5,6)  /* allow only video & audio & mix */
			GROUP BY ev.partner_id,DATE(ev.event_time)*1',v_aggr_id_field_str,',ev.session_id
		) AS a
		GROUP BY partner_id,date_id',v_aggr_id_field_str,';');
		
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	
		SET extra = CONCAT('daily_procedure_',v_aggr_table);
		IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_NAME=extra) THEN
			
			SET @ss = CONCAT('CALL ',extra,'(''', p_date_val,''',''',p_aggr_name,''');'); 
			PREPARE stmt1 FROM  @ss;
			EXECUTE stmt1;
			DEALLOCATE PREPARE stmt1;
		END IF ;
	END IF;
	
	
	IF ( v_hourly_aggr_table <> "" ) THEN
		SET @s = CONCAT('INSERT INTO ',v_hourly_aggr_table,'
			(partner_id
			,date_id
		,hour_id
			',v_aggr_id_field_str,' 
			,sum_time_viewed 
			,count_time_viewed 
			,count_plays 
			,count_loads 
			,count_plays_25 
			,count_plays_50 
			,count_plays_75 
			,count_plays_100 
			,count_edit
			,count_viral 
			,count_download 
			,count_report
			,count_buf_start
			,count_buf_end
		,count_open_full_screen
		,count_close_full_screen
		,count_replay
		,count_seek
		,count_open_upload
		,count_save_publish 
		,count_close_editor
			,count_pre_bumper_played
		,count_post_bumper_played
		,count_bumper_clicked
		,count_preroll_started
		,count_midroll_started
		,count_postroll_started
		,count_overlay_started
		,count_preroll_clicked
		,count_midroll_clicked
		,count_postroll_clicked
		,count_overlay_clicked
		,count_preroll_25
		,count_preroll_50
		,count_preroll_75
		,count_midroll_25
		,count_midroll_50
		,count_midroll_75
		,count_postroll_25
		,count_postroll_50
		,count_postroll_75
			) 
		SELECT  partner_id,date_id,hour_id',v_aggr_id_field_str,',
		SUM(time_viewed) sum_time_viewed,
		COUNT(time_viewed) count_time_viewed,
		SUM(count_plays) count_plays,
		SUM(count_loads) count_loads,
		SUM(count_plays_25) count_plays_25,
		SUM(count_plays_50) count_plays_50,
		SUM(count_plays_75) count_plays_75,
		SUM(count_plays_100) count_plays_100,
		SUM(count_edit) count_edit,
		SUM(count_viral) count_viral,
		SUM(count_download) count_download,
		SUM(count_report) count_report,
		SUM(count_buf_start) count_buf_start,
		SUM(count_buf_end) count_buf_end,
	    SUM(count_open_full_screen) count_open_full_screen,
	    SUM(count_close_full_screen) count_close_full_screen,
	    SUM(count_replay) count_replay,
	    SUM(count_seek) count_seek,
	    SUM(count_open_upload) count_open_upload,
	    SUM(count_save_publish) count_save_publish,
	    SUM(count_close_editor) count_close_editor,
	    SUM(count_pre_bumper_played) count_pre_bumper_played,
	    SUM(count_post_bumper_played) count_post_bumper_played,
	    SUM(count_bumper_clicked) count_bumper_clicked,
	    SUM(count_preroll_started) count_preroll_started,
	    SUM(count_midroll_started) count_midroll_started,
	    SUM(count_postroll_started) count_postroll_started,
	    SUM(count_overlay_started) count_overlay_started,
	    SUM(count_preroll_clicked) count_preroll_clicked,
	    SUM(count_midroll_clicked) count_midroll_clicked,
	    SUM(count_postroll_clicked) count_postroll_clicked,
	    SUM(count_overlay_clicked) count_overlay_clicked,
	    SUM(count_preroll_25) count_preroll_25,
	    SUM(count_preroll_50) count_preroll_50,
	    SUM(count_preroll_75) count_preroll_75,
	    SUM(count_midroll_25) count_midroll_25,
	    SUM(count_midroll_50) count_midroll_50,
	    SUM(count_midroll_75) count_midroll_75,
	    SUM(count_postroll_25) count_postroll_25,
	    SUM(count_postroll_50) count_postroll_50,
	    SUM(count_postroll_75) count_postroll_75
		FROM (
			SELECT ev.partner_id,MIN(DATE(ev.event_time)*1) date_id, MIN(HOUR(ev.event_time)) hour_id',v_aggr_id_field_str,',ev.session_id,
				MAX(IF(ev.event_type_id IN(4,5,6,7),current_point,NULL))/60000  time_viewed,
				COUNT(IF(ev.event_type_id = 2, 1,NULL)) count_loads,
				COUNT(IF(ev.event_type_id = 3, 1,NULL)) count_plays,
				COUNT(IF(ev.event_type_id = 4, 1,NULL)) count_plays_25,
				COUNT(IF(ev.event_type_id = 5, 1,NULL)) count_plays_50,
				COUNT(IF(ev.event_type_id = 6, 1,NULL)) count_plays_75,
				COUNT(IF(ev.event_type_id = 7, 1,NULL)) count_plays_100,
				COUNT(IF(ev.event_type_id = 8, 1,NULL)) count_edit ,
				COUNT(IF(ev.event_type_id = 9, 1,NULL)) count_viral ,
				COUNT(IF(ev.event_type_id = 10, 1,NULL)) count_download ,
				COUNT(IF(ev.event_type_id = 11, 1,NULL)) count_report,
				COUNT(IF(ev.event_type_id = 12, 1,NULL)) count_buf_start ,
				COUNT(IF(ev.event_type_id = 13, 1,NULL)) count_buf_end	,            
		    COUNT(IF(ev.event_type_id = 14, 1,NULL)) count_open_full_screen	,            
		    COUNT(IF(ev.event_type_id = 15, 1,NULL)) count_close_full_screen,            
		    COUNT(IF(ev.event_type_id = 16, 1,NULL)) count_replay	,            
		    COUNT(IF(ev.event_type_id = 17, 1,NULL)) count_seek	,            
		    COUNT(IF(ev.event_type_id = 18, 1,NULL)) count_open_upload	,            
		    COUNT(IF(ev.event_type_id = 19, 1,NULL)) count_save_publish	,            
		    COUNT(IF(ev.event_type_id = 20, 1,NULL)) count_close_editor	,            
				COUNT(IF(ev.event_type_id = 21, 1,NULL)) count_pre_bumper_played , 
				COUNT(IF(ev.event_type_id = 22, 1,NULL)) count_post_bumper_played	, 
				COUNT(IF(ev.event_type_id = 23, 1,NULL)) count_bumper_clicked 	, 
				COUNT(IF(ev.event_type_id = 24, 1,NULL)) count_preroll_started 	, 
				COUNT(IF(ev.event_type_id = 25, 1,NULL)) count_midroll_started 	, 
				COUNT(IF(ev.event_type_id = 26, 1,NULL)) count_postroll_started, 
				COUNT(IF(ev.event_type_id = 27, 1,NULL)) count_overlay_started, 
				COUNT(IF(ev.event_type_id = 28, 1,NULL)) count_preroll_clicked,
		    COUNT(IF(ev.event_type_id = 29, 1,NULL)) count_midroll_clicked , 
				COUNT(IF(ev.event_type_id = 30, 1,NULL)) count_postroll_clicked	, 
				COUNT(IF(ev.event_type_id = 31, 1,NULL)) count_overlay_clicked 	, 
				COUNT(IF(ev.event_type_id = 32, 1,NULL)) count_preroll_25 	, 
				COUNT(IF(ev.event_type_id = 33, 1,NULL)) count_preroll_50 	, 
				COUNT(IF(ev.event_type_id = 34, 1,NULL)) count_preroll_75, 
				COUNT(IF(ev.event_type_id = 35, 1,NULL)) count_midroll_25, 
				COUNT(IF(ev.event_type_id = 36, 1,NULL)) count_midroll_50	,
				COUNT(IF(ev.event_type_id = 37, 1,NULL)) count_midroll_75	, 
				COUNT(IF(ev.event_type_id = 38, 1,NULL)) count_postroll_25 	, 
				COUNT(IF(ev.event_type_id = 39, 1,NULL)) count_postroll_50 	, 
				COUNT(IF(ev.event_type_id = 40, 1,NULL)) count_postroll_75 	
			FROM dwh_fact_events as ev ',v_aggr_join_stmt,' 
			WHERE ev.event_type_id BETWEEN 2 AND 40 
				AND ev.event_date_id  = DATE(''',p_date_val,''')*1
		    AND ev.event_time BETWEEN DATE(''',p_date_val,''') AND DATE(''',p_date_val,''') + INTERVAL 1 DAY
				AND ev.entry_media_type_id IN (1,5,6)  /* allow only video & audio & mix */
			GROUP BY ev.partner_id',v_aggr_id_field_str,',ev.session_id
		) AS a
		GROUP BY partner_id,date_id, hour_id',v_aggr_id_field_str,';');
		
		PREPARE stmt FROM  @s;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET extra = CONCAT('daily_procedure_',v_hourly_aggr_table);
		IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_NAME=extra) THEN
			
			SET @ss = CONCAT('CALL ',extra,'(''', p_date_val,''',''',p_aggr_name,''');'); 
			PREPARE stmt1 FROM  @ss;
			EXECUTE stmt1;
			DEALLOCATE PREPARE stmt1;
		END IF ;
	END IF;
    
  
	SET @s = CONCAT('UPDATE aggr_managment SET is_calculated = 1,end_time = NOW()
	WHERE aggr_name = ''',p_aggr_name,''' AND aggr_day = ''',p_date_val,'''');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
    END$$

DELIMITER ;