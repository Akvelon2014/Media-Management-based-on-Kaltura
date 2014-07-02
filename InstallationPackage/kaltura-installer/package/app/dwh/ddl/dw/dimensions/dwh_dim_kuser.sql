USE kalturadw;

DROP TABLE IF EXISTS `dwh_dim_kusers`;

CREATE TABLE `dwh_dim_kusers` (
  `kuser_id` INT NOT NULL ,
  `screen_name` VARCHAR(127) DEFAULT 'missing value',
  `full_name` VARCHAR(40) DEFAULT 'missing value',
  `first_name` VARCHAR(40),
  `last_name` VARCHAR(40),
  `email` VARCHAR(100) DEFAULT 'missing value',
  `date_of_birth` DATE DEFAULT NULL,
   location_id INT DEFAULT -1,
   country_id INT DEFAULT -1,
  `zip` VARCHAR(10) DEFAULT NULL,
  `url_list` VARCHAR(256) DEFAULT NULL,
  `picture` VARCHAR(48) DEFAULT NULL,
  `icon` TINYINT DEFAULT NULL,
  `about_me` VARCHAR(4096) DEFAULT NULL,
  `tags` TEXT,
  `tagline` VARCHAR(256) DEFAULT NULL,
  `network_highschool` VARCHAR(30) DEFAULT NULL,
  `network_college` VARCHAR(30) DEFAULT NULL,
  `network_other` VARCHAR(30) DEFAULT NULL,
  `mobile_num` VARCHAR(16) DEFAULT NULL,
  `mature_content` TINYINT DEFAULT '-1',
  `gender_id` TINYINT DEFAULT NULL,
  `gender_name` VARCHAR(7) DEFAULT NULL,
  `registration_ip` INT DEFAULT NULL,
  `registration_cookie` VARCHAR(256) DEFAULT NULL,
  `im_list` VARCHAR(256) DEFAULT NULL,
  `views` INT DEFAULT '0',
  `fans` INT DEFAULT '0',
  `entries` INT DEFAULT '0',
  `produced_kshows` INT DEFAULT '0',
  `kuser_status_id` INT DEFAULT -1,
  `kuser_status_name` VARCHAR(64) DEFAULT 'missing value',
  `created_at` DATETIME DEFAULT NULL,
  `created_date_id` INT DEFAULT '-1',
   created_hour_id TINYINT DEFAULT '-1',
  `updated_at` DATETIME DEFAULT NULL,
   updated_date_id INT DEFAULT '-1',
   updated_hour_id TINYINT DEFAULT '-1',
  `operational_measures_updated_at` datetime default null,
  `partner_id` INT DEFAULT '-1',
  `display_in_search` TINYINT DEFAULT '1',
  `search_text` VARCHAR(4096) DEFAULT NULL,
  `partner_data` VARCHAR(4096) DEFAULT NULL,
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '0',
   storage_size INT,
   puser_id varchar(100),
   admin_tags text,
   indexed_partner_data_int INT,
   indexed_partner_data_string varchar(64),
   is_admin TINYINT(4),
  PRIMARY KEY (`kuser_id`),
  KEY `partner_id_index` (`partner_id`,`kuser_id`),
  KEY `created_index` (`created_at`),
  KEY `operational_measures_updated_at` (`operational_measures_updated_at`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_kusers_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_kusers`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();

    