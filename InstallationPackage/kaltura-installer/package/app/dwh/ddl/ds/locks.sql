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

/*Table structure for table `locks` */

DROP TABLE IF EXISTS `locks`;

CREATE TABLE `locks` (
  `lock_id` INT(11) NOT NULL AUTO_INCREMENT,
  `lock_name` varchar(265) DEFAULT NULL,
  `lock_time` datetime DEFAULT NULL,
  `lock_state` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`lock_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
