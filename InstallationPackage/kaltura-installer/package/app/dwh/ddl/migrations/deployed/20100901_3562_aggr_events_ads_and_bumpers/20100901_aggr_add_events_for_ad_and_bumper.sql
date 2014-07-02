/* Add event types to bi_sources */
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(24,'Preroll Started'); 
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(25,'Midroll Started'); 
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(26,'Postroll Started');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(27,'Overlay Started');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(28,'Preroll Clicked');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(29,'Midroll Clicked');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(30,'Postroll Clicked');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(31,'Overlay Clicked');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(32,'Preroll 25');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(33,'Preroll 50');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(34,'Preroll 75');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(35,'Midroll 25');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(36,'Midroll 50');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(37,'Midroll 75');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(38,'Postroll 25');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(39,'Postroll 50');
INSERT INTO kalturadw_bisources.bisources_event_type (event_type_id,event_type_name) VALUES(40,'Postroll 75');

/* add columns to aggregations */
ALTER TABLE kalturadw.dwh_aggr_events_country
ADD COLUMN `count_open_full_screen` int DEFAULT NULL,
ADD COLUMN `count_close_full_screen` int DEFAULT NULL,
ADD COLUMN `count_replay` int DEFAULT NULL,
ADD COLUMN `count_seek` int DEFAULT NULL,
ADD COLUMN `count_open_upload` int DEFAULT NULL,
ADD COLUMN `count_save_publish` int DEFAULT NULL,
ADD COLUMN `count_close_editor` int DEFAULT NULL,  
ADD COLUMN `count_pre_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_post_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_bumper_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_started` INT DEFAULT NULL,
ADD COLUMN `count_midroll_started` INT DEFAULT NULL,
ADD COLUMN `count_postroll_started` INT DEFAULT NULL,
ADD COLUMN `count_overlay_started` INT DEFAULT NULL,
ADD COLUMN `count_preroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_midroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_postroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_overlay_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_25` INT DEFAULT NULL,
ADD COLUMN `count_preroll_50` INT DEFAULT NULL,
ADD COLUMN `count_preroll_75` INT DEFAULT NULL,
ADD COLUMN `count_midroll_25` INT DEFAULT NULL,
ADD COLUMN `count_midroll_50` INT DEFAULT NULL,
ADD COLUMN `count_midroll_75` INT DEFAULT NULL,
ADD COLUMN `count_postroll_25` INT DEFAULT NULL,
ADD COLUMN `count_postroll_50` INT DEFAULT NULL,
ADD COLUMN `count_postroll_75` INT DEFAULT NULL;
  
ALTER TABLE kalturadw.dwh_aggr_events_domain
ADD COLUMN `count_open_full_screen` int DEFAULT NULL,
ADD COLUMN `count_close_full_screen` int DEFAULT NULL,
ADD COLUMN `count_replay` int DEFAULT NULL,
ADD COLUMN `count_seek` int DEFAULT NULL,
ADD COLUMN `count_open_upload` int DEFAULT NULL,
ADD COLUMN `count_save_publish` int DEFAULT NULL,
ADD COLUMN `count_close_editor` int DEFAULT NULL,  
ADD COLUMN `count_pre_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_post_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_bumper_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_started` INT DEFAULT NULL,
ADD COLUMN `count_midroll_started` INT DEFAULT NULL,
ADD COLUMN `count_postroll_started` INT DEFAULT NULL,
ADD COLUMN `count_overlay_started` INT DEFAULT NULL,
ADD COLUMN `count_preroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_midroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_postroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_overlay_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_25` INT DEFAULT NULL,
ADD COLUMN `count_preroll_50` INT DEFAULT NULL,
ADD COLUMN `count_preroll_75` INT DEFAULT NULL,
ADD COLUMN `count_midroll_25` INT DEFAULT NULL,
ADD COLUMN `count_midroll_50` INT DEFAULT NULL,
ADD COLUMN `count_midroll_75` INT DEFAULT NULL,
ADD COLUMN `count_postroll_25` INT DEFAULT NULL,
ADD COLUMN `count_postroll_50` INT DEFAULT NULL,
ADD COLUMN `count_postroll_75` INT DEFAULT NULL;

ALTER TABLE kalturadw.dwh_aggr_events_entry
ADD COLUMN `count_open_full_screen` int DEFAULT NULL,
ADD COLUMN `count_close_full_screen` int DEFAULT NULL,
ADD COLUMN `count_replay` int DEFAULT NULL,
ADD COLUMN `count_seek` int DEFAULT NULL,
ADD COLUMN `count_open_upload` int DEFAULT NULL,
ADD COLUMN `count_save_publish` int DEFAULT NULL,
ADD COLUMN `count_close_editor` int DEFAULT NULL,  
ADD COLUMN `count_pre_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_post_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_bumper_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_started` INT DEFAULT NULL,
ADD COLUMN `count_midroll_started` INT DEFAULT NULL,
ADD COLUMN `count_postroll_started` INT DEFAULT NULL,
ADD COLUMN `count_overlay_started` INT DEFAULT NULL,
ADD COLUMN `count_preroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_midroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_postroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_overlay_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_25` INT DEFAULT NULL,
ADD COLUMN `count_preroll_50` INT DEFAULT NULL,
ADD COLUMN `count_preroll_75` INT DEFAULT NULL,
ADD COLUMN `count_midroll_25` INT DEFAULT NULL,
ADD COLUMN `count_midroll_50` INT DEFAULT NULL,
ADD COLUMN `count_midroll_75` INT DEFAULT NULL,
ADD COLUMN `count_postroll_25` INT DEFAULT NULL,
ADD COLUMN `count_postroll_50` INT DEFAULT NULL,
ADD COLUMN `count_postroll_75` INT DEFAULT NULL;

