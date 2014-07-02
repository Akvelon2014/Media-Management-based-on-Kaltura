DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_partner_status`;

CREATE TABLE `kalturadw_bisources`.`bisources_partner_status` (
  `partner_status_id` SMALLINT NOT NULL ,
  `partner_status_name` VARCHAR(50),
  PRIMARY KEY (`partner_status_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


INSERT INTO `kalturadw_bisources`.`bisources_partner_status` (PARTNER_STATUS_id,PARTNER_STATUS_name) VALUES(1,'status_one');
INSERT INTO `kalturadw_bisources`.`bisources_partner_status` (PARTNER_STATUS_id,PARTNER_STATUS_name) VALUES(2,'status_two');
