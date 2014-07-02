INSERT INTO kalturadw_ds.processes (id, process_name) VALUE (4, 'bandwidth_usage_AKAMAI');

INSERT INTO `kalturadw_ds`.`staging_areas` 
	(`id`, 
	`process_id`, 
	`source_table`, 
	`target_table`, 
	`on_duplicate_clause`, 
	`staging_partition_field`, 
	`post_transfer_sp`
	)
	VALUES
	(4,	 4,
	 'ds_bandwidth_usage',
	 'kalturadw.dwh_fact_bandwidth_usage',
	 NULL,
	 'cycle_id',
	 NULL
	);

INSERT INTO kalturadw_ds.processes (id, process_name) VALUE (5, 'bandwidth_usage_LLN');

INSERT INTO `kalturadw_ds`.`staging_areas` 
	(`id`, 
	`process_id`, 
	`source_table`, 
	`target_table`, 
	`on_duplicate_clause`, 
	`staging_partition_field`, 
	`post_transfer_sp`
	)
	VALUES
	(5,	 5,
	 'ds_bandwidth_usage',
	 'kalturadw.dwh_fact_bandwidth_usage',
	 NULL,
	 'cycle_id',
	 NULL
	);
	
INSERT INTO kalturadw_ds.processes (id, process_name) VALUE (6, 'bandwidth_usage_LEVEL3');

INSERT INTO `kalturadw_ds`.`staging_areas` 
	(`id`, 
	`process_id`, 
	`source_table`, 
	`target_table`, 
	`on_duplicate_clause`, 
	`staging_partition_field`, 
	`post_transfer_sp`
	)
	VALUES
	(6,	 6,
	 'ds_bandwidth_usage',
	 'kalturadw.dwh_fact_bandwidth_usage',
	 NULL,
	 'cycle_id',
	 NULL
	);
	
INSERT INTO kalturadw_ds.processes (id, process_name) VALUE (7, 'bandwidth_usage_WWW');

INSERT INTO `kalturadw_ds`.`staging_areas` 
	(`id`, 
	`process_id`, 
	`source_table`, 
	`target_table`, 
	`on_duplicate_clause`, 
	`staging_partition_field`, 
	`post_transfer_sp`
	)
	VALUES
	(7,	 7,
	 'ds_bandwidth_usage',
	 'kalturadw.dwh_fact_bandwidth_usage',
	 NULL,
	 'cycle_id',
	 NULL
	);