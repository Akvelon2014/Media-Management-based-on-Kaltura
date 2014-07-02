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

USE `kalturadw_ds`;

CREATE TABLE `ds_bandwidth_usage` (
  `line_number` INT (10) DEFAULT NULL,
  `cycle_id` INT(11) NOT NULL,
  `file_id` INT(11) NOT NULL,
  `partner_id` INT(11) NOT NULL default -1,
  `activity_date_id` INT(11) DEFAULT '-1',
  `activity_hour_id` TINYINT(4) DEFAULT '-1',
  `bandwidth_source_id` BIGINT(20) DEFAULT NULL,
  `url` varchar(2000) default null,
  `bandwidth_bytes` BIGINT(20) DEFAULT '0',
  `user_ip` VARCHAR(15) DEFAULT NULL,
  `user_ip_number` INT(10) UNSIGNED DEFAULT NULL,
  `country_id` INT(11) DEFAULT NULL,
  `location_id` INT(11) DEFAULT NULL,
  `os_id` int(11),
  `browser_id` int(11),
  `entry_id` varchar(20)
) ENGINE=INNODB DEFAULT CHARSET=utf8
PARTITION BY LIST (cycle_id)
(PARTITION p_0 VALUES IN (0) ENGINE = INNODB);
 
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
