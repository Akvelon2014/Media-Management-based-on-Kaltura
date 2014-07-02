INSERT INTO kalturadw_ds.processes (id, process_name, max_files_per_cycle)
values (9, 'transcoding_errors', 0);

INSERT INTO kalturadw_ds.parameters (id, parameter_name, date_value, process_id)
values (9, 'transcoding_errors_last_update', DATE(20100101), 9);
