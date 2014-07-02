/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_asset_status`;

CREATE TABLE `bisources_asset_status` (
  `asset_status_id` SMALLINT(6) NOT NULL,
  `asset_status_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`asset_status_id`)
);

INSERT INTO `bisources_asset_status`
			(`asset_status_id`,`asset_status_name`) 
VALUES 		(-1,'ERROR'),(0,'QUEUED'),(1,'CONVERTING'),(2,'READY'),(3,'DELETED'),(4,'NOT_APPLICABLE');