
DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `add_partitions`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `add_partitions`()
BEGIN
  CALL add_partition_for_fact_table('dwh_fact_events');
  CALL add_partition_for_fact_table('dwh_fact_fms_session_events');
  CALL add_partition_for_fact_table('dwh_fact_fms_sessions');

	CALL add_partition_for_table('dwh_aggr_events_entry');
	CALL add_partition_for_table('dwh_aggr_events_domain');
	CALL add_partition_for_table('dwh_aggr_events_country');
	CALL add_partition_for_table('dwh_aggr_events_widget');
	CALL add_partition_for_table('dwh_aggr_events_uid');		
	CALL add_partition_for_table('dwh_aggr_partner');		

	CALL add_partition_for_table('dwh_hourly_events_entry');
	CALL add_partition_for_table('dwh_hourly_events_domain');
	CALL add_partition_for_table('dwh_hourly_events_country');
	CALL add_partition_for_table('dwh_hourly_events_widget');
	CALL add_partition_for_table('dwh_hourly_events_uid');		
	CALL add_partition_for_table('dwh_hourly_partner');		

	CALL add_partition_for_table('dwh_aggr_partner_daily_usage');
END$$

DELIMITER ;

USE `kalturadw_ds`;

/* Add hourly aggregation table name */

ALTER TABLE `aggr_name_resolver`
ADD COLUMN `hourly_aggr_table` VARCHAR(100) DEFAULT NULL;

UPDATE `aggr_name_resolver`
SET `hourly_aggr_table` = CONCAT(SUBSTR(aggr_table,1,4),'hourly',SUBSTR(aggr_table,9));

USE `kalturadw`;
 /* create tables */
CREATE TABLE kalturadw.`dwh_hourly_events_country` (
  `partner_id` INT DEFAULT NULL,
  `date_id` INT DEFAULT NULL,
  `hour_id` INT DEFAULT NULL,
  `country_id` INT DEFAULT NULL,
  `location_id` INT DEFAULT NULL,
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT DEFAULT NULL,
  `count_plays` INT DEFAULT NULL,
  `count_loads` INT DEFAULT NULL,
  `count_plays_25` INT DEFAULT NULL,
  `count_plays_50` INT DEFAULT NULL,
  `count_plays_75` INT DEFAULT NULL,
  `count_plays_100` INT DEFAULT NULL,
  `count_edit` INT DEFAULT NULL,
  `count_viral` INT DEFAULT NULL,
  `count_download` INT DEFAULT NULL,
  `count_report` INT DEFAULT NULL,
  `count_buf_start` INT DEFAULT NULL,
  `count_buf_end` INT DEFAULT NULL,
  `count_open_full_screen` INT DEFAULT NULL,
  `count_close_full_screen` INT DEFAULT NULL,
  `count_replay` INT DEFAULT NULL,
  `count_seek` INT DEFAULT NULL,
  `count_open_upload` INT DEFAULT NULL,
  `count_save_publish` INT DEFAULT NULL,
  `count_close_editor` INT DEFAULT NULL,    
  `count_pre_bumper_played` INT DEFAULT NULL,
  `count_post_bumper_played` INT DEFAULT NULL,
  `count_bumper_clicked` INT DEFAULT NULL,
  `count_preroll_started` INT DEFAULT NULL,
  `count_midroll_started` INT DEFAULT NULL,
  `count_postroll_started` INT DEFAULT NULL,
  `count_overlay_started` INT DEFAULT NULL,
  `count_preroll_clicked` INT DEFAULT NULL,
  `count_midroll_clicked` INT DEFAULT NULL,
  `count_postroll_clicked` INT DEFAULT NULL,
  `count_overlay_clicked` INT DEFAULT NULL,
  `count_preroll_25` INT DEFAULT NULL,
  `count_preroll_50` INT DEFAULT NULL,
  `count_preroll_75` INT DEFAULT NULL,
  `count_midroll_25` INT DEFAULT NULL,
  `count_midroll_50` INT DEFAULT NULL,
  `count_midroll_75` INT DEFAULT NULL,
  `count_postroll_25` INT DEFAULT NULL,
  `count_postroll_50` INT DEFAULT NULL,
  `count_postroll_75` INT DEFAULT NULL,
  PRIMARY KEY `partner_id` (`partner_id`,`date_id`,`hour_id`,`country_id`,location_id),
  KEY `country_id` (`country_id`,`partner_id`,`date_id`,`hour_id`,location_id),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MYISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MYISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MYISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MYISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MYISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MYISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MYISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MYISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MYISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MYISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MYISAM);
 


