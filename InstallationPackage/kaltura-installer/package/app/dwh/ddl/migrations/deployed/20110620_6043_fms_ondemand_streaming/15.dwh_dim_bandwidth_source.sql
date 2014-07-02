ALTER TABLE kalturadw.dwh_dim_bandwidth_source ADD is_live BOOL DEFAULT 0 NOT NULL;

UPDATE kalturadw.dwh_dim_bandwidth_source bs
SET is_live = 1
WHERE bandwidth_source_id = 5;