ALTER TABLE kalturadw.dwh_aggr_events_widget
ADD COLUMN `count_open_full_screen` int DEFAULT NULL,
ADD COLUMN `count_close_full_screen` int DEFAULT NULL,
ADD COLUMN `count_replay` int DEFAULT NULL,
ADD COLUMN `count_seek` int DEFAULT NULL,
ADD COLUMN `count_open_upload` int DEFAULT NULL,
ADD COLUMN `count_save_publish` int DEFAULT NULL,
ADD COLUMN `count_close_editor` int DEFAULT NULL,  
ADD COLUMN `count_pre_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_post_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_bumper_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_started` INT DEFAULT NULL,
ADD COLUMN `count_midroll_started` INT DEFAULT NULL,
ADD COLUMN `count_postroll_started` INT DEFAULT NULL,
ADD COLUMN `count_overlay_started` INT DEFAULT NULL,
ADD COLUMN `count_preroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_midroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_postroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_overlay_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_25` INT DEFAULT NULL,
ADD COLUMN `count_preroll_50` INT DEFAULT NULL,
ADD COLUMN `count_preroll_75` INT DEFAULT NULL,
ADD COLUMN `count_midroll_25` INT DEFAULT NULL,
ADD COLUMN `count_midroll_50` INT DEFAULT NULL,
ADD COLUMN `count_midroll_75` INT DEFAULT NULL,
ADD COLUMN `count_postroll_25` INT DEFAULT NULL,
ADD COLUMN `count_postroll_50` INT DEFAULT NULL,
ADD COLUMN `count_postroll_75` INT DEFAULT NULL;

ALTER TABLE kalturadw.dwh_aggr_monthly_partner
ADD COLUMN `count_open_full_screen` int DEFAULT NULL,
ADD COLUMN `count_close_full_screen` int DEFAULT NULL,
ADD COLUMN `count_replay` int DEFAULT NULL,
ADD COLUMN `count_seek` int DEFAULT NULL,
ADD COLUMN `count_open_upload` int DEFAULT NULL,
ADD COLUMN `count_save_publish` int DEFAULT NULL,
ADD COLUMN `count_close_editor` int DEFAULT NULL,  
ADD COLUMN `count_pre_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_post_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_bumper_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_started` INT DEFAULT NULL,
ADD COLUMN `count_midroll_started` INT DEFAULT NULL,
ADD COLUMN `count_postroll_started` INT DEFAULT NULL,
ADD COLUMN `count_overlay_started` INT DEFAULT NULL,
ADD COLUMN `count_preroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_midroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_postroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_overlay_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_25` INT DEFAULT NULL,
ADD COLUMN `count_preroll_50` INT DEFAULT NULL,
ADD COLUMN `count_preroll_75` INT DEFAULT NULL,
ADD COLUMN `count_midroll_25` INT DEFAULT NULL,
ADD COLUMN `count_midroll_50` INT DEFAULT NULL,
ADD COLUMN `count_midroll_75` INT DEFAULT NULL,
ADD COLUMN `count_postroll_25` INT DEFAULT NULL,
ADD COLUMN `count_postroll_50` INT DEFAULT NULL,
ADD COLUMN `count_postroll_75` INT DEFAULT NULL;

ALTER TABLE kalturadw.dwh_aggr_partner
ADD COLUMN `count_open_full_screen` int DEFAULT NULL,
ADD COLUMN `count_close_full_screen` int DEFAULT NULL,
ADD COLUMN `count_replay` int DEFAULT NULL,
ADD COLUMN `count_seek` int DEFAULT NULL,
ADD COLUMN `count_open_upload` int DEFAULT NULL,
ADD COLUMN `count_save_publish` int DEFAULT NULL,
ADD COLUMN `count_close_editor` int DEFAULT NULL,  
ADD COLUMN `count_pre_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_post_bumper_played` INT DEFAULT NULL,
ADD COLUMN `count_bumper_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_started` INT DEFAULT NULL,
ADD COLUMN `count_midroll_started` INT DEFAULT NULL,
ADD COLUMN `count_postroll_started` INT DEFAULT NULL,
ADD COLUMN `count_overlay_started` INT DEFAULT NULL,
ADD COLUMN `count_preroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_midroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_postroll_clicked` INT DEFAULT NULL,
ADD COLUMN `count_overlay_clicked` INT DEFAULT NULL,
ADD COLUMN `count_preroll_25` INT DEFAULT NULL,
ADD COLUMN `count_preroll_50` INT DEFAULT NULL,
ADD COLUMN `count_preroll_75` INT DEFAULT NULL,
ADD COLUMN `count_midroll_25` INT DEFAULT NULL,
ADD COLUMN `count_midroll_50` INT DEFAULT NULL,
ADD COLUMN `count_midroll_75` INT DEFAULT NULL,
ADD COLUMN `count_postroll_25` INT DEFAULT NULL,
ADD COLUMN `count_postroll_50` INT DEFAULT NULL,
ADD COLUMN `count_postroll_75` INT DEFAULT NULL;