DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_widget_security_type`;

CREATE TABLE `kalturadw_bisources`.`bisources_widget_security_type` (
  `widget_security_type_id` SMALLINT NOT NULL ,
  `widget_security_type_name` VARCHAR(50),
  PRIMARY KEY (`widget_security_type_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

INSERT INTO kalturadw_bisources.bisources_widget_security_type (widget_security_type_id,widget_security_type_name) VALUES(1,'NONE'); 
INSERT INTO kalturadw_bisources.bisources_widget_security_type (widget_security_type_id,widget_security_type_name) VALUES(2,'TIMEHASH'); 
INSERT INTO kalturadw_bisources.bisources_widget_security_type (widget_security_type_id,widget_security_type_name) VALUES(3,'MATCH_IP'); 
INSERT INTO kalturadw_bisources.bisources_widget_security_type (widget_security_type_id,widget_security_type_name) VALUES(4,'FORCE_KS'); 
