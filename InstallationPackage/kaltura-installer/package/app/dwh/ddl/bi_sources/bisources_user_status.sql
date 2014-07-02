DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_user_status`;

CREATE TABLE `kalturadw_bisources`.`bisources_user_status` (
  `user_status_id` SMALLINT NOT NULL ,
  `user_status_name` VARCHAR(50),
  PRIMARY KEY (`user_status_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

INSERT INTO `kalturadw_bisources`.`bisources_user_status` VALUES(0,'BLOCKED');
INSERT INTO `kalturadw_bisources`.`bisources_user_status` VALUES(1,'ACTIVE'); 
INSERT INTO `kalturadw_bisources`.`bisources_user_status`  VALUES(2,'DELETED');
