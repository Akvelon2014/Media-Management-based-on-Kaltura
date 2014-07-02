UPDATE kalturadw_ds.staging_areas
SET on_duplicate_clause = '',
staging_partition_field = 'cycle_id'
WHERE process_id = 1;
