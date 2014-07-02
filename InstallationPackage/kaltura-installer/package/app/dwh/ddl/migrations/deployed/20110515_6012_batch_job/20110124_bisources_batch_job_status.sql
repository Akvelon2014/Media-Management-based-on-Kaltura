/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_batch_job_status`;

CREATE TABLE `bisources_batch_job_status` (
  `batch_job_status_id` int(11) NOT NULL,
  `batch_job_status_name` VARCHAR(100) DEFAULT 'missing value',
  PRIMARY KEY (`batch_job_status_id`)
);

INSERT INTO `bisources_batch_job_status`
			(`batch_job_status_id`,`batch_job_status_name`) 
VALUES 	(0, 'APP'),
		(1, 'RUNTIME'),
		(2, 'HTTP'),
		(3, 'CURL'),
		(4, 'KALTURA_API'),
		(5, 'KALTURA_CLIENT');