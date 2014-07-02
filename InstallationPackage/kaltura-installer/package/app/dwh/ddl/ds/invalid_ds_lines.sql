/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_ds
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `kalturadw_ds`;

/*Table structure for table `invalid_event_lines` */

DROP TABLE IF EXISTS `invalid_ds_lines`;
		      
CREATE TABLE `invalid_ds_lines` (
  `line_number` INT(11) DEFAULT NULL,
  `file_id` INT(11) NOT NULL,
  `error_reason_code` SMALLINT(6) DEFAULT NULL,
  `ds_line` VARCHAR(4096) DEFAULT NULL,
  `insert_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_id` INT(11) DEFAULT NULL,
  `partner_id` VARCHAR(20) DEFAULT NULL,
  `cycle_id` INT(11) DEFAULT NULL,
  `process_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (file_id, line_number)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
