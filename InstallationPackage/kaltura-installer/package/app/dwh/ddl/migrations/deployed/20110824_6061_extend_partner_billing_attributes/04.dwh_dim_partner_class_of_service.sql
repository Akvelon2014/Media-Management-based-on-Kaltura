DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_partner_class_of_service`;

CREATE TABLE `kalturadw`.`dwh_dim_partner_class_of_service` (
  `partner_class_of_service_id` SMALLINT NOT NULL ,
  `partner_class_of_service_name` VARCHAR(50) DEFAULT 'missing value',
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '1',
  PRIMARY KEY (`partner_class_of_service_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_partner_class_of_service_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_partner_class_of_service`
    FOR EACH ROW
        SET new.dwh_creation_date = NOW();

