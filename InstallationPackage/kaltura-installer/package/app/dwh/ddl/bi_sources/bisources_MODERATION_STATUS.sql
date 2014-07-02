DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_moderation_status`;

CREATE TABLE `kalturadw_bisources`.`bisources_moderation_status` (
  `moderation_status_id` SMALLINT NOT NULL ,
  `moderation_status_name` VARCHAR(50),
  PRIMARY KEY (`moderation_status_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

INSERT INTO kalturadw_bisources.bisources_moderation_status (moderation_status_id,moderation_status_name) VALUES(1,'PENDING'); 
INSERT INTO kalturadw_bisources.bisources_moderation_status (moderation_status_id,moderation_status_name) VALUES(2,'APROVED'); 
INSERT INTO kalturadw_bisources.bisources_moderation_status (moderation_status_id,moderation_status_name) VALUES(3,'BLOCK'); 
INSERT INTO kalturadw_bisources.bisources_moderation_status (moderation_status_id,moderation_status_name) VALUES(4,'DELETE'); 
INSERT INTO kalturadw_bisources.bisources_moderation_status (moderation_status_id,moderation_status_name) VALUES(5,'REVIEW'); 
INSERT INTO kalturadw_bisources.bisources_moderation_status (moderation_status_id,moderation_status_name) VALUES(6,'AUTO_APPROVED'); 
