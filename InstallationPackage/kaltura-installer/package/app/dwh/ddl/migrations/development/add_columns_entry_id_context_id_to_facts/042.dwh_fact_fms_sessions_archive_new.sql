USE kalturadw;

DROP TABLE IF EXISTS dwh_fact_fms_sessions_archive_new;

CREATE TABLE `dwh_fact_fms_sessions_archive_new` (
  `session_id` varchar(20) NOT NULL,
  `session_time` datetime NOT NULL,
  `session_date_id` int(11) unsigned DEFAULT NULL,
  `bandwidth_source_id` int(11) NOT NULL,
  `session_client_ip` varchar(15) DEFAULT NULL,
  `session_client_ip_number` int(10) unsigned DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `session_partner_id` int(10) unsigned DEFAULT NULL,
  `total_bytes` bigint(20) unsigned DEFAULT NULL,
  `entry_id` varchar(20) DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=latin1
/*!50100 PARTITION BY RANGE (session_date_id)
(PARTITION p_0 VALUES LESS THAN (1) ENGINE = ARCHIVE)*/;
