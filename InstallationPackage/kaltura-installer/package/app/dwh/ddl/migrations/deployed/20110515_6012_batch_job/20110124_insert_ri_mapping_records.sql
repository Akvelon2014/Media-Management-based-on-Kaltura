USE kalturadw;

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_batch_job', 'job_type_id', 'dwh_update_date','dwh_dim_batch_job_type','batch_job_type_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_batch_job', 'status_id', 'dwh_update_date','dwh_dim_batch_job_status','batch_job_status_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_batch_job', 'error_type_id', 'dwh_update_date','dwh_dim_batch_job_error_type','batch_job_error_type_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_batch_job', 'partner_id', 'dwh_update_date','dwh_dim_partners','partner_id',1);