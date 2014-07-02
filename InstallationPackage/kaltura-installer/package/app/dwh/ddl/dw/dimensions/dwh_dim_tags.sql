/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/

use kalturadw;

drop table if exists `dwh_dim_tags`;

create table `dwh_dim_tags` (
	`tag_id` int(11) NOT NULL AUTO_INCREMENT,
    `tag_name` varchar(50) NOT NULL,
	`dwh_creation_date` TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dwh_update_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`tag_id`), UNIQUE KEY (tag_name)
) ENGINE=MYISAM; 

CREATE TRIGGER `kalturadw`.`dwh_dim_tags_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_tags`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
    
 
