DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_user_status`;

CREATE TABLE `kalturadw`.`dwh_dim_user_status` (
  `user_status_id` SMALLINT NOT NULL ,
  `user_status_name` VARCHAR(50),
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_status_id`)
  
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


CREATE TRIGGER `kalturadw`.`dwh_dim_user_status_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_user_status`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
	
