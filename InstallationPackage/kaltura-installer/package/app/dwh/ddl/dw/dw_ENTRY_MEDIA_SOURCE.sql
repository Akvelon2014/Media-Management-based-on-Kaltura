DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_entry_media_source`;

CREATE TABLE `kalturadw`.`dwh_dim_entry_media_source` (
  `entry_media_source_id` SMALLINT NOT NULL ,
  `entry_media_source_name` VARCHAR(25) DEFAULT 'missing value',
  `entry_media_source_category` VARCHAR(25) NOT NULL DEFAULT 'IMPORT',
   dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
   dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
   ri_ind TINYINT NOT NULL DEFAULT '1',
  PRIMARY KEY (`entry_media_source_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_entry_media_source_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_entry_media_source`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
	
INSERT INTO kalturadw.dwh_dim_entry_media_source (entry_media_source_id, entry_media_source_name, entry_media_source_category, dwh_creation_date, dwh_update_date, ri_ind)
VALUES (1, 'FILE', 'UPLOAD', NOW(), NOW(), 0), (2, 'WEBCAM', 'WEBCAM', NOW(), NOW(), 0);
