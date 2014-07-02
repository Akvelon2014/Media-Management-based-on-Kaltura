DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_moderation_status`;

CREATE TABLE `kalturadw`.`dwh_dim_moderation_status` (
  `moderation_status_id` SMALLINT NOT NULL ,
  `moderation_status_name` VARCHAR(50) DEFAULT 'missing value',
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '1',
  PRIMARY KEY (`moderation_status_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_moderation_status_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_moderation_status`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
	
