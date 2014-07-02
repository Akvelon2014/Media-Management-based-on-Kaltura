/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

use kalturadw;

drop table if exists `dwh_dim_file_sync`;

create table `dwh_dim_file_sync` (
	`id` int (11),
	`partner_id` int (11),
	`object_type` tinyint (4),
	`object_id` varchar (60),
	`version` varchar (60),
	`object_sub_type` tinyint (4),
	`dc` varchar (6),
	`original` tinyint (4),
	`created_at` datetime ,
	`updated_at` datetime ,
	`ready_at` datetime ,
	`sync_time` int (11),
	`status` tinyint (4),
	`file_type` tinyint (4),
	`linked_id` int (11),
	`link_count` int (11),
	`file_root` varchar (192),
	`file_path` varchar (384),
	`file_size` bigint (20),  
	`dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`ri_ind` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `unique_key` (`object_type`,`object_id`,`version`,`object_sub_type`,`dc`),
	KEY `object_id_object_type_version_subtype_index` (`object_id`,`object_type`,`version`,`object_sub_type`),
	KEY `partner_id_object_id_object_type_index` (`partner_id`,`object_id`,`object_type`)
); 
