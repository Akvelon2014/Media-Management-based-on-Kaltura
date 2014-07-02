DROP TABLE IF EXISTS `kalturadw_ds`.`ods_fms_session_events`;
CREATE TABLE  `kalturadw_ds`.`ods_fms_session_events` (
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
  `server_cpu_load` tinyint(3) unsigned NOT NULL,
  `server_memory_load` tinyint(3) unsigned NOT NULL,
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
(PARTITION p_0 VALUES IN (0) ENGINE = MyISAM
) */;

DROP TABLE IF EXISTS `kalturadw_ds`.`invalid_fms_event_lines`;
CREATE TABLE  `kalturadw_ds`.`invalid_fms_event_lines` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_adaptor`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_adaptor` (
  `adaptor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adaptor` varchar(45) NOT NULL,
  PRIMARY KEY (`adaptor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_app`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_app` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app` varchar(45) NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_app_instance`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_app_instance` (
  `app_instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_instance` varchar(45) NOT NULL,
  PRIMARY KEY (`app_instance_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_client_protocol`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_client_protocol` (
  `client_protocol_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_protocol` varchar(45) NOT NULL,
  PRIMARY KEY (`client_protocol_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_event_category`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_event_category` (
  `event_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_category` varchar(45) NOT NULL,
  PRIMARY KEY (`event_category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_event_type`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_event_type` (
  `event_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(45) NOT NULL,
  PRIMARY KEY (`event_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_status_description`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_status_description` (
  `status_description_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_description` varchar(300) DEFAULT '<unset status>',
  `status_number` smallint(3) unsigned DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`status_description_id`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_stream_type`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_stream_type` (
  `stream_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_type` varchar(45) NOT NULL,
  PRIMARY KEY (`stream_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_fms_virtual_host`;
CREATE TABLE  `kalturadw`.`dwh_dim_fms_virtual_host` (
  `virtual_host_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `virtual_host` varchar(45) NOT NULL,
  PRIMARY KEY (`virtual_host_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kalturadw`.`dwh_fact_fms_session_events`;
CREATE TABLE  `kalturadw`.`dwh_fact_fms_session_events` (
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
  `server_cpu_load` tinyint(3) unsigned NOT NULL,
  `server_memory_load` tinyint(3) unsigned NOT NULL,
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
  `server_to_client_qos_bytes` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`file_id`,`event_time`),
  KEY `partner_id_event_type_id_time` (`partner_id`,`event_type_id`,`event_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (TO_DAYS(event_time))
(PARTITION p_201001 VALUES LESS THAN (734169) ENGINE = MyISAM,
 PARTITION p_201002 VALUES LESS THAN (734197) ENGINE = MyISAM,
 PARTITION p_201003 VALUES LESS THAN (734228) ENGINE = MyISAM,
 PARTITION p_201004 VALUES LESS THAN (734258) ENGINE = MyISAM,
 PARTITION p_201005 VALUES LESS THAN (734289) ENGINE = MyISAM) */;


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