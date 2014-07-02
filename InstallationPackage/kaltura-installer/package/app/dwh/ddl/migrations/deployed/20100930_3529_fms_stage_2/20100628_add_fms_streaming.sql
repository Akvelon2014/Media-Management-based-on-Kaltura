DROP TABLE IF EXISTS `kalturadw_ds`.`ods_fms_session_events`;
CREATE TABLE `ods_fms_session_events` (
  `file_id` int(11) unsigned NOT NULL,
  `event_type_id` tinyint(3) unsigned NOT NULL,
  `event_category_id` tinyint(3) unsigned NOT NULL,
  `event_time` datetime NOT NULL,
  `event_time_tz` varchar(3) NOT NULL,
  `event_date_id` int(11) NOT NULL,
  `event_hour_id` tinyint(3) NOT NULL,
  `context` varchar(100) DEFAULT NULL,
  `entry_id` varchar(20) DEFAULT NULL,
  `partner_id` int(10) DEFAULT NULL,
  `external_id` varchar(50) DEFAULT NULL,
  `server_ip` int(10) unsigned DEFAULT NULL,
  `server_process_id` int(10) unsigned NOT NULL,
  `server_cpu_load` smallint(5) unsigned NOT NULL,
  `server_memory_load` smallint(5) unsigned NOT NULL,
  `adaptor_id` smallint(5) unsigned NOT NULL,
  `virtual_host_id` smallint(5) unsigned NOT NULL,
  `app_id` tinyint(3) unsigned NOT NULL,
  `app_instance_id` tinyint(3) unsigned NOT NULL,
  `duration_secs` int(10) unsigned NOT NULL,
  `status_id` smallint(3) unsigned DEFAULT NULL,
  `status_desc_id` tinyint(3) unsigned NOT NULL,
  `client_ip_str` varchar(15) NOT NULL,
  `client_ip` int(10) unsigned NOT NULL,
  `client_country_id` int(10) unsigned DEFAULT '0',
  `client_location_id` int(10) unsigned DEFAULT '0',
  `client_protocol_id` tinyint(3) unsigned NOT NULL,
  `uri` varchar(4000) NOT NULL,
  `uri_stem` varchar(2000) DEFAULT NULL,
  `uri_query` varchar(2000) DEFAULT NULL,
  `referrer` varchar(4000) DEFAULT NULL,
  `user_agent` varchar(2000) DEFAULT NULL,
  `session_id` varchar(20) NOT NULL,
  `client_to_server_bytes` bigint(20) unsigned NOT NULL,
  `server_to_client_bytes` bigint(20) unsigned NOT NULL,
  `stream_name` varchar(50) DEFAULT NULL,
  `stream_query` varchar(50) DEFAULT NULL,
  `stream_file_name` varchar(4000) DEFAULT NULL,
  `stream_type_id` tinyint(3) unsigned DEFAULT NULL,
  `stream_size_bytes` int(11) DEFAULT NULL,
  `stream_length_secs` int(11) DEFAULT NULL,
  `stream_position` int(11) DEFAULT NULL,
  `client_to_server_stream_bytes` int(10) unsigned DEFAULT NULL,
  `server_to_client_stream_bytes` int(10) unsigned DEFAULT NULL,
  `server_to_client_qos_bytes` int(10) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY LIST (file_id)
(PARTITION p_0 VALUES IN (0) ENGINE = MyISAM) */;

DROP TABLE IF EXISTS `invalid_fms_event_lines`;
CREATE TABLE `invalid_fms_event_lines` (
  `line_id` int(11) NOT NULL AUTO_INCREMENT,
  `line_number` int(11) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `error_reason_code` smallint(6) DEFAULT NULL,
  `error_reason` varchar(255) DEFAULT NULL,
  `event_line` varchar(1023) DEFAULT NULL,
  `insert_time` datetime DEFAULT NULL,
  `date_id` int(11) DEFAULT NULL,
  `entry_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`line_id`),
  KEY `date_id_partner_id` (`date_id`,`entry_id`),
  KEY `file_reason_code` (`file_id`,`error_reason_code`)
) ENGINE=MyISAM AUTO_INCREMENT=369 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_adaptor`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_adaptor` (
  `adaptor_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `adaptor` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`adaptor_id`)
) ENGINE=MYISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_app`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_app` (
  `app_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MYISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_app_instance`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_app_instance` (
  `app_instance_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_instance` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`app_instance_id`)
) ENGINE=MYISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_client_protocol`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_client_protocol` (
  `client_protocol_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_protocol` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`client_protocol_id`)
) ENGINE=MYISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_event_category`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_event_category` (
  `event_category_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_category` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`event_category_id`)
) ENGINE=MYISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_event_type`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_event_type` (
  `event_type_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`event_type_id`)
) ENGINE=MYISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_status_description`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_status_description` (
  `status_description_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_description` VARCHAR(300) DEFAULT '<unset status>',
  `status_number` SMALLINT(3) UNSIGNED DEFAULT NULL,
  `event_type` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`status_description_id`)
) ENGINE=MYISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_stream_type`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_stream_type` (
  `stream_type_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `stream_type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`stream_type_id`)
) ENGINE=MYISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_virtual_host`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_virtual_host` (
  `virtual_host_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtual_host` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`virtual_host_id`)
) ENGINE=MYISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `kalturadw`.`dwh_fact_fms_session_events`;
CREATE TABLE  `kalturadw`.`dwh_fact_fms_session_events` (
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
  `server_to_client_qos_bytes` INT(10) UNSIGNED DEFAULT NULL,
  KEY `partner_id_event_type_id_time` (`partner_id`,`event_type_id`,`event_time`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (TO_DAYS(event_time))
(PARTITION p_201001 VALUES LESS THAN (734169) ENGINE = MyISAM,
 PARTITION p_201002 VALUES LESS THAN (734197) ENGINE = MyISAM,
 PARTITION p_201003 VALUES LESS THAN (734228) ENGINE = MyISAM,
 PARTITION p_201004 VALUES LESS THAN (734258) ENGINE = MyISAM,
 PARTITION p_201005 VALUES LESS THAN (734289) ENGINE = MyISAM) */;
 
DROP TABLE IF EXISTS `kalturadw`.`dwh_fact_fms_sessions`;
CREATE TABLE `kalturadw`.`dwh_fact_fms_sessions` (
  `session_id` varchar(20) NOT NULL,
  `session_time` datetime NOT NULL,
  `session_date_id` int(11) unsigned DEFAULT NULL,
  `session_partner_id` int(10) unsigned DEFAULT NULL,
  `total_bytes` bigint(20) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 
 DROP PROCEDURE IF EXISTS  `kalturadw_ds`.`agg_new_fms_to_partner_activity`;
DELIMITER $$
CREATE DEFINER=`etl`@`localhost` PROCEDURE `kalturadw_ds`.`agg_new_fms_to_partner_activity`()
BEGIN
  DECLARE DEFAULT_ACTIVITY_ID INTEGER;
  DECLARE STREAMING_ACTIVITY_ID INTEGER;
  DECLARE STREAMING_SUB_ACTIVITY INTEGER;
  SET DEFAULT_ACTIVITY_ID = 1;
  SET STREAMING_ACTIVITY_ID = 7;
  SET STREAMING_SUB_ACTIVITY = 700;

  INSERT INTO kalturadw.dwh_fact_partner_activities
  (activity_id,partner_id,activity_date,activity_date_id,activity_hour_id,partner_activity_id,partner_sub_activity_id,amount)
  SELECT DEFAULT_ACTIVITY_ID,session_partner_id,DATE(session_time),session_date_id,0 hour_id,STREAMING_ACTIVITY_ID,STREAMING_SUB_ACTIVITY,SUM(total_bytes)
  FROM kalturadw.dwh_fact_fms_sessions
  WHERE session_date_id IN (
    SELECT DISTINCT aggr_day_int
    FROM kalturadw.aggr_managment
    WHERE aggr_name = 'fms_sessions' AND is_calculated = 0 AND aggr_day <= NOW())
  GROUP BY session_partner_id,DATE(session_time),session_date_id
  ON DUPLICATE KEY UPDATE
    amount=VALUES(amount);

  UPDATE kalturadw.aggr_managment
  SET is_calculated = 1
  WHERE aggr_name = 'fms_sessions' AND aggr_day <= NOW();
END $$
DELIMITER ;
  
DELIMITER $$
DROP PROCEDURE IF EXISTS `kalturadw`.`add_partition_for_fact_event` $$
DROP PROCEDURE IF EXISTS `kalturadw`.`add_partitions` $$
CREATE DEFINER=`etl`@`localhost` PROCEDURE `kalturadw`.`add_partitions`()
BEGIN
	CALL kalturadw.add_partition_for_fact_table('dwh_fact_events');
	CALL kalturadw.add_partition_for_fact_table('dwh_fact_fms_session_events');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_entry');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_domain');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_country');
	CALL kalturadw.add_partition_for_table('dwh_aggr_events_widget');
	CALL kalturadw.add_partition_for_table('dwh_aggr_partner');
	CALL kalturadw.add_partition_for_table('dwh_aggr_partner_daily_usage');
END $$


DELIMITER ;






