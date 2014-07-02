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
