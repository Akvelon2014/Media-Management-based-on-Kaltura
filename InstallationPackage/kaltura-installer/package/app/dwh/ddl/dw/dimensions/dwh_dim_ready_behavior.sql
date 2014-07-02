/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw
*********************************************************************
*/

use `kalturadw`;

DROP TABLE IF EXISTS `dwh_dim_ready_behavior`;

CREATE TABLE `dwh_dim_ready_behavior` (
  `ready_behavior_id` SMALLINT(6) NOT NULL,
  `ready_behavior_name` VARCHAR(50) DEFAULT 'missing value',
  `dwh_creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dwh_update_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ri_ind` TINYINT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ready_behavior_id`)
) ENGINE=MYISAM;
