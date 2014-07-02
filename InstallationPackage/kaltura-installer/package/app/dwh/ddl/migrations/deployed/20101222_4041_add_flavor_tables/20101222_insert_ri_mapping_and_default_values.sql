USE kalturadw;

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_conversion_profile', 'creation_mode', 'dwh_update_date','dwh_dim_creation_mode','creation_mode_id',1);
INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_conversion_profile', 'partner_id', 'dwh_update_date','dwh_dim_partners','partner_id',1);

INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_creation_mode', 'creation_mode_name', 'Missing Value');


INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_flavor_params_conversion_profile', 'conversion_profile_id', 'dwh_update_date','dwh_dim_conversion_profile','id',1);
INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_flavor_params_conversion_profile', 'ready_behavior', 'dwh_update_date','dwh_dim_ready_behavior','ready_behavior_id',1);
INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_flavor_params_conversion_profile', 'flavor_params_id', 'dwh_update_date','dwh_dim_flavor_params','id',1);

INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'partner_id', -1);
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'name', 'Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'created_at', '2099-01-01 00:00:00');
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'updated_at', '2099-01-01 00:00:00');
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'deleted_at', '2099-01-01 00:00:00');
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'clip_start', -1);
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'clip_duration', -1);
INSERT INTO ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_conversion_profile', 'creation_mode', -1);


INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_flavor_params_output', 'ready_behavior', 'dwh_update_date','dwh_dim_ready_behavior','ready_behavior_id',1);
INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_flavor_params_output', 'entry_id', 'dwh_update_date','dwh_dim_entries','entry_id',1);


