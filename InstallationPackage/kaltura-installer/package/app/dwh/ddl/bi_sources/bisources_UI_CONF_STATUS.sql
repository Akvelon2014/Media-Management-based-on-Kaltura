DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_ui_conf_status`;

CREATE TABLE `kalturadw_bisources`.`bisources_ui_conf_status` (
  `ui_conf_status_id` SMALLINT NOT NULL ,
  `ui_conf_status_name` VARCHAR(50) DEFAULT 'missing value',
   PRIMARY KEY (`ui_conf_status_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

	
INSERT INTO kalturadw_bisources.bisources_ui_conf_status(ui_conf_status_id,ui_conf_status_name) VALUES(1,'PENDING'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_status(ui_conf_status_id,ui_conf_status_name) VALUES(2,'READY'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_status(ui_conf_status_id,ui_conf_status_name) VALUES(3,'DELETED'); 
