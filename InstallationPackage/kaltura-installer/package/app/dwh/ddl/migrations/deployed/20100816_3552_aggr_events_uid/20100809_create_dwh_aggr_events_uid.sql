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

/*Table structure for table `dwh_aggr_events_uid` */

DROP TABLE IF EXISTS `dwh_aggr_events_uid`;

CREATE TABLE `dwh_aggr_events_uid` (
  `partner_id` INT(11) NOT NULL DEFAULT '0',
  `date_id` INT(11) NOT NULL DEFAULT '0',
  `uid` VARCHAR(64) NOT NULL DEFAULT '0',
  `sum_time_viewed` DECIMAL(20,3) DEFAULT NULL,
  `count_time_viewed` INT(11) DEFAULT NULL,
  `count_plays` INT(11) DEFAULT NULL,
  `count_loads` INT(11) DEFAULT NULL,
  `count_plays_25` INT(11) DEFAULT NULL,
  `count_plays_50` INT(11) DEFAULT NULL,
  `count_plays_75` INT(11) DEFAULT NULL,
  `count_plays_100` INT(11) DEFAULT NULL,
  `count_edit` INT(11) DEFAULT NULL,
  `count_viral` INT(11) DEFAULT NULL,
  `count_download` INT(11) DEFAULT NULL,
  `count_report` INT(11) DEFAULT NULL,
  `count_buf_start` INT(11) DEFAULT NULL,
  `count_buf_end` INT(11) DEFAULT NULL,
  `count_open_full_screen` INT(11) DEFAULT NULL,
  `count_close_full_screen` INT(11) DEFAULT NULL,
  `count_replay` INT(11) DEFAULT NULL,
  `count_seek` INT(11) DEFAULT NULL,
  `count_open_upload` INT(11) DEFAULT NULL,
  `count_save_publish` INT(11) DEFAULT NULL,
  `count_close_editor` INT(11) DEFAULT NULL,
  `count_pre_bumper_played` INT(11) DEFAULT NULL,
  `count_post_bumper_played` INT(11) DEFAULT NULL,
  `count_bumper_clicked` INT(11) DEFAULT NULL,
  `count_preroll_started` INT(11) DEFAULT NULL,
  `count_midroll_started` INT(11) DEFAULT NULL,
  `count_postroll_started` INT(11) DEFAULT NULL,
  `count_overlay_started` INT(11) DEFAULT NULL,
  `count_preroll_clicked` INT(11) DEFAULT NULL,
  `count_midroll_clicked` INT(11) DEFAULT NULL,
  `count_postroll_clicked` INT(11) DEFAULT NULL,
  `count_overlay_clicked` INT(11) DEFAULT NULL,
  `count_preroll_25` INT(11) DEFAULT NULL,
  `count_preroll_50` INT(11) DEFAULT NULL,
  `count_preroll_75` INT(11) DEFAULT NULL,
  `count_midroll_25` INT(11) DEFAULT NULL,
  `count_midroll_50` INT(11) DEFAULT NULL,
  `count_midroll_75` INT(11) DEFAULT NULL,
  `count_postroll_25` INT(11) DEFAULT NULL,
  `count_postroll_50` INT(11) DEFAULT NULL,
  `count_postroll_75` INT(11) DEFAULT NULL,
  PRIMARY KEY (`partner_id`,`date_id`,`uid`),
  KEY `uid` (`uid`,`partner_id`,`date_id`),
  KEY `date_id` (`date_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MyISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MyISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MyISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MyISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MyISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MyISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MyISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MyISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MyISAM) */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
