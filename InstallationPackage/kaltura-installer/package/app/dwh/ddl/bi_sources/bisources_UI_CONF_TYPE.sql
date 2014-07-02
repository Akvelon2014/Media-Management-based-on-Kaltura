DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_ui_conf_type`;

CREATE TABLE `kalturadw_bisources`.`bisources_ui_conf_type` (
  `ui_conf_type_id` SMALLINT NOT NULL ,
  `ui_conf_type_name` VARCHAR(50) DEFAULT 'missing value',

  PRIMARY KEY (`ui_conf_type_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(0, 'GENERIC');	
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(1,'KDP'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(2,'CW'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(3,'EDITOR'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(4,'ADVANCED_EDITOR'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(5,'PLAYLIST'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(6,'APP_STUDIO'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(7, 'KRecord');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(8, 'KDP3'); 
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(9, 'KMC_ACCOUNT');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(10, 'KMC_ANALYTICS');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(11, 'KMC_CONTENT');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(12, 'KMC_DASHBOARD');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(14, 'Silverlight player');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(15, 'CLIENTSIDE_ENCODER');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(16, 'KMC_GENERAL');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(17, 'KMC_ROLES_AND_PERMISSIONS');
INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name) VALUES(18, 'Clipper');