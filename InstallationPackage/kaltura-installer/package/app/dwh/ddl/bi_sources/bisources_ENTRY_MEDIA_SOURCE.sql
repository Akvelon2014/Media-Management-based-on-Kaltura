DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_entry_media_source`;

CREATE TABLE `kalturadw_bisources`.`bisources_entry_media_source` (
  `entry_media_source_id` SMALLINT NOT NULL ,
  `entry_media_source_name` VARCHAR(25) DEFAULT 'missing value',

  PRIMARY KEY (`entry_media_source_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(-1,'UNKNOWN');
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(0,'OTHER');	
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(1,'FILE'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(2,'WEBCAM'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(3,'FLICKR'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(4,'YOUTUBE'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(5,'URL'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(6,'TEXT'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(7,'MYSPACE'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(8,'PHOTOBUCKET'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(9,'JAMENDO'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(10,'CCMIXTER'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(11,'NYPL'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(12,'CURRENT'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(13,'COMMONS'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(20,'KALTURA'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(21,'KALTURA_USER_CLIPS'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(22,'ARCHIVE_ORG'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(23,'KALTURA_PARTNER'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(24,'METACAFE'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(25,'KALTURA_QA'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(26,'KALTURA_KSHOW'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(27,'KALTURA_PARTNER_KSHOW'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(28,'SEARCH_PROXY'); 
INSERT INTO kalturadw_bisources.bisources_entry_media_source (ENTRY_MEDIA_SOURCE_id,ENTRY_MEDIA_SOURCE_name) VALUES(29,'AKAMAI_LIVE'); 
