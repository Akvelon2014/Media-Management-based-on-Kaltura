/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_fms_app`;

CREATE TABLE `bisources_fms_app` (
  `fms_app_id` SMALLINT(6) NOT NULL,
  `fms_app_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`fms_app_id`)
);

INSERT INTO `bisources_fms_app`
                        (`fms_app_id`,`fms_app_name`) 
VALUES          (1,'ondemand'),(5,'live');

