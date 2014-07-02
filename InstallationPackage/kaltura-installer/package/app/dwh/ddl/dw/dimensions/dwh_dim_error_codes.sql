DROP TABLE IF EXISTS kalturadw.dwh_dim_error_codes;

CREATE TABLE `kalturadw`.`dwh_dim_error_codes` (
  `error_code_id` INT(11) NOT NULL AUTO_INCREMENT,
  `error_code_name` VARCHAR(165) NOT NULL DEFAULT '',
  `sub_error_code_name` VARCHAR(165) NOT NULL DEFAULT 'unknown',
  `dwh_creation_date` TIMESTAMP  NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dwh_update_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`error_code_id`),
   UNIQUE KEY (`error_code_name`,`sub_error_code_name`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_error_code_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_error_codes`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
