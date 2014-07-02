use kalturadw;
DROP TABLE IF EXISTS dwh_fact_bandwidth_usage_archive;
CREATE TABLE `dwh_fact_bandwidth_usage_archive` (
  `file_id` INT(11) NOT NULL,
  `line_number` INT(11) DEFAULT NULL,
  `partner_id` INT(11) NOT NULL DEFAULT '-1',
  `activity_date_id` INT(11) DEFAULT '-1',
  `activity_hour_id` TINYINT(4) DEFAULT '-1',
  `bandwidth_source_id` BIGINT(20) DEFAULT NULL,
  `url` VARCHAR(2000) DEFAULT NULL,
  `bandwidth_bytes` BIGINT(20) DEFAULT '0',
  `user_ip` VARCHAR(15) DEFAULT NULL,
  `user_ip_number` INT(10) UNSIGNED DEFAULT NULL,
  `country_id` INT(11) DEFAULT NULL,
  `location_id` INT(11) DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (activity_date_id)
(PARTITION p_0 VALUES LESS THAN (1) ENGINE = ARCHIVE)*/
