/*
SQLyog Community v8.3 
MySQL - 5.1.41-3ubuntu12.3 : Database - kalturadw_ds
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `kalturadw_ds`;

/*Table structure for table `staging_areas` */

DROP TABLE IF EXISTS `staging_areas`;

CREATE TABLE `staging_areas` (
  `id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  `source_table` varchar(45) NOT NULL,
  `target_table` varchar(45) NOT NULL,
  `on_duplicate_clause` varchar(4000) DEFAULT NULL,
  `staging_partition_field` varchar(45) DEFAULT NULL,
  `post_transfer_sp` varchar(500) DEFAULT NULL,
  `aggr_date_field` varchar(45),
  `hour_id_field` VARCHAR(45),
  `post_transfer_aggregations` VARCHAR(255),
  `reset_aggregations_min_date` DATE NOT NULL DEFAULT '1970-01-01',
  `ignore_duplicates_on_transfer` BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
