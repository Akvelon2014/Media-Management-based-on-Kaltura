USE `kalturadw`;

/*Table structure for table `dwh_dim_fms_adaptor` */

CREATE TABLE `dwh_dim_fms_adaptor` (
  `adaptor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adaptor` varchar(45) NOT NULL,
  PRIMARY KEY (`adaptor_id`),
  UNIQUE KEY (`adaptor`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_app` */

CREATE TABLE `dwh_dim_fms_app` (
  `fms_app_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fms_app_name` varchar(45) NOT NULL,
  `dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ri_ind` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`fms_app_id`),
  UNIQUE KEY (`fms_app_name`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_app_instance` */

CREATE TABLE `dwh_dim_fms_app_instance` (
  `app_instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_instance` varchar(333) NOT NULL,
  PRIMARY KEY (`app_instance_id`),
  UNIQUE KEY (`app_instance`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_client_protocol` */

CREATE TABLE `dwh_dim_fms_client_protocol` (
  `client_protocol_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_protocol` varchar(45) NOT NULL,
  PRIMARY KEY (`client_protocol_id`),
  UNIQUE KEY (`client_protocol`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_event_category` */

CREATE TABLE `dwh_dim_fms_event_category` (
  `event_category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_category` varchar(45) NOT NULL,
  PRIMARY KEY (`event_category_id`),
  UNIQUE KEY (`event_category`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_event_type` */

CREATE TABLE `dwh_dim_fms_event_type` (
  `event_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(45) NOT NULL,
  PRIMARY KEY (`event_type_id`),
  UNIQUE KEY (`event_type`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_status_description` */

CREATE TABLE `dwh_dim_fms_status_description` (
  `status_description_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_description` varchar(300) DEFAULT '<unset status>',
  `status_number` smallint(3) unsigned DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`status_description_id`)
) ENGINE=MyISAM AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_stream_type` */

CREATE TABLE `dwh_dim_fms_stream_type` (
  `stream_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_type` varchar(45) NOT NULL,
  PRIMARY KEY (`stream_type_id`),
  UNIQUE KEY (`stream_type`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `dwh_dim_fms_virtual_host` */

CREATE TABLE `dwh_dim_fms_virtual_host` (
  `virtual_host_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `virtual_host` varchar(45) NOT NULL,
  PRIMARY KEY (`virtual_host_id`),
  UNIQUE KEY (`virtual_host`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE `dwh_dim_fms_bandwidth_source` (
  `process_id` INT(10) NOT NULL,
  `fms_app_id` SMALLINT(6) NOT NULL,
  `bandwidth_source_id` INT(11) NOT NULL,
  `file_regex` VARCHAR(100) NOT NULL DEFAULT '.*',
  UNIQUE KEY (`process_id`,`fms_app_id`, `file_regex`)
);

INSERT INTO `dwh_dim_fms_bandwidth_source`
			(`process_id`,`fms_app_id`,`bandwidth_source_id`,`file_regex`) 
VALUES 		(2,5,5,'.*'),(7,1,6,'_77658\\.|_86593\\.'),(7,1,7,'_105515\\.');