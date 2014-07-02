DROP TABLE IF EXISTS `kalturadw_ds`.`fms_incomplete_sessions`;

CREATE TABLE `kalturadw_ds`.`fms_incomplete_sessions` (
  `session_id` varchar(20) DEFAULT NULL,
  `session_time` datetime DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `session_date_id` int(11) unsigned DEFAULT NULL,
  `session_client_ip` VARCHAR(15) DEFAULT NULL,
  `session_client_ip_number` INT(10) UNSIGNED DEFAULT NULL,
  `country_id` INT(11) DEFAULT NULL,
  `location_id` INT(11) DEFAULT NULL,
  `con_cs_bytes` bigint(20) unsigned DEFAULT NULL,
  `con_sc_bytes` bigint(20) unsigned DEFAULT NULL,
  `dis_cs_bytes` bigint(20) unsigned DEFAULT NULL,
  `dis_sc_bytes` bigint(20) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `bandwidth_source_id` INT(11) NOT NULL DEFAULT 5, 
  is_connected_ind int(11),
  is_disconnected_ind int(11),
  PRIMARY KEY (session_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
