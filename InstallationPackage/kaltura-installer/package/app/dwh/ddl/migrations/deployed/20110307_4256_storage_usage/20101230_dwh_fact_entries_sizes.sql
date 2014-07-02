/*
SQLyog Community v8.3 
MySQL - 5.1.41-3ubuntu12.6 : Database - kalturadw
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

USE `kalturadw`;

/*Table structure for table `dwh_fact_storage_usage` */

DROP TABLE IF EXISTS `dwh_fact_entries_sizes`;

CREATE TABLE `dwh_fact_entries_sizes` (
`partner_id` INT(11) NOT NULL,
`entry_id` VARCHAR(20) NOT NULL,
`entry_size_date` DATETIME NOT NULL,
`entry_size_date_id` INT(11) NOT NULL,
`entry_additional_size_kb` DECIMAL(15,3) NOT NULL,
PRIMARY KEY `partner_id_entry_id_entry_size_date_id` (`partner_id`,`entry_id`,`entry_size_date_id`))
ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (entry_size_date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201));


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

