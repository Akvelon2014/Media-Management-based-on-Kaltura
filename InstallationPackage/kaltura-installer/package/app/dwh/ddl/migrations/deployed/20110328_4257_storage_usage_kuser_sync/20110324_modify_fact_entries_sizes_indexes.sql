/*
SQLyog Community v8.7 
MySQL - 5.1.37-log : Database - kalturadw
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `kalturadw`;

/*Table structure for table `dwh_fact_entries_sizes` */

CREATE TABLE `dwh_fact_entries_sizes_new` (
  `partner_id` int(11) NOT NULL,
  `entry_id` varchar(20) NOT NULL,
  `entry_size_date` datetime NOT NULL,
  `entry_size_date_id` int(11) NOT NULL,
  `entry_additional_size_kb` decimal(15,3) NOT NULL,
  PRIMARY KEY (`entry_size_date_id`, `partner_id`,`entry_id`),
  KEY entry_id (`entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (entry_size_date_id)
(PARTITION p_201001 VALUES LESS THAN (20100201) ENGINE = MyISAM,
 PARTITION p_201002 VALUES LESS THAN (20100301) ENGINE = MyISAM,
 PARTITION p_201003 VALUES LESS THAN (20100401) ENGINE = MyISAM,
 PARTITION p_201004 VALUES LESS THAN (20100501) ENGINE = MyISAM,
 PARTITION p_201005 VALUES LESS THAN (20100601) ENGINE = MyISAM,
 PARTITION p_201006 VALUES LESS THAN (20100701) ENGINE = MyISAM,
 PARTITION p_201007 VALUES LESS THAN (20100801) ENGINE = MyISAM,
 PARTITION p_201008 VALUES LESS THAN (20100901) ENGINE = MyISAM,
 PARTITION p_201009 VALUES LESS THAN (20101001) ENGINE = MyISAM,
 PARTITION p_201010 VALUES LESS THAN (20101101) ENGINE = MyISAM,
 PARTITION p_201011 VALUES LESS THAN (20101201) ENGINE = MyISAM,
 PARTITION p_201012 VALUES LESS THAN (20110101) ENGINE = MyISAM,
 PARTITION p_201101 VALUES LESS THAN (20110201) ENGINE = MyISAM,
 PARTITION p_201102 VALUES LESS THAN (20110301) ENGINE = MyISAM,
 PARTITION p_201103 VALUES LESS THAN (20110401) ENGINE = MyISAM,
 PARTITION p_201104 VALUES LESS THAN (20110501) ENGINE = MyISAM) */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

CALL add_partition_for_table('dwh_fact_entries_sizes_new');

INSERT INTO kalturadw.dwh_fact_entries_sizes_new
SELECT * FROM kalturadw.dwh_fact_entries_sizes;

RENAME TABLE kalturadw.dwh_fact_entries_sizes to kalturadw.dwh_fact_entries_sizes_old;
RENAME TABLE kalturadw.dwh_fact_entries_sizes_new to kalturadw.dwh_fact_entries_sizes;
DROP TABLE kalturadw.dwh_fact_entries_sizes_old;
