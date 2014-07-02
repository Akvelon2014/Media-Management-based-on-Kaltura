USE kalturadw;

DROP TABLE IF EXISTS `dwh_hourly_partner_new`;

CREATE TABLE `dwh_hourly_partner_new` (
  `partner_id` int(11) NOT NULL DEFAULT '0',
  `date_id` int(11) NOT NULL DEFAULT '0',
  `hour_id` int(11) NOT NULL DEFAULT '0',
  `sum_time_viewed` decimal(20,3) DEFAULT NULL,
  `count_time_viewed` int(11) DEFAULT NULL,
  `count_plays` int(11) DEFAULT NULL,
  `count_loads` int(11) DEFAULT NULL,
  `count_plays_25` int(11) DEFAULT NULL,
  `count_plays_50` int(11) DEFAULT NULL,
  `count_plays_75` int(11) DEFAULT NULL,
  `count_plays_100` int(11) DEFAULT NULL,
  `count_edit` int(11) DEFAULT NULL,
  `count_viral` int(11) DEFAULT NULL,
  `count_download` int(11) DEFAULT NULL,
  `count_report` int(11) DEFAULT NULL,
  `new_admins` INT(11) DEFAULT NULL,
  `new_videos` INT(11) DEFAULT NULL,
  `deleted_videos` INT(11) DEFAULT NULL,
  `new_images` INT(11) DEFAULT NULL,
  `deleted_images` INT(11) DEFAULT NULL,
  `new_audios` INT(11) DEFAULT NULL,
  `deleted_audios` INT(11) DEFAULT NULL,
  `new_livestreams` INT(11) DEFAULT NULL,
  `deleted_livestreams` INT(11) DEFAULT NULL,
  `new_playlists` INT(11) DEFAULT NULL,
  `deleted_playlists` INT(11) DEFAULT NULL,
  `new_documents` INT(11) DEFAULT NULL,
  `deleted_documents` INT(11) DEFAULT NULL,
  `new_other_entries` INT(11) DEFAULT NULL,
  `deleted_other_entries` INT(11) DEFAULT NULL,
  `flag_active_site` tinyint(4) DEFAULT '0',
  `flag_active_publisher` tinyint(4) DEFAULT '0',
  `count_buf_start` int(11) DEFAULT NULL,
  `count_buf_end` int(11) DEFAULT NULL,
  `count_open_full_screen` int(11) DEFAULT NULL,
  `count_close_full_screen` int(11) DEFAULT NULL,
  `count_replay` int(11) DEFAULT NULL,
  `count_seek` int(11) DEFAULT NULL,
  `count_open_upload` int(11) DEFAULT NULL,
  `count_save_publish` int(11) DEFAULT NULL,
  `count_close_editor` int(11) DEFAULT NULL,
  `count_pre_bumper_played` int(11) DEFAULT NULL,
  `count_post_bumper_played` int(11) DEFAULT NULL,
  `count_bumper_clicked` int(11) DEFAULT NULL,
  `count_preroll_started` int(11) DEFAULT NULL,
  `count_midroll_started` int(11) DEFAULT NULL,
  `count_postroll_started` int(11) DEFAULT NULL,
  `count_overlay_started` int(11) DEFAULT NULL,
  `count_preroll_clicked` int(11) DEFAULT NULL,
  `count_midroll_clicked` int(11) DEFAULT NULL,
  `count_postroll_clicked` int(11) DEFAULT NULL,
  `count_overlay_clicked` int(11) DEFAULT NULL,
  `count_preroll_25` int(11) DEFAULT NULL,
  `count_preroll_50` int(11) DEFAULT NULL,
  `count_preroll_75` int(11) DEFAULT NULL,
  `count_midroll_25` int(11) DEFAULT NULL,
  `count_midroll_50` int(11) DEFAULT NULL,
  `count_midroll_75` int(11) DEFAULT NULL,
  `count_postroll_25` int(11) DEFAULT NULL,
  `count_postroll_50` int(11) DEFAULT NULL,
  `count_postroll_75` int(11) DEFAULT NULL,
  PRIMARY KEY (`partner_id`,`date_id`,`hour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (date_id)
(partition p_0 values less than (1))*/;

CALL apply_table_partitions_to_target_table('dwh_hourly_partner');

INSERT INTO kalturadw.dwh_hourly_partner_new
	(partner_id, date_id, hour_id, sum_time_viewed, count_time_viewed, count_plays, 
	count_loads, count_plays_25, count_plays_50, count_plays_75, count_plays_100, count_edit, 
	count_viral, count_download, count_report, flag_active_site, flag_active_publisher, count_buf_start, 
	count_buf_end, count_open_full_screen, count_close_full_screen, count_replay, count_seek, count_open_upload, 
	count_save_publish, count_close_editor, count_pre_bumper_played, count_post_bumper_played, 
	count_bumper_clicked, count_preroll_started, count_midroll_started, count_postroll_started,
	count_overlay_started, count_preroll_clicked, count_midroll_clicked, count_postroll_clicked, 
	count_overlay_clicked, count_preroll_25, count_preroll_50, count_preroll_75, count_midroll_25, 
	count_midroll_50, count_midroll_75, count_postroll_25, count_postroll_50, count_postroll_75)
SELECT 	partner_id, date_id, hour_id, sum_time_viewed, count_time_viewed, count_plays, 
	count_loads, count_plays_25, count_plays_50, count_plays_75, count_plays_100, count_edit, 
	count_viral, count_download, count_report, flag_active_site, flag_active_publisher, count_buf_start, 
	count_buf_end, count_open_full_screen, count_close_full_screen, count_replay, count_seek, count_open_upload, 
	count_save_publish, count_close_editor, count_pre_bumper_played, count_post_bumper_played, 
	count_bumper_clicked, count_preroll_started, count_midroll_started, count_postroll_started,
	count_overlay_started, count_preroll_clicked, count_midroll_clicked, count_postroll_clicked, 
	count_overlay_clicked, count_preroll_25, count_preroll_50, count_preroll_75, count_midroll_25, 
	count_midroll_50, count_midroll_75, count_postroll_25, count_postroll_50, count_postroll_75
FROM 	kalturadw.dwh_hourly_partner;

RENAME TABLE kalturadw.dwh_hourly_partner to kalturadw.dwh_hourly_partner_old; 
RENAME TABLE kalturadw.dwh_hourly_partner_new to kalturadw.dwh_hourly_partner; 
