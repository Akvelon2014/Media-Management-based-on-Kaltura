DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_partner_group_type`;

CREATE TABLE `kalturadw_bisources`.`bisources_partner_group_type` (
  `partner_group_type_id` SMALLINT NOT NULL ,
  `partner_group_type_name` VARCHAR(50),
  PRIMARY KEY (`partner_group_type_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


INSERT INTO `kalturadw_bisources`.`bisources_partner_group_type`  
	VALUES
	(1,'Publisher'),
	(2,'VAR'),
	(3,'Group');
