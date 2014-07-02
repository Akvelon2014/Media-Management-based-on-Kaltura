/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/

USE kalturadw;

DROP TABLE IF EXISTS `dwh_dim_flavor_params`;

CREATE TABLE `dwh_dim_flavor_params` (
	`dwh_id` int(11) NOT NULL AUTO_INCREMENT,
	`id` 	int(11) NOT NULL,
	`version` int (11),
	`partner_id` INT (11),
	`name` VARCHAR (384),
	`tags` BLOB ,
	`description` VARCHAR (3072),
	`ready_behavior` TINYINT (4),
	`created_at` datetime ,
	`updated_at` datetime ,
	`deleted_at` datetime ,
	`is_default` TINYINT (4),
	`flavor_format_id` INT(11),
	`video_codec_id` INT (11),
	`video_bitrate` INT (11),
	`audio_codec_id` INT(11),
	`audio_bitrate` INT (11),
	`audio_channels` TINYINT (4),
	`audio_sample_rate` INT (11),
	`audio_resolution` INT (11),
	`width` INT (11),
	`height` INT (11),
	`frame_rate` FLOAT ,
	`gop_size` INT (11),
	`two_pass` INT (11),
	`conversion_engines` VARCHAR (3072),
	`conversion_engines_extra_params` VARCHAR (3072),
	`view_order` INT (11),
	`bypass_by_extension` VARCHAR (96),
	`creation_mode` SMALLINT (6),
	`deinterlice` INT (11),
	`rotate` INT (11),
	`operators` BLOB ,
	`engine_version` SMALLINT (6),
	`dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`ri_ind` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`dwh_id`),
	UNIQUE KEY `id_version` (`id`,`version`)
); 
