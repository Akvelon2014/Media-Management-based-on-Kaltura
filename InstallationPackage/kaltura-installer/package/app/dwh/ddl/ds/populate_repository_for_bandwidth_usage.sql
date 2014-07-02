INSERT INTO kalturadw_ds.processes (id, process_name, max_files_per_cycle) VALUE (4, 'bandwidth_usage_AKAMAI', 50);
INSERT INTO kalturadw_ds.processes (id, process_name, max_files_per_cycle) VALUE (5, 'bandwidth_usage_LLN', 50);
INSERT INTO kalturadw_ds.processes (id, process_name, max_files_per_cycle) VALUE (6, 'bandwidth_usage_LEVEL3', 1000);

INSERT INTO `kalturadw_ds`.`staging_areas`
        (`id`,
        `process_id`,
        `source_table`,
        `target_table`,
        `on_duplicate_clause`,
        `staging_partition_field`,
        `post_transfer_sp`,
	`post_transfer_aggregations`,
	`aggr_date_field`,
	`hour_id_field`
        )
        VALUES
        (4,      4,
         'ds_bandwidth_usage',
         'kalturadw.dwh_fact_bandwidth_usage',
         NULL,
         'cycle_id',
         NULL,
	'(\'bandwidth_usage\',\'devices_bandwidth_usage\')',
	'activity_date_id',
	'activity_hour_id'
        );


INSERT INTO `kalturadw_ds`.`staging_areas`
        (`id`,
        `process_id`,
        `source_table`,
        `target_table`,
        `on_duplicate_clause`,
        `staging_partition_field`,
        `post_transfer_sp`,
	`post_transfer_aggregations`,
	`aggr_date_field`,
	`hour_id_field`
        )
        VALUES
        (5,      5,
         'ds_bandwidth_usage',
         'kalturadw.dwh_fact_bandwidth_usage',
         NULL,
         'cycle_id',
         NULL,
	'(\'bandwidth_usage\',\'devices_bandwidth_usage\')',
	'activity_date_id',
	'activity_hour_id'
        );

INSERT INTO `kalturadw_ds`.`staging_areas`
        (`id`,
        `process_id`,
        `source_table`,
        `target_table`,
        `on_duplicate_clause`,
        `staging_partition_field`,
        `post_transfer_sp`,
	`post_transfer_aggregations`,
	`aggr_date_field`,
	`hour_id_field`
        )
        VALUES
        (6,      6,
         'ds_bandwidth_usage',
         'kalturadw.dwh_fact_bandwidth_usage',
         NULL,
         'cycle_id',
         NULL,
	'(\'bandwidth_usage\',\'devices_bandwidth_usage\')',
	'activity_date_id',
	'activity_hour_id'
        );

INSERT INTO `kalturadw_ds`.`staging_areas`
        (`id`,
        `process_id`,
        `source_table`,
        `target_table`,
        `on_duplicate_clause`,
        `staging_partition_field`,
        `post_transfer_sp`,
	`post_transfer_aggregations`,
	`aggr_date_field`,
	`hour_id_field`
        )
        VALUES
        (7,      1,
         'ds_bandwidth_usage',
         'kalturadw.dwh_fact_bandwidth_usage',
         NULL,
         'cycle_id',
         NULL,
	'(\'bandwidth_usage\',\'devices_bandwidth_usage\')',
	'activity_date_id',
	'activity_hour_id'
        );


