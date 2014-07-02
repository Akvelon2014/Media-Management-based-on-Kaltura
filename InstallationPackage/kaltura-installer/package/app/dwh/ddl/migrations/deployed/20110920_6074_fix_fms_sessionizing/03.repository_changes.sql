ALTER TABLE kalturadw.dwh_dim_fms_bandwidth_source ADD COLUMN file_regex VARCHAR(100) NOT NULL DEFAULT '.*',
DROP KEY process_id,
ADD UNIQUE KEY `process_id-fms_app_id-file_regex` (`process_id`,`fms_app_id`,`file_regex`);

INSERT INTO kalturadw.dwh_dim_bandwidth_source (bandwidth_source_id, bandwidth_source_name, is_live)
VALUES (7,'akamai_vod_fms_HD',0);

INSERT INTO kalturadw.dwh_dim_fms_bandwidth_source (process_id, fms_app_id, bandwidth_source_id, file_regex)
VALUES (7, 1, 7, '_105515\\.');

UPDATE kalturadw.dwh_dim_fms_bandwidth_source
SET file_regex = '_77658\\.|_86593\\.'
WHERE process_id =7 AND fms_app_id = 1 AND bandwidth_source_id = 6;