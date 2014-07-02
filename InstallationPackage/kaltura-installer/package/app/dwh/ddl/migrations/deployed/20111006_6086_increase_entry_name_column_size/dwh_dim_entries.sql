USE `kalturadw`;

DROP TABLE IF EXISTS `dwh_dim_entries_new`;

CREATE TABLE `dwh_dim_entries_new` (
  `entry_id` varchar(20) NOT NULL DEFAULT '',
  `kshow_id` varchar(20) DEFAULT NULL,
  `kuser_id` int(11) DEFAULT '-1',
  `entry_name` varchar(256) DEFAULT NULL,
  `entry_type_id` smallint(6) DEFAULT NULL,
  `entry_media_type_id` smallint(6) DEFAULT NULL,
  `data` varchar(48) DEFAULT NULL,
  `thumbnail` varchar(48) DEFAULT NULL,
  `views` int(11) DEFAULT '0',
  `votes` int(11) DEFAULT '0',
  `comments` int(11) DEFAULT '0',
  `favorites` int(11) DEFAULT '0',
  `total_rank` int(11) DEFAULT '0',
  `rank` int(11) DEFAULT '0',
  `tags` text,
  `anonymous` tinyint(4) DEFAULT NULL,
  `entry_status_id` smallint(6) DEFAULT '-1',
  `entry_media_source_id` smallint(6) DEFAULT '-1',
  `entry_source_id` varchar(48) DEFAULT '-1',
  `source_link` varchar(1024) DEFAULT NULL,
  `entry_license_type_id` smallint(6) DEFAULT '-1',
  `credit` varchar(1024) DEFAULT NULL,
  `length_in_msecs` int(11) DEFAULT '0',
  `height` int(11) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `conversion_quality` varchar(50) DEFAULT NULL,
  `storage_size` bigint(20) DEFAULT NULL,
  `editor_type_id` smallint(6) DEFAULT '-1',
  `puser_id` varchar(64) DEFAULT NULL,
  `is_admin_content` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_date_id` int(11) DEFAULT '-1',
  `created_hour_id` tinyint(4) DEFAULT '-1',
  `updated_at` datetime DEFAULT NULL,
  `updated_date_id` int(11) DEFAULT '-1',
  `updated_hour_id` tinyint(4) DEFAULT '-1',
  `partner_id` int(11) DEFAULT '-1',
  `display_in_search` tinyint(4) DEFAULT NULL,
  `subp_id` int(11) DEFAULT '-1',
  `custom_data` text,
  `search_text` varchar(4096) DEFAULT NULL,
  `screen_name` varchar(20) DEFAULT NULL,
  `site_url` varchar(256) DEFAULT NULL,
  `permissions` int(11) DEFAULT NULL,
  `group_id` varchar(64) DEFAULT NULL,
  `plays` int(11) DEFAULT '0',
  `partner_data` varchar(4096) DEFAULT NULL,
  `int_id` int(11) NOT NULL,
  `indexed_custom_data_1` int(11) DEFAULT NULL,
  `description` text,
  `media_date` datetime DEFAULT NULL,
  `admin_tags` text,
  `moderation_status` tinyint(4) DEFAULT '-1',
  `moderation_count` int(11) DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL,
  `modified_date_id` int(11) DEFAULT '-1',
  `modified_hour_id` tinyint(4) DEFAULT '-1',
  `dwh_creation_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dwh_update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ri_ind` tinyint(4) NOT NULL DEFAULT '0',
  `access_control_id` int(11) DEFAULT NULL,
  `conversion_profile_id` int(11) DEFAULT NULL,
  `categories` varchar(4096) DEFAULT NULL,
  `categories_ids` varchar(1024) DEFAULT NULL,
  `search_text_discrete` varchar(4096) DEFAULT NULL,
  `flavor_params_ids` varchar(512) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `start_date_id` int(11) DEFAULT NULL,
  `start_hour_id` tinyint(4) DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `end_date_id` int(11) DEFAULT NULL,
  `end_hour_id` tinyint(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO kalturadw.dwh_dim_entries_new
select * from kalturadw.dwh_dim_entries;

ALTER TABLE kalturadw.dwh_dim_entries_new
  ADD KEY `partner_id_created_media_type_source` (`partner_id`,`created_at`,`entry_media_type_id`,`entry_media_source_id`),
  ADD KEY `dwh_update_date` (`dwh_update_date`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `updated_at` (`updated_at`),
  ADD KEY `modified_at` (`modified_at`),
  ADD KEY `entry_id_modified_at` (`entry_id`,`modified_at`),
  ADD KEY `entry_id_name` (`entry_id`,`entry_name`),
  ADD KEY `entry_id_media_type` (`entry_id`,`entry_media_type_id`),
  ADD KEY `entry_FI_3` (`access_control_id`),
  ADD KEY `entry_FI_5` (`conversion_profile_id`),
  ADD FULLTEXT KEY `search_text_index` (`search_text`),
  ADD FULLTEXT KEY `all_text` (`entry_name`,`description`,`tags`,`admin_tags`),
  ADD FULLTEXT KEY `admin_tags_text` (`admin_tags`),
  ADD FULLTEXT KEY `search_text_discrete_index` (`search_text_discrete`);

DROP TABLE kalturadw.dwh_dim_entries;
RENAME TABLE kalturadw.dwh_dim_entries_new TO kalturadw.dwh_dim_entries;

DELIMITER $$

DROP TRIGGER /*!50032 IF EXISTS */ `dwh_dim_entries_setcreationtime_oninsert`$$

CREATE
    /*!50017 DEFINER = 'root'@'localhost' */
    TRIGGER `dwh_dim_entries_setcreationtime_oninsert` BEFORE INSERT ON `dwh_dim_entries` 
    FOR EACH ROW SET new.dwh_creation_date = NOW();
$$

DELIMITER ;