CREATE TABLE kalturadw.`dwh_hourly_events_domain` (
  `partner_id` INT DEFAULT NULL,
  `date_id` INT DEFAULT NULL,
  `hour_id` INT DEFAULT NULL,
  `domain_id` INT DEFAULT NULL,
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT DEFAULT NULL,
  `count_plays` INT DEFAULT NULL,
  `count_loads` INT DEFAULT NULL,
  `count_plays_25` INT DEFAULT NULL,
  `count_plays_50` INT DEFAULT NULL,
  `count_plays_75` INT DEFAULT NULL,
  `count_plays_100` INT DEFAULT NULL,
  `count_edit` INT DEFAULT NULL,
  `count_viral` INT DEFAULT NULL,
  `count_download` INT DEFAULT NULL,
  `count_report` INT DEFAULT NULL,
  `count_buf_start` INT DEFAULT NULL,
  `count_buf_end` INT DEFAULT NULL,
  `count_open_full_screen` INT DEFAULT NULL,
  `count_close_full_screen` INT DEFAULT NULL,
  `count_replay` INT DEFAULT NULL,
  `count_seek` INT DEFAULT NULL,
  `count_open_upload` INT DEFAULT NULL,
  `count_save_publish` INT DEFAULT NULL,
  `count_close_editor` INT DEFAULT NULL,    
  `count_pre_bumper_played` INT DEFAULT NULL,
  `count_post_bumper_played` INT DEFAULT NULL,
  `count_bumper_clicked` INT DEFAULT NULL,
  `count_preroll_started` INT DEFAULT NULL,
  `count_midroll_started` INT DEFAULT NULL,
  `count_postroll_started` INT DEFAULT NULL,
  `count_overlay_started` INT DEFAULT NULL,
  `count_preroll_clicked` INT DEFAULT NULL,
  `count_midroll_clicked` INT DEFAULT NULL,
  `count_postroll_clicked` INT DEFAULT NULL,
  `count_overlay_clicked` INT DEFAULT NULL,
  `count_preroll_25` INT DEFAULT NULL,
  `count_preroll_50` INT DEFAULT NULL,
  `count_preroll_75` INT DEFAULT NULL,
  `count_midroll_25` INT DEFAULT NULL,
  `count_midroll_50` INT DEFAULT NULL,
  `count_midroll_75` INT DEFAULT NULL,
  `count_postroll_25` INT DEFAULT NULL,
  `count_postroll_50` INT DEFAULT NULL,
  `count_postroll_75` INT DEFAULT NULL,
  PRIMARY KEY `partner_id` (`partner_id`,`date_id`,`hour_id`,`domain_id`),
  KEY `domain_id` (`domain_id`,`partner_id`,`date_id`,`hour_id`),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MYISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MYISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MYISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MYISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MYISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MYISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MYISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MYISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MYISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MYISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MYISAM);
 

