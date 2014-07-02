INSERT INTO kalturadw_ds.processes (id, process_name, max_files_per_cycle) VALUES (7, 'fms_ondemand_streaming', 50);
INSERT INTO kalturadw_ds.staging_areas (id, process_id, source_table, target_table, staging_partition_field, post_transfer_sp, aggr_date_field, post_transfer_aggregations, hour_id_field)
VALUES (8, 7, 'ds_fms_session_events', 'kalturadw.dwh_fact_fms_session_events', 'cycle_id', 'fms_sessionize', 'event_date_id', '','event_hour_id');


