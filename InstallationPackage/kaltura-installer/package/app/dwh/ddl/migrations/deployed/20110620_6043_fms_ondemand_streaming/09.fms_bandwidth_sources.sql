/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw`;

DROP TABLE IF EXISTS `dwh_dim_fms_bandwidth_source`;

CREATE TABLE `dwh_dim_fms_bandwidth_source` (
  `process_id` INT(10) NOT NULL,
  `fms_app_id` SMALLINT(6) NOT NULL,
  `bandwidth_source_id` INT(11) NOT NULL,
  UNIQUE KEY (`process_id`,`fms_app_id`)
);

INSERT INTO `dwh_dim_fms_bandwidth_source`
			(`process_id`,`fms_app_id`,`bandwidth_source_id`) 
VALUES 		(2,5,5),(7,1,6);
