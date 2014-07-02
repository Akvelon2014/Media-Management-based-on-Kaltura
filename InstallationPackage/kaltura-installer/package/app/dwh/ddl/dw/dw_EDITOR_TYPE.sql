DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_editor_type`;

CREATE TABLE `kalturadw`.`dwh_dim_editor_type` (
  `editor_type_id` smallint(6) auto_increment not null ,
  `editor_type_name` VARCHAR(50) DEFAULT 'missing value',
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`editor_type_id`),
  UNIQUE KEY (`editor_type_name`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_editor_type_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_editor_type`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
	
