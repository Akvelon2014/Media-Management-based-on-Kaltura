DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_entry_media_type`;

CREATE TABLE `kalturadw_bisources`.`bisources_entry_media_type` (
  `entry_media_type_id` SMALLINT NOT NULL ,
  `entry_media_type_name` VARCHAR(50) DEFAULT 'missing value',

  PRIMARY KEY (`entry_media_type_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


	
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(-1,'AUTOMATIC'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(0,'ANY'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(1,'VIDEO'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(2,'IMAGE'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(3,'TEXT'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(4,'HTML'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(5,'AUDIO'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(6,'SHOW'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(7,'SHOW_XML'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(9,'BUBBLES'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(10,'XML'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(11,'DOCUMENT'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(101,'GENERIC_1'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(102,'GENERIC_2'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(103,'GENERIC_3'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(104,'GENERIC_4');
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(201, 'LIVE_STREAM_FLASH');
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(202, 'LIVE_STREAM_WINDOWS_MEDIA');
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(203, 'LIVE_STREAM_REAL_MEDIA');
INSERT INTO kalturadw_bisources.bisources_entry_media_type (entry_media_type_id,entry_media_type_name) VALUES(204, 'LIVE_STREAM_QUICKTIME'); 
