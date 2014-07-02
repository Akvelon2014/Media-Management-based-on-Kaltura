/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_file_sync_object_type`;

CREATE TABLE `bisources_file_sync_object_type` (
  `file_sync_object_type_id` SMALLINT(6) NOT NULL,
  `file_sync_object_type_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`file_sync_object_type_id`)
);

INSERT INTO `bisources_file_sync_object_type`
			(`file_sync_object_type_id`,`file_sync_object_type_name`) 
VALUES 		(1,'ENTRY'),(2,'UICONF'),(3,'BATCHJOB'),(4,'FLAVOR_ASSET'), (5,'METADATA'), (6,'METADATA_PROFILE');