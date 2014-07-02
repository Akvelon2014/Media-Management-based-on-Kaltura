ALTER TABLE kalturadw.dwh_dim_entries
ADD INDEX (updated_at),
ADD INDEX (modified_at),
ADD INDEX (created_at);