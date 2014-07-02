/*
SQLyog Community v8.7 
MySQL - 5.1.37-log : Database - kaltura
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `kalturadw`;

/*Table structure for table `batch_job` */

DROP TABLE IF EXISTS `dwh_dim_batch_job`;

CREATE TABLE `dwh_dim_batch_job` (
  `dwh_id` INT(11) NOT NULL AUTO_INCREMENT,
  `id` INT(11) NOT NULL,
  `job_type_id` SMALLINT(6) DEFAULT NULL,
  `job_sub_type_id` SMALLINT(6) DEFAULT NULL,
  `data` varchar(8192) DEFAULT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `duplication_key` VARCHAR(41) DEFAULT NULL,
  `status_id` INT(11) DEFAULT NULL,
  `abort` TINYINT(4) DEFAULT NULL,
  `check_again_timeout` INT(11) DEFAULT NULL,
  `progress` TINYINT(4) DEFAULT NULL,
  `message` VARCHAR(1024) DEFAULT NULL,
  `description` VARCHAR(1024) DEFAULT NULL,
  `updates_count` SMALLINT(6) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(20),
  `updated_at` DATETIME DEFAULT NULL,
  `updated_by` VARCHAR(20),
  `deleted_at` DATETIME DEFAULT NULL,
  `priority` TINYINT(4),
  `work_group_id` INT(11),
  `queue_time` DATETIME DEFAULT NULL,
  `finish_time` DATETIME DEFAULT NULL,
  `entry_id` VARCHAR(20) DEFAULT NULL,
  `partner_id` INT(11) DEFAULT NULL,
  `subp_id` INT(11) DEFAULT NULL,
  `scheduler_id` INT(11) DEFAULT NULL,
  `worker_id` INT(11) DEFAULT NULL,
  `batch_index` INT(11) DEFAULT NULL,
  `last_scheduler_id` INT(11) DEFAULT NULL,
  `last_worker_id` INT(11) DEFAULT NULL,
  `last_worker_remote` INT(11) DEFAULT '0',
  `processor_name` VARCHAR(64) DEFAULT NULL,
  `processor_expiration` DATETIME DEFAULT NULL,
  `parent_job_id` INT(11) DEFAULT NULL,
  `processor_location` VARCHAR(64) DEFAULT NULL,
  `execution_attempts` TINYINT(4) DEFAULT NULL,
  `lock_version` INT(11) DEFAULT NULL,
  `twin_job_id` INT(11) DEFAULT NULL,
  `bulk_job_id` INT(11) DEFAULT NULL,
  `root_job_id` INT(11) DEFAULT NULL,
  `dc` VARCHAR(2) DEFAULT NULL,
  `error_type_id` INT(11) DEFAULT '0',
  `err_number` INT(11) DEFAULT '0',
  `on_stress_divert_to` INT(11) DEFAULT '0',
  `dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ri_ind` TINYINT(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dwh_id`),
  UNIQUE KEY(`id`),
  KEY `entry_id_index_id` (`entry_id`,`id`),
  KEY `status_job_type_index` (`status_id`,`job_type_id`),
  KEY `created_at_job_type_status_index` (`created_at`,`job_type_id`,`status_id`),
  KEY `partner_type_index` (`partner_id`,`job_type_id`),
  KEY `partner_id_index` (`partner_id`),
  KEY `work_group_id_index_priority` (`work_group_id`,`priority`),
  KEY `twin_job_id_index` (`twin_job_id`),
  KEY `bulk_job_id_index` (`bulk_job_id`),
  KEY `root_job_id_index` (`root_job_id`),
  KEY `parent_job_id_index` (`parent_job_id`),
  KEY `duplication_status_created_index` (`duplication_key`,`status_id`,`created_at`)
) ENGINE=MYISAM AUTO_INCREMENT=160030683 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
