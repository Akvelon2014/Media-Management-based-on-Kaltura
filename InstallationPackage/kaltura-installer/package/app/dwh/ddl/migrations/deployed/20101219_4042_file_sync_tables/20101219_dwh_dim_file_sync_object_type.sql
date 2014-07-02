/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw
*********************************************************************
*/

USE `kalturadw`;

DROP TABLE IF EXISTS `dwh_dim_file_sync_object_type`;

CREATE TABLE `dwh_dim_file_sync_object_type` (
  `file_sync_object_type_id` SMALLINT(6) NOT NULL,
  `file_sync_object_type_name` VARCHAR(50) DEFAULT 'missing value',
  `dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ri_ind` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_sync_object_type_id`)
)

