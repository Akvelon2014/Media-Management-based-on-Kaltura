DROP TABLE IF EXISTS `kalturadw`.dwh_dim_partners;

CREATE TABLE `kalturadw`.`dwh_dim_partners` (
  `partner_id` INT NOT NULL ,
  `partner_name` VARCHAR(256) DEFAULT 'missing value',
  `url1` VARCHAR(1024) DEFAULT NULL,
  `url2` VARCHAR(1024) DEFAULT NULL,
  `secret` VARCHAR(50) DEFAULT NULL,
  `admin_secret` VARCHAR(50) DEFAULT NULL,
  `max_number_of_hits_per_day` INT DEFAULT '-1',
  `appear_in_search` TINYINT DEFAULT '2',
  `debug_level` TINYINT DEFAULT '0',
  `invalid_login_count` SMALLINT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `created_date_id` INT DEFAULT '-1',
   created_hour_id TINYINT DEFAULT '-1',
  `updated_at` DATETIME DEFAULT NULL,
   updated_date_id INT DEFAULT '-1',
   updated_hour_id TINYINT DEFAULT '-1',
  `partner_alias` VARCHAR(64) DEFAULT NULL,
  `anonymous_kuser_id` INT DEFAULT '-1',
  `ks_max_expiry_in_seconds` INT DEFAULT NULL,
  `create_user_on_demand` TINYINT DEFAULT '1',
  `prefix` VARCHAR(32) DEFAULT NULL,
  `admin_name` VARCHAR(50) DEFAULT 'missing value',
  `admin_email` VARCHAR(50) DEFAULT 'missing value',
  `description` VARCHAR(1024) DEFAULT 'missing value',
  `commercial_use` TINYINT DEFAULT '0',
  `moderate_content` TINYINT DEFAULT '0',
  `notify` TINYINT DEFAULT '0',
  `custom_data` TEXT,
  `service_config_id` VARCHAR(64) DEFAULT NULL,
  `partner_status_id` SMALLINT DEFAULT '-1', 
  `partner_status_name` VARCHAR(64) DEFAULT 'missing value',
  `content_categories` VARCHAR(1024) DEFAULT NULL,
  `partner_type_id` SMALLINT DEFAULT '-1',
  `partner_type_name` VARCHAR(64) DEFAULT 'missing value',
   phone   VARCHAR(64) DEFAULT NULL ,
   describe_yourself VARCHAR(64) DEFAULT NULL,
   adult_content TINYINT DEFAULT '-1',
   partner_package TINYINT DEFAULT '-1',
   usage_percent INT  DEFAULT NULL,
   storage_usage INT  DEFAULT NULL,
   eighty_percent_warning INT DEFAULT NULL,
   usage_limit_warning INT DEFAULT NULL,
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '0',
   `priority_group_id` INTEGER ,
   `work_group_id` INTEGER ,
	`partner_group_type_id` SMALLINT default 1,
	`partner_parent_id` INTEGER default null,
	`monitor_usage` int(11) DEFAULT NULL,
	class_of_service_id INT(11),
	vertical_id INT(11),
	internal_use BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY (`partner_id`),
    KEY `partner_alias_index` (`partner_alias`),
    KEY `Partner_Status_ID` (`Partner_Status_ID`),
    KEY `Partner_Type_ID` (`Partner_Type_ID`),
    KEY `partner_id,updated_at` (partner_id,updated_at),
    KEY `dwh_update_date` (`dwh_update_date`),
    KEY `partner_parent_index`(`partner_parent_id`),
    KEY `partner_package_indx` (`partner_package`,`partner_id`,`partner_name`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_partners_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_partners`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();





