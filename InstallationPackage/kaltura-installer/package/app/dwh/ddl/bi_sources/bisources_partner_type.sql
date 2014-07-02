DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_partner_type`;

CREATE TABLE `kalturadw_bisources`.`bisources_partner_type` (
  `partner_type_id` SMALLINT NOT NULL ,
  `partner_type_name` VARCHAR(50),
  PRIMARY KEY (`partner_type_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(1,'KMC_SIGNUP');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(2,'OTHER');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(100,'WIKI');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(101,'WORDPRESS');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(102,'DRUPAL');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(103,'MIND_TOUCH');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(104,'MOODLE');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(105,'COMMUNITY_EDITION');
INSERT INTO `kalturadw_bisources`.`bisources_partner_type`  VALUES(106,'JOOMLA ');