CREATE TABLE kalturadw.`dwh_hourly_events_entry` (
  `partner_id` INT DEFAULT NULL,
  `date_id` INT DEFAULT NULL,
  `hour_id` INT DEFAULT NULL,
  `entry_id` VARCHAR(20) DEFAULT NULL,
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT DEFAULT NULL,
  `count_plays` INT DEFAULT NULL,
  `count_loads` INT DEFAULT NULL,
  `count_plays_25` INT DEFAULT NULL,
  `count_plays_50` INT DEFAULT NULL,
  `count_plays_75` INT DEFAULT NULL,
  `count_plays_100` INT DEFAULT NULL,
  `count_edit` INT DEFAULT NULL,
  `count_viral` INT DEFAULT NULL,
  `count_download` INT DEFAULT NULL,
  `count_report` INT DEFAULT NULL,
  `count_buf_start` INT DEFAULT NULL,
  `count_buf_end` INT DEFAULT NULL,
  `count_open_full_screen` INT DEFAULT NULL,
  `count_close_full_screen` INT DEFAULT NULL,
  `count_replay` INT DEFAULT NULL,
  `count_seek` INT DEFAULT NULL,
  `count_open_upload` INT DEFAULT NULL,
  `count_save_publish` INT DEFAULT NULL,
  `count_close_editor` INT DEFAULT NULL,    
  `count_pre_bumper_played` INT DEFAULT NULL,
  `count_post_bumper_played` INT DEFAULT NULL,
  `count_bumper_clicked` INT DEFAULT NULL,
  `count_preroll_started` INT DEFAULT NULL,
  `count_midroll_started` INT DEFAULT NULL,
  `count_postroll_started` INT DEFAULT NULL,
  `count_overlay_started` INT DEFAULT NULL,
  `count_preroll_clicked` INT DEFAULT NULL,
  `count_midroll_clicked` INT DEFAULT NULL,
  `count_postroll_clicked` INT DEFAULT NULL,
  `count_overlay_clicked` INT DEFAULT NULL,
  `count_preroll_25` INT DEFAULT NULL,
  `count_preroll_50` INT DEFAULT NULL,
  `count_preroll_75` INT DEFAULT NULL,
  `count_midroll_25` INT DEFAULT NULL,
  `count_midroll_50` INT DEFAULT NULL,
  `count_midroll_75` INT DEFAULT NULL,
  `count_postroll_25` INT DEFAULT NULL,
  `count_postroll_50` INT DEFAULT NULL,
  `count_postroll_75` INT DEFAULT NULL,
  PRIMARY KEY `partner_id` (`partner_id`,`date_id`,`hour_id`,`entry_id`),
  KEY `entry_id` (`entry_id`,`partner_id`,`date_id`,`hour_id`),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MYISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MYISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MYISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MYISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MYISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MYISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MYISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MYISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MYISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MYISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MYISAM);
 

    
CREATE TABLE `dwh_hourly_events_uid` (
  `partner_id` INT(11) NOT NULL DEFAULT '0',
  `date_id` INT(11) NOT NULL DEFAULT '0',
  `hour_id` INT(11) NOT NULL DEFAULT '0',
  `kuser_id` VARCHAR(64) NOT NULL DEFAULT '0',
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT(11) DEFAULT NULL,
  `count_plays` INT(11) DEFAULT NULL,
  `count_loads` INT(11) DEFAULT NULL,
  `count_plays_25` INT(11) DEFAULT NULL,
  `count_plays_50` INT(11) DEFAULT NULL,
  `count_plays_75` INT(11) DEFAULT NULL,
  `count_plays_100` INT(11) DEFAULT NULL,
  `count_edit` INT(11) DEFAULT NULL,
  `count_viral` INT(11) DEFAULT NULL,
  `count_download` INT(11) DEFAULT NULL,
  `count_report` INT(11) DEFAULT NULL,
  `count_buf_start` INT(11) DEFAULT NULL,
  `count_buf_end` INT(11) DEFAULT NULL,
  `count_open_full_screen` INT(11) DEFAULT NULL,
  `count_close_full_screen` INT(11) DEFAULT NULL,
  `count_replay` INT(11) DEFAULT NULL,
  `count_seek` INT(11) DEFAULT NULL,
  `count_open_upload` INT(11) DEFAULT NULL,
  `count_save_publish` INT(11) DEFAULT NULL,
  `count_close_editor` INT(11) DEFAULT NULL,
  `count_pre_bumper_played` INT(11) DEFAULT NULL,
  `count_post_bumper_played` INT(11) DEFAULT NULL,
  `count_bumper_clicked` INT(11) DEFAULT NULL,
  `count_preroll_started` INT(11) DEFAULT NULL,
  `count_midroll_started` INT(11) DEFAULT NULL,
  `count_postroll_started` INT(11) DEFAULT NULL,
  `count_overlay_started` INT(11) DEFAULT NULL,
  `count_preroll_clicked` INT(11) DEFAULT NULL,
  `count_midroll_clicked` INT(11) DEFAULT NULL,
  `count_postroll_clicked` INT(11) DEFAULT NULL,
  `count_overlay_clicked` INT(11) DEFAULT NULL,
  `count_preroll_25` INT(11) DEFAULT NULL,
  `count_preroll_50` INT(11) DEFAULT NULL,
  `count_preroll_75` INT(11) DEFAULT NULL,
  `count_midroll_25` INT(11) DEFAULT NULL,
  `count_midroll_50` INT(11) DEFAULT NULL,
  `count_midroll_75` INT(11) DEFAULT NULL,
  `count_postroll_25` INT(11) DEFAULT NULL,
  `count_postroll_50` INT(11) DEFAULT NULL,
  `count_postroll_75` INT(11) DEFAULT NULL,
  PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`kuser_id`),
  KEY `uid` (`kuser_id`,`partner_id`,`date_id`,`hour_id`),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MYISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MYISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MYISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MYISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MYISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MYISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MYISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MYISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MYISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MYISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MYISAM);



