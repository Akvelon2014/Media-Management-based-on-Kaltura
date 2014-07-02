DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_widget_security_policy`;

CREATE TABLE `kalturadw`.`dwh_dim_widget_security_policy` (
  `widget_security_policy_id` SMALLINT NOT NULL ,
  `widget_security_policy_name` VARCHAR(50),
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '1',
  PRIMARY KEY (`widget_security_policy_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8; 


create trigger `kalturadw`.`dwh_dim_widget_security_policy_setcreationtime_oninsert` before insert
    on `kalturadw`.`dwh_dim_widget_security_policy`
    for each row 
	set new.dwh_creation_date = now();
	
