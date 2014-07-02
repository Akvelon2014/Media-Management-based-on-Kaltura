/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_batch_job_type`;

CREATE TABLE `bisources_batch_job_type` (
  `batch_job_type_id` INT(11) NOT NULL,
  `batch_job_type_name` VARCHAR(100) DEFAULT 'missing value',
  PRIMARY KEY (`batch_job_type_id`)
);

INSERT INTO `bisources_batch_job_type`
			(`batch_job_type_id`,`batch_job_type_name`) 
VALUES 		(0,'CONVERT'),
			(1,'IMPORT'),
			(2,'DELETE'),
			(3,'FLATTEN'), 
			(4,'BULKUPLOAD'),
			(5,'DVDCREATOR'),
			(6,'DOWNLOAD'),
			(7,'OOCONVERT'),
			(10,'CONVERT_PROFILE'),
			(11,'POSTCONVERT'),
			(12,'PULL'),
			(13,'REMOTE_CONVERT'),
			(14,'EXTRACT_MEDIA'),
			(15,'MAIL'),
			(16,'NOTIFICATION'),
			(17,'CLEANUP'),
			(18,'SCHEDULER_HELPER'),
			(19,'BULKDOWNLOAD'),
			(20,'DB_CLEANUP'),
			(21,'PROVISION_PROVIDE'),
			(22,'CONVERT_COLLECTION'),
			(23,'STORAGE_EXPORT'),
			(24,'PROVISION_DELETE'),
			(25,'STORAGE_DELETE'),
			(26,'EMAIL_INGESTION'),
			(27,'METADATA_IMPORT'),
			(28,'METADATA_TRANSFORM'),
			(29,'FILESYNC_IMPORT'),
			(10001, 'BatchJobType.VirusScan.virusScan'),
			(10011, 'entryStatus.Infected.virusScan'),
			(10021, 'conversionEngineType.QuickTimeTools.quickTimeTools'),
			(10031, 'conversionEngineType.FastStart.fastStart'),
			(10041, 'conversionEngineType.FastStart.fastStart'),
			(10051, 'conversionEngineType.FastStart.fastStart'),
			(10061, 'conversionEngineType.ExpressionEncoder.expressionEncoder');