CREATE TABLE kalturadw.`dwh_hourly_events_widget` (
  `partner_id` INT DEFAULT NULL,
  `date_id` INT DEFAULT NULL,
  `hour_id`  INT DEFAULT NULL,
  `widget_id` VARCHAR(32) DEFAULT NULL,
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT DEFAULT NULL,
  `count_plays` INT DEFAULT NULL,
  `count_loads` INT DEFAULT NULL,
  `count_plays_25` INT DEFAULT NULL,
  `count_plays_50` INT DEFAULT NULL,
  `count_plays_75` INT DEFAULT NULL,
  `count_plays_100` INT DEFAULT NULL,
  `count_edit` INT DEFAULT NULL,
  `count_viral` INT DEFAULT NULL,
  `count_download` INT DEFAULT NULL,
  `count_report` INT DEFAULT NULL,
  `count_widget_loads` INT DEFAULT NULL,
  `count_buf_start` INT DEFAULT NULL,
  `count_buf_end` INT DEFAULT NULL,
  `count_open_full_screen` INT DEFAULT NULL,
  `count_close_full_screen` INT DEFAULT NULL,
  `count_replay` INT DEFAULT NULL,
  `count_seek` INT DEFAULT NULL,
  `count_open_upload` INT DEFAULT NULL,
  `count_save_publish` INT DEFAULT NULL,
  `count_close_editor` INT DEFAULT NULL,    
  `count_pre_bumper_played` INT DEFAULT NULL,
  `count_post_bumper_played` INT DEFAULT NULL,
  `count_bumper_clicked` INT DEFAULT NULL,
  `count_preroll_started` INT DEFAULT NULL,
  `count_midroll_started` INT DEFAULT NULL,
  `count_postroll_started` INT DEFAULT NULL,
  `count_overlay_started` INT DEFAULT NULL,
  `count_preroll_clicked` INT DEFAULT NULL,
  `count_midroll_clicked` INT DEFAULT NULL,
  `count_postroll_clicked` INT DEFAULT NULL,
  `count_overlay_clicked` INT DEFAULT NULL,
  `count_preroll_25` INT DEFAULT NULL,
  `count_preroll_50` INT DEFAULT NULL,
  `count_preroll_75` INT DEFAULT NULL,
  `count_midroll_25` INT DEFAULT NULL,
  `count_midroll_50` INT DEFAULT NULL,
  `count_midroll_75` INT DEFAULT NULL,
  `count_postroll_25` INT DEFAULT NULL,
  `count_postroll_50` INT DEFAULT NULL,
  `count_postroll_75` INT DEFAULT NULL,
  PRIMARY KEY `partner_id` (`partner_id`,`date_id`,`hour_id`,`widget_id`),
  KEY `widget_id` (`widget_id`,`partner_id`,`date_id`,`hour_id`),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MYISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MYISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MYISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MYISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MYISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MYISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MYISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MYISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MYISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MYISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MYISAM);
        


