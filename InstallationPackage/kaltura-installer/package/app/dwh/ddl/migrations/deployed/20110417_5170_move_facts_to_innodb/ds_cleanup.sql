DROP TABLE IF EXISTS `kalturadw_ds`.`ds_bandwidth_usage`;
CREATE TABLE `kalturadw_ds`.`ds_bandwidth_usage` (
  `cycle_id` INT(11) NOT NULL,
  `file_id` INT(11) NOT NULL,
  `partner_id` INT(11) NOT NULL DEFAULT -1,
  `activity_date_id` INT(11) DEFAULT '-1',
  `activity_hour_id` TINYINT(4) DEFAULT '-1',
  `bandwidth_source_id` BIGINT(20) DEFAULT NULL,
  `url` VARCHAR(2000) DEFAULT NULL,
  `bandwidth_bytes` BIGINT(20) DEFAULT '0'
) ENGINE=INNODB DEFAULT CHARSET=utf8
PARTITION BY LIST (cycle_id)
(PARTITION p_0 VALUES IN (0) ENGINE = INNODB);

DROP TABLE IF EXISTS `kalturadw_ds`.`ds_events`;
CREATE TABLE `kalturadw_ds`.`ds_events`
     (   file_id INT NOT NULL
	, event_id INT  NOT NULL
	, event_type_id SMALLINT  NOT NULL
	, client_version VARCHAR(31)
	, event_time DATETIME
	, event_date_id INT
	, event_hour_id TINYINT
	, session_id VARCHAR(50)
	, partner_id INT
	, entry_id VARCHAR(20)
	, unique_viewer VARCHAR(40)
	, widget_id VARCHAR(31)
	, ui_conf_id INT
	, uid VARCHAR(64)
	, current_point INT
	, duration INT
	, user_ip VARCHAR(15)
	, user_ip_number INT UNSIGNED
	, country_id INT
	, location_id INT
	, process_duration INT
	, control_id VARCHAR(15)
	, seek INT
	, new_point INT
	, domain_id INT
	, entry_media_type_id INT
	, entry_partner_id INT
	, referrer_id INT(11)) ENGINE=INNODB  DEFAULT CHARSET=utf8  
     PARTITION BY LIST(file_id) (PARTITION p_0 VALUES IN (0));

DROP TABLE IF EXISTS `kalturadw_ds`.`ods_fms_session_events`;
CREATE TABLE `kalturadw_ds`.`ods_fms_session_events` (
  `file_id` INT(11) UNSIGNED NOT NULL,
  `event_type_id` TINYINT(3) UNSIGNED NOT NULL,
  `event_category_id` TINYINT(3) UNSIGNED NOT NULL,
  `event_time` DATETIME NOT NULL,
  `event_time_tz` VARCHAR(3) NOT NULL,
  `event_date_id` INT(11) NOT NULL,
  `event_hour_id` TINYINT(3) NOT NULL,
  `context` VARCHAR(100) DEFAULT NULL,
  `entry_id` VARCHAR(20) DEFAULT NULL,
  `partner_id` INT(10) DEFAULT NULL,
  `external_id` VARCHAR(50) DEFAULT NULL,
  `server_ip` INT(10) UNSIGNED DEFAULT NULL,
  `server_process_id` INT(10) UNSIGNED NOT NULL,
  `server_cpu_load` SMALLINT(5) UNSIGNED NOT NULL,
  `server_memory_load` SMALLINT(5) UNSIGNED NOT NULL,
  `adaptor_id` SMALLINT(5) UNSIGNED NOT NULL,
  `virtual_host_id` SMALLINT(5) UNSIGNED NOT NULL,
  `app_id` TINYINT(3) UNSIGNED NOT NULL,
  `app_instance_id` TINYINT(3) UNSIGNED NOT NULL,
  `duration_secs` INT(10) UNSIGNED NOT NULL,
  `status_id` SMALLINT(3) UNSIGNED DEFAULT NULL,
  `status_desc_id` TINYINT(3) UNSIGNED NOT NULL,
  `client_ip_str` VARCHAR(15) NOT NULL,
  `client_ip` INT(10) UNSIGNED NOT NULL,
  `client_country_id` INT(10) UNSIGNED DEFAULT '0',
  `client_location_id` INT(10) UNSIGNED DEFAULT '0',
  `client_protocol_id` TINYINT(3) UNSIGNED NOT NULL,
  `uri` VARCHAR(4000) NOT NULL,
  `uri_stem` VARCHAR(2000) DEFAULT NULL,
  `uri_query` VARCHAR(2000) DEFAULT NULL,
  `referrer` VARCHAR(4000) DEFAULT NULL,
  `user_agent` VARCHAR(2000) DEFAULT NULL,
  `session_id` VARCHAR(20) NOT NULL,
  `client_to_server_bytes` BIGINT(20) UNSIGNED NOT NULL,
  `server_to_client_bytes` BIGINT(20) UNSIGNED NOT NULL,
  `stream_name` VARCHAR(50) DEFAULT NULL,
  `stream_query` VARCHAR(50) DEFAULT NULL,
  `stream_file_name` VARCHAR(4000) DEFAULT NULL,
  `stream_type_id` TINYINT(3) UNSIGNED DEFAULT NULL,
  `stream_size_bytes` INT(11) DEFAULT NULL,
  `stream_length_secs` INT(11) DEFAULT NULL,
  `stream_position` INT(11) DEFAULT NULL,
  `client_to_server_stream_bytes` INT(10) UNSIGNED DEFAULT NULL,
  `server_to_client_stream_bytes` INT(10) UNSIGNED DEFAULT NULL,
  `server_to_client_qos_bytes` INT(10) UNSIGNED DEFAULT NULL
) ENGINE=MYISAM DEFAULT CHARSET=utf8
 PARTITION BY LIST (file_id)
(PARTITION p_0 VALUES IN (0) ENGINE = MYISAM);

ALTER TABLE kalturadw_ds.invalid_ds_lines ENGINE = INNODB;
ALTER TABLE kalturadw_ds.invalid_ds_lines_error_codes ENGINE = INNODB;
ALTER TABLE kalturadw_ds.invalid_event_lines ENGINE = INNODB;
ALTER TABLE kalturadw_ds.invalid_fms_event_lines ENGINE = INNODB;

