ALTER TABLE kalturadw_ds.parameters ADD date_value TIMESTAMP;

INSERT INTO kalturadw_ds.parameters (id, process_id, parameter_name, int_value, date_value) VALUES(2, 0, "dim_sync_last_update", -1, NOW() - INTERVAL 1 DAY);
