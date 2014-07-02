insert into kalturadw_ds.processes (id,process_name, max_files_per_cycle) values (2,'fms_live_streaming', 50);

insert into kalturadw_ds.staging_areas (id,process_id,source_table,target_table,on_duplicate_clause,staging_partition_field,post_transfer_sp, aggr_date_field, hour_id_field, post_transfer_aggregations)
values(2,2,'ds_fms_session_events','kalturadw.dwh_fact_fms_session_events',null,'cycle_id','fms_sessionize', 'event_date_id', 'event_hour_id', '(\'bandwidth_usage\',\'devices_bandwidth_usage\')');

INSERT INTO kalturadw_ds.processes (id, process_name, max_files_per_cycle) VALUES (7, 'fms_ondemand_streaming', 50);
INSERT INTO kalturadw_ds.staging_areas (id, process_id, source_table, target_table, staging_partition_field, post_transfer_sp, aggr_date_field, post_transfer_aggregations, hour_id_field)
VALUES (8, 7, 'ds_fms_session_events', 'kalturadw.dwh_fact_fms_session_events', 'cycle_id', 'fms_sessionize', 'event_date_id', '(\'bandwidth_usage\',\'devices_bandwidth_usage\')','event_hour_id');


