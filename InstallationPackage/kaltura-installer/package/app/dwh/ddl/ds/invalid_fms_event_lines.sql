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
) ENGINE=INNODB DEFAULT CHARSET=utf8;