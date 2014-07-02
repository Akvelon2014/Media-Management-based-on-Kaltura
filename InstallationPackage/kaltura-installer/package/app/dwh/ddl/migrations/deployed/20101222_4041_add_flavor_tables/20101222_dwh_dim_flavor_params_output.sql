/*
SQLyog Community v8.7 
MySQL - 5.1.47 
*********************************************************************
*/

USE kalturadw;

DROP TABLE IF EXISTS dwh_dim_flavor_params_output;

create table dwh_dim_flavor_params_output (
	id int (11) NOT NULL,
	flavor_params_id int (11),
	flavor_params_version int (11),
	partner_id int (11),
	entry_id varchar (60),
	flavor_asset_id varchar (60),
	flavor_asset_version varchar (60),
	name varchar (384),
	tags blob ,
	description varchar (3072),
	ready_behavior tinyint (4),
	created_at datetime ,
	updated_at datetime ,
	deleted_at datetime ,
	is_default tinyint (4),
	flavor_format_id int(11),
	video_codec_id int (11),
	video_bitrate int (11),
	audio_codec_id int (11),
	audio_bitrate int (11),
	audio_channels tinyint (4),
	audio_sample_rate int (11),
	audio_resolution int (11),
	width int (11),
	height int (11),
	frame_rate float ,
	gop_size int (11),
	two_pass int (11),
	conversion_engines varchar (3072),
	conversion_engines_extra_params varchar (3072),
	custom_data blob ,
	command_lines varchar (6141),
	file_ext varchar (12),
	deinterlice int (11),
	rotate int (11),
	operators blob ,
	engine_version smallint (6),
	dwh_creation_date TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
	dwh_update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	ri_ind TINYINT(4)  NOT NULL DEFAULT 0 ,
	PRIMARY KEY (id)
); 
