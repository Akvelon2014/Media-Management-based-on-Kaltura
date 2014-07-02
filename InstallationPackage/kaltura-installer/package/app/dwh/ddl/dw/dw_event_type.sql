DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_event_type`;

CREATE TABLE `kalturadw`.`dwh_dim_event_type` (
  `event_type_id` SMALLINT NOT NULL ,
  `event_type_name` VARCHAR(50) DEFAULT 'missing value',
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_type_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_event_type_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_event_type`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
	
