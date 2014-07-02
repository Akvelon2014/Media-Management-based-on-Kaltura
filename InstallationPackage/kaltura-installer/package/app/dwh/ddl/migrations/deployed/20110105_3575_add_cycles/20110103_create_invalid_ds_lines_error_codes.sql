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

/*Table structure for table `invalid_ds_lines_error_codes` */

DROP TABLE IF EXISTS `invalid_ds_lines_error_codes`;

CREATE TABLE `invalid_ds_lines_error_codes` (
  `error_code_id` SMALLINT(6) NOT NULL AUTO_INCREMENT,
  `error_code_reason` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`error_code_id`),
  UNIQUE KEY `error_code_reason` (`error_code_reason`)
) ENGINE=MYISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
