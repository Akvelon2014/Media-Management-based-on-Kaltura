DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_entry_type`;

CREATE TABLE `kalturadw_bisources`.`bisources_entry_type` (
  `entry_type_id` SMALLINT NOT NULL ,
  `entry_type_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`entry_type_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


	
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(-1,'AUTOMATIC'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(0,'BACKGROUND'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(1,'MEDIACLIP'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(2,'SHOW'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(4,'BUBBLES'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(5,'PLAYLIST'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(7,'LIVE_STREAM'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(10,'DOCUMENT'); 
INSERT INTO kalturadw_bisources.bisources_entry_type (entry_type_id,entry_type_name) VALUES(300,'DVD'); 
