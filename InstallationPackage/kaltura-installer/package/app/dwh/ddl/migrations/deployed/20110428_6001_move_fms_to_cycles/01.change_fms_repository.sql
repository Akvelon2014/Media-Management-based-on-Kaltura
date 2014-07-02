UPDATE kalturadw_ds.staging_areas
SET source_table = 'ds_fms_session_events', staging_partition_field = 'cycle_id'
WHERE process_id = 2
