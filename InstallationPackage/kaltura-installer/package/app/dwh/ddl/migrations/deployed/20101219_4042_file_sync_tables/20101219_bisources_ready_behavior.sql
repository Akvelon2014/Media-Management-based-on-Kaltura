/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_ready_behavior`;

CREATE TABLE `bisources_ready_behavior` (
  `ready_behavior_id` SMALLINT(6) NOT NULL,
  `ready_behavior_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`ready_behavior_id`)
);

INSERT INTO `bisources_ready_behavior`
			(`ready_behavior_id`,`ready_behavior_name`) 
VALUES 		(-1,'_IGNORE'),(0,'INHERIT_FLAVOR_PARAMS'),(1,'REQUIRED'),(2,'OPTIONAL');