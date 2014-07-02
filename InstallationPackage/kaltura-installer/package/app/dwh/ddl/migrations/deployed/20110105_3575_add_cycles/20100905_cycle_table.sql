/*
SQLyog Community Edition- MySQL GUI v8.12 
MySQL - 5.1.47 : Database - kalturadw_ds
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


USE `kalturadw_ds`;

/*Table structure for table `files` */

CREATE TABLE `cycles` (
  `cycle_id` INT(11) NOT NULL AUTO_INCREMENT,
  `status` VARCHAR(60) DEFAULT NULL,
  `prev_status` VARCHAR(60) DEFAULT NULL,
  `insert_time` DATETIME DEFAULT NULL,
  `run_time` DATETIME DEFAULT NULL,
  `transfer_time` DATETIME DEFAULT NULL,
  `process_id` INT(11) DEFAULT '1',
  PRIMARY KEY (`cycle_id`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
