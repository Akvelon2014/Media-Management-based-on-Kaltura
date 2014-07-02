USE kalturadw;

DROP TABLE IF EXISTS `dwh_fact_bandwidth_usage_archive_new`;
CREATE TABLE `dwh_fact_bandwidth_usage_archive_new` (
  `file_id` int(11) NOT NULL,
  `line_number` int(11) DEFAULT NULL,
  `partner_id` int(11) NOT NULL DEFAULT '-1',
  `activity_date_id` int(11) DEFAULT '-1',
  `activity_hour_id` tinyint(4) DEFAULT '-1',
  `bandwidth_source_id` bigint(20) DEFAULT NULL,
  `url` varchar(2000) DEFAULT NULL,
  `bandwidth_bytes` bigint(20) DEFAULT '0',
  `user_ip` varchar(15) DEFAULT NULL,
  `user_ip_number` int(10) unsigned DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `os_id` int(11) DEFAULT NULL,
  `browser_id` int(11) DEFAULT NULL,
  `entry_id` varchar(20) DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (activity_date_id)
(PARTITION p_0 VALUES LESS THAN (1) ENGINE = ARCHIVE)*/;