CREATE TABLE kalturadw.`dwh_hourly_partner` (
  `partner_id` INT DEFAULT NULL,
  `date_id` INT DEFAULT NULL,
  `hour_id` INT DEFAULT NULL,
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT DEFAULT NULL,
  `count_plays` INT DEFAULT NULL,
  `count_loads` INT DEFAULT NULL,
  `count_plays_25` INT DEFAULT NULL,
  `count_plays_50` INT DEFAULT NULL,
  `count_plays_75` INT DEFAULT NULL,
  `count_plays_100` INT DEFAULT NULL,
  `count_edit` INT DEFAULT NULL,
  `count_viral` INT DEFAULT NULL,
  `count_download` INT DEFAULT NULL,
  `count_report` INT DEFAULT NULL,
  `count_media`  INT DEFAULT NULL,
  `count_video`  INT DEFAULT NULL,
  `count_image`  INT DEFAULT NULL,
  `count_audio`  INT DEFAULT NULL,
  `count_mix`  INT DEFAULT NULL,
  `count_mix_non_empty`  INT DEFAULT NULL,
  `count_playlist`  INT DEFAULT NULL,
  `count_bandwidth`  BIGINT DEFAULT NULL,
  `count_storage`  BIGINT DEFAULT NULL,
  `count_users`  INT DEFAULT NULL,  
  `count_widgets`  INT DEFAULT NULL,
  `flag_active_site` TINYINT(4) DEFAULT '0',
  `flag_active_publisher` TINYINT(4) DEFAULT '0',
  `aggr_storage` BIGINT(20) DEFAULT NULL,
  `aggr_bandwidth` BIGINT(20) DEFAULT NULL,
  `count_buf_start` INT DEFAULT NULL,
  `count_buf_end` INT DEFAULT NULL,
  `count_open_full_screen` INT DEFAULT NULL,
  `count_close_full_screen` INT DEFAULT NULL,
  `count_replay` INT DEFAULT NULL,
  `count_seek` INT DEFAULT NULL,
  `count_open_upload` INT DEFAULT NULL,
  `count_save_publish` INT DEFAULT NULL,
  `count_close_editor` INT DEFAULT NULL,
  `count_pre_bumper_played` INT DEFAULT NULL,
  `count_post_bumper_played` INT DEFAULT NULL,
  `count_bumper_clicked` INT DEFAULT NULL,
  `count_preroll_started` INT DEFAULT NULL,
  `count_midroll_started` INT DEFAULT NULL,
  `count_postroll_started` INT DEFAULT NULL,
  `count_overlay_started` INT DEFAULT NULL,
  `count_preroll_clicked` INT DEFAULT NULL,
  `count_midroll_clicked` INT DEFAULT NULL,
  `count_postroll_clicked` INT DEFAULT NULL,
  `count_overlay_clicked` INT DEFAULT NULL,
  `count_preroll_25` INT DEFAULT NULL,
  `count_preroll_50` INT DEFAULT NULL,
  `count_preroll_75` INT DEFAULT NULL,
  `count_midroll_25` INT DEFAULT NULL,
  `count_midroll_50` INT DEFAULT NULL,
  `count_midroll_75` INT DEFAULT NULL,
  `count_postroll_25` INT DEFAULT NULL,
  `count_postroll_50` INT DEFAULT NULL,
  `count_postroll_75` INT DEFAULT NULL,
  `count_streaming` bigint(20) DEFAULT '0',
  `aggr_streaming` bigint(20) DEFAULT '0',
  PRIMARY KEY `partner_id` (`partner_id`,`date_id`, `hour_id`),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MYISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MYISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MYISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MYISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MYISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MYISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MYISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MYISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MYISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MYISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MYISAM);
 
 
    
