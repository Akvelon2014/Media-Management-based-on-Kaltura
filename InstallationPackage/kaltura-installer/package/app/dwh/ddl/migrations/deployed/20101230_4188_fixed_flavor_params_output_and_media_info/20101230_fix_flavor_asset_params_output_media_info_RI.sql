INSERT INTO kalturadw.ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_flavor_params_output', 'flavor_asset_id', 'dwh_update_date','dwh_dim_flavor_asset','id',1);
INSERT INTO kalturadw.ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check) VALUES ('dwh_dim_media_info', 'flavor_asset_id', 'dwh_update_date','dwh_dim_flavor_asset','id',1);
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','int_id','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','partner_id','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','entry_id','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','flavor_params_id','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','STATUS','-2');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','VERSION','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','width','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','height','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','bitrate','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','frame_rate','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','size','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','is_original','-1');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','created_at','2099-01-01 00:00:00');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','updated_at','2099-01-01 00:00:00');
INSERT INTO kalturadw.ri_defaults (table_name, default_field, default_value) VALUES ('dwh_dim_flavor_asset','deleted_at','2099-01-01 00:00:00');

