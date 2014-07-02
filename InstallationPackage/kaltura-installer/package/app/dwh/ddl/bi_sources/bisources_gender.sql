DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_gender`;

CREATE TABLE `kalturadw_bisources`.`bisources_gender` (
  `gender_id` SMALLINT NOT NULL ,
  `gender_name` VARCHAR(50),
  PRIMARY KEY (`gender_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


INSERT INTO `kalturadw_bisources`.`bisources_gender`  VALUES(1,'MALE');
INSERT INTO `kalturadw_bisources`.`bisources_gender`  VALUES(2,'FEMALE');