/* update procedures */
    
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
	
    # Old aggregate (delete when KMC don't need)
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

    # Hourly aggregation
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
    
    # Run post aggregate actions 
	SET extra = CONCAT('daily_procedure_',v_aggr_table);
	IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_NAME=extra) THEN
		
		SET @ss = CONCAT('CALL ',extra,'(''', p_date_val,''',''',p_aggr_name,''');'); 
		PREPARE stmt1 FROM  @ss;
		EXECUTE stmt1;
		DEALLOCATE PREPARE stmt1;
	END IF ;
    
    # Run post hourly aggregate actions
    SET extra = CONCAT('daily_procedure_',v_hourly_aggr_table);
	IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_NAME=extra) THEN
		
		SET @ss = CONCAT('CALL ',extra,'(''', p_date_val,''',''',p_aggr_name,''');'); 
		PREPARE stmt1 FROM  @ss;
		EXECUTE stmt1;
		DEALLOCATE PREPARE stmt1;
	END IF ;
    
	SET @s = CONCAT('UPDATE aggr_managment SET is_calculated = 1,end_time = NOW()
	WHERE aggr_name = ''',p_aggr_name,''' AND aggr_day = ''',p_date_val,'''');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
    END$$

DELIMITER ;

DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_hourly_events_widget`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `daily_procedure_dwh_hourly_events_widget`(date_val DATE,p_aggr_name VARCHAR(100))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	DECLARE v_aggr_id_field VARCHAR(100);
	DECLARE v_aggr_id_field_str VARCHAR(100);

    SELECT hourly_aggr_table, aggr_id_field
	INTO  v_aggr_table, v_aggr_id_field
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
			widget_id,
     		count_widget_loads)
    	SELECT  
    		partner_id,event_date_id,HOUR(event_time),widget_id,
    		SUM(IF(event_type_id=1,1,NULL)) count_widget_loads
		FROM dwh_fact_events  ev
		WHERE event_type_id IN (1) 
			AND event_date_id = DATE(''',date_val,''')*1
		GROUP BY partner_id,DATE(event_time)*1,HOUR(event_time)',v_aggr_id_field_str,'
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

DROP PROCEDURE IF EXISTS `daily_procedure_dwh_hourly_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `daily_procedure_dwh_hourly_partner`(date_val DATE,p_aggr_name VARCHAR(100))
BEGIN

	DECLARE v_aggr_table VARCHAR(100);
	DECLARE v_aggr_id_field VARCHAR(100);
	DECLARE v_aggr_id_field_str VARCHAR(100);

    SELECT hourly_aggr_table, aggr_id_field
	INTO  v_aggr_table, v_aggr_id_field
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
    		count_storage,  
		count_streaming )
   		SELECT partner_id,pa.activity_date_id date_id, 0 hour_id,
			SUM(IF(partner_activity_id = 1, amount ,NULL)) count_bandwidth, 
			SUM(IF(partner_activity_id = 3 AND partner_sub_activity_id=301, amount,NULL)) count_storage, 
			SUM(IF(partner_activity_id = 7, amount, NULL)) count_streaming 
		FROM dwh_fact_partner_activities  pa 
		WHERE 
			pa.activity_date_id=DATE(''',date_val,''')*1
		GROUP BY partner_id,pa.activity_date_id
    	ON DUPLICATE KEY UPDATE
    		count_bandwidth=VALUES(count_bandwidth), 
    		count_storage=VALUES(count_storage),
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
	
    # After removing aggr tables, undo comment:
	# CALL daily_procedure_dwh_aggr_partner_daily_usage(date_val) ;
END$$

DELIMITER ;

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
	
	# Old aggregation
	SET @s = CONCAT('delete from ',v_aggr_table,'
		where date_id = DATE(''',date_val,''')*1');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	#hourly aggregation
	SET @s = CONCAT('delete from ',v_hourly_aggr_table,'
		where date_id = DATE(''',date_val,''')*1');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	
	
	SET @s = CONCAT('UPDATE aggr_managment SET is_calculated = 0 
	WHERE aggr_name = ''',p_aggr_name,''' AND aggr_day = ''',date_val,'''');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	CALL calc_aggr_day(date_val,p_aggr_name);
    END$$

DELIMITER ;

