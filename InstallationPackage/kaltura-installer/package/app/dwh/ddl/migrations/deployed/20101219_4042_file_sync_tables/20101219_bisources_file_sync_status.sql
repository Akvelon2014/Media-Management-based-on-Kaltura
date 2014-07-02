/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_file_sync_status`;

CREATE TABLE `bisources_file_sync_status` (
  `file_sync_status_id` SMALLINT(6) NOT NULL,
  `file_sync_status_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`file_sync_status_id`)
);

INSERT INTO `bisources_file_sync_status`
			(`file_sync_status_id`,`file_sync_status_name`) 
VALUES 		(-1,'PENDING'), (1,'PENDING'),(2,'READY'),(3,'DELETED'),(4,'PURGED');
	

	