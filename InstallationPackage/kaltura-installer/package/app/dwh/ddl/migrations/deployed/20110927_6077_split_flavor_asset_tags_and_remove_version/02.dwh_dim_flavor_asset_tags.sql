/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/

use kalturadw;

drop table if exists `dwh_dim_flavor_asset_tags`;

create table `dwh_dim_flavor_asset_tags` (
	`flavor_asset_id` varchar(60) NOT NULL,
        `tag_id` int(11) NOT NULL,
	`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`ri_ind` TINYINT(4)  NOT NULL DEFAULT 0 ,
	UNIQUE (`flavor_asset_id`, `tag_id`)
) ENGINE=MYISAM; 
