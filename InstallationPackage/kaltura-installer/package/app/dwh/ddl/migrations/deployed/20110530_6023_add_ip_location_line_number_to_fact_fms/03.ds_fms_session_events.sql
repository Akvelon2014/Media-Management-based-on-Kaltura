ALTER TABLE kalturadw_ds.ds_fms_session_events
        ADD `line_number` INT (10) DEFAULT NULL FIRST,
        CHANGE `client_ip_str` `client_ip` VARCHAR(15) DEFAULT NULL,
        CHANGE `client_ip` `client_ip_number` INT(10) UNSIGNED DEFAULT NULL,
        CHANGE `server_ip` `server_ip_number` INT(10) UNSIGNED DEFAULT NULL,
        ADD `server_ip` VARCHAR(15) DEFAULT NULL AFTER external_id;

ALTER TABLE kalturadw_ds.fms_incomplete_sessions
        ADD `session_client_location_id` INT(11) DEFAULT NULL AFTER session_date_id,
        ADD `session_client_country_id` INT(11) DEFAULT NULL AFTER session_date_id,
        ADD `session_client_ip_number` INT(10) UNSIGNED DEFAULT NULL AFTER session_date_id,
        ADD `session_client_ip` VARCHAR(15) DEFAULT NULL AFTER session_date_id;

ALTER TABLE kalturadw_ds.fms_stale_sessions
        ADD `session_client_location_id` INT(11) DEFAULT NULL AFTER session_date_id,
        ADD `session_client_country_id` INT(11) DEFAULT NULL AFTER session_date_id,
        ADD `session_client_ip_number` INT(10) UNSIGNED DEFAULT NULL AFTER session_date_id,
        ADD `session_client_ip` VARCHAR(15) DEFAULT NULL AFTER session_date_id;
