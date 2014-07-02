CREATE TABLE  `kalturadw_ds`.`parameters` (
`id` int(11) unsigned NOT NULL,
`process_id` int(11) unsigned NOT NULL,
`parameter_name` varchar(100) NOT NULL,
`int_value` int(11) NOT NULL,
date_value TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=MyISAM;


INSERT INTO kalturadw_ds.parameters (id, process_id, parameter_name, int_value, date_value) VALUES
(2, 0, "dim_sync_last_update", -1, NOW() - INTERVAL 1 DAY),
(3, 2, 'fms_stale_session_days_limit', 3, null),
(8, 0, 'referencial_integrity_last_update', 0, date(20110101));

INSERT kalturadw_ds.parameters (id, parameter_name)
VALUES (4, 'sync_last_execution_plays_views'),
(5, 'sync_start_time_plays_views'),
(6, 'sync_last_execution_kuser_storage'),
(7, 'sync_start_time_kuser_storage');
