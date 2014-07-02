/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `kalturadw`;

/*Table structure for table `dwh_qos_reports` */

DROP TABLE IF EXISTS `dwh_qos_reports`;

CREATE TABLE `dwh_qos_reports` (
  `measure` varchar(50) DEFAULT NULL,
  `classification` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `yesterday` decimal(15,2) DEFAULT NULL,
  `the_day_before` decimal(15,2) DEFAULT NULL,
  `diff` decimal(15,2) DEFAULT NULL,
  `last_5_days_avg` decimal(15,2) DEFAULT NULL,
  `last_30_days_avg` decimal(15,2) DEFAULT NULL,
  `outer_order` int(11) DEFAULT NULL,
  `inner_order` int(11) DEFAULT NULL,
  UNIQUE KEY `m_c_d_key` (`measure`,`classification`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
