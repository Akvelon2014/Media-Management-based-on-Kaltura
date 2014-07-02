/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_creation_mode`;

CREATE TABLE `bisources_creation_mode` (
  `creation_mode_id` SMALLINT(6) NOT NULL,
  `creation_mode_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`creation_mode_id`)
);

INSERT INTO `bisources_creation_mode`
			(`creation_mode_id`,`creation_mode_name`) 
VALUES 		(-1,''),(1,'MANUAL'),(2,'KMC'),(3,'AUTOMATIC'),(4,'AUTOMATIC_BYPASS_FLV');