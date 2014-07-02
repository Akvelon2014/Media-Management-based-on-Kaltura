/*
SQLyog Community v8.7 
MySQL - 5.1.37-log 
*********************************************************************
*/

use kalturadw;

DROP TABLE IF EXISTS dwh_dim_media_info;

create table dwh_dim_media_info (
	id int (11) not null,
	created_at datetime ,
	updated_at datetime ,
	flavor_asset_id varchar (60),
	file_size int (11),
	container_format_id int (11),
	container_id varchar (381),
	container_profile varchar (381),
	container_duration int (11),
	container_bit_rate int (11),
	video_format_id int(11),
	video_codec_id int(11),
	video_duration int (11),
	video_bit_rate int (11),
	video_bit_rate_mode tinyint (4),
	video_width int (11),
	video_height int (11),
	video_frame_rate float ,
	video_dar float ,
	video_rotation int (11),
	audio_format_id int (11),
	audio_codec_id int (11),
	audio_duration int (11),
	audio_bit_rate int (11),
	audio_bit_rate_mode tinyint (4),
	audio_channels tinyint (4),
	audio_sampling_rate int (11),
	audio_resolution int (11),
	writing_lib varchar (381),
	custom_data blob ,
	raw_data blob ,
	multi_stream_info varchar (3069),
	flavor_asset_version varchar (60),
	scan_type int (11),
	multi_stream varchar (765),
	dwh_creation_date TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
	dwh_update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	ri_ind TINYINT(4)  NOT NULL DEFAULT 0 ,
	PRIMARY KEY (id),
	KEY dwh_update_date (dwh_update_date)
) ENGINE=MYISAM; 
