/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/

use `kalturadw`;

DROP TABLE IF EXISTS `dwh_dim_conversion_profile`;

CREATE TABLE `dwh_dim_conversion_profile` (
	`id` INT (11),
	`partner_id` INT (11),
	`name` VARCHAR (384),
	`created_at` datetime ,
	`updated_at` datetime ,
	`deleted_at` datetime ,
	`description` VARCHAR (3072),
	`clip_start` INT (11),
	`clip_duration` INT (11),
	`input_tags_map` VARCHAR (3069),
	`creation_mode` SMALLINT (6),
	`dwh_creation_date` TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dwh_update_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`ri_ind` TINYINT(4)  NOT NULL DEFAULT 0 ,
	PRIMARY KEY (`id`)
); 
