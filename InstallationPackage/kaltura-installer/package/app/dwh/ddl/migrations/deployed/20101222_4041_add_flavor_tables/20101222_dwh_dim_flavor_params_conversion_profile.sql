/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/
use kalturadw;

drop table if exists `dwh_dim_flavor_params_conversion_profile`;

create table `dwh_dim_flavor_params_conversion_profile` (
	`id` int (11),
	`flavor_params_id` int (11),
	`conversion_profile_id` int (11),
	`ready_behavior` tinyint (4),
	`force_none_complied` int (11),
	`created_at` datetime ,
	`updated_at` datetime ,
	`dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`ri_ind` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)	
); 
