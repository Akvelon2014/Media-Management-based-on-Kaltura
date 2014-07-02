DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_control`;
CREATE TABLE `kalturadw_bisources`.`bisources_control` (
  `control_id` SMALLINT NOT NULL ,
  `control_name` VARCHAR(50),
  PRIMARY KEY (`control_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

INSERT INTO `kalturadw_bisources`.`bisources_control`  VALUES(1,'control_one');
INSERT INTO `kalturadw_bisources`.`bisources_control`  VALUES(2,'control_two');
