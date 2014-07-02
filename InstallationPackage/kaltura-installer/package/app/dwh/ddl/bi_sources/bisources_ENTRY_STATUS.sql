DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_entry_status`;

CREATE TABLE `kalturadw_bisources`.`bisources_entry_status` (
  `entry_status_id` SMALLINT NOT NULL ,
  `entry_status_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`entry_status_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

	
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(-2,'ERROR_IMPORTING');
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(-1,'ERROR_CONVERTING'); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(0,'IMPORT'); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(1,'PRECONVERT'); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(2,'READY'); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(3,'DELETED'); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(4,'PENDING'); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(5,'MODERATE '); 
INSERT INTO kalturadw_bisources.bisources_entry_status (entry_status_id,entry_status_name) VALUES(6,'BLOCKED'); 
