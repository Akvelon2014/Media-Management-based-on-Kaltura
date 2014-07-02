/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_batch_job_error_type`;

CREATE TABLE `bisources_batch_job_error_type` (
  `batch_job_error_type_id` int(11) NOT NULL,
  `batch_job_error_type_name` VARCHAR(100) DEFAULT 'missing value',
  PRIMARY KEY (`batch_job_error_type_id`)
);

INSERT INTO `bisources_batch_job_error_type`
			(`batch_job_error_type_id`,`batch_job_error_type_name`) 
VALUES 	(0, 'PENDING'),
		(1, 'QUEUED'),
		(2, 'PROCESSING'),
		(3, 'PROCESSED'),
		(4, 'MOVEFILE'),
		(5, 'FINISHED'),
		(6, 'FAILED'),
		(7, 'ABORTED'),
		(8, 'ALMOST_DONE'),
		(9, 'RETRY'),
		(10, 'FATAL'),
		(11, 'DONT_PROCESS');