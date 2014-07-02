USE `kalturadw`;

DROP TABLE IF EXISTS kalturadw.`dwh_dim_entry_type_display`;

CREATE TABLE kalturadw.`dwh_dim_entry_type_display` 
(
    entry_type_id SMALLINT NOT NULL,
    entry_media_type_id SMALLINT NOT NULL,
    display varchar(256) default null,
    dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
    dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    UNIQUE KEY (entry_type_id, entry_media_type_id)
);

CREATE TRIGGER `kalturadw`.`dwh_dim_entry_type_display_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_entry_type_display`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
    
DELIMITER $$

USE `kalturadw`$$

DROP VIEW IF EXISTS `dwh_view_entry_type_display`$$

CREATE VIEW `kalturadw`.`dwh_view_entry_type_display` AS 
SELECT
  t.entry_type_id, t.entry_type_name, m.entry_media_type_id, m.entry_media_type_name,
    ifnull(d.display, concat(t.entry_type_name,'-',m.entry_media_type_name)) as display
FROM dwh_dim_entry_type_display d,
dwh_dim_entry_type t,
dwh_dim_entry_media_type m
WHERE t.entry_type_id = d.entry_type_id AND m.entry_media_type_id = d.entry_media_type_id
$$

DELIMITER ;
