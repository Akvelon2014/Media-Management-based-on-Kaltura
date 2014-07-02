USE kalturadw;

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_file_sync', 'object_type', 'dwh_update_date','dwh_dim_file_sync_object_type','file_sync_object_type_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_file_sync', 'status', 'dwh_update_date','dwh_dim_file_sync_status','file_sync_status_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_file_sync', 'partner_id', 'dwh_update_date','dwh_dim_partners','partner_id',1);

INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_file_sync_object_type', 'file_sync_object_type_name', 'Missing Value');

INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_file_sync_status', 'file_sync_status_name', 'Missing Value');

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_flavor_asset', 'entry_id', 'dwh_update_date','dwh_dim_entries','entry_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_flavor_asset', 'status', 'dwh_update_date','dwh_dim_asset_status','asset_status_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_flavor_asset', 'partner_id', 'dwh_update_date','dwh_dim_partners','partner_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_flavor_asset', 'flavor_params_id', 'dwh_update_date','dwh_dim_flavor_params','id',1);


INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_asset_status', 'asset_status_name', 'Missing Value');

INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','version', '-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','partner_id','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','NAME','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','tags','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','description','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','ready_behavior','-2');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','is_default','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','FORMAT','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','video_codec','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','video_bitrate','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','audio_codec','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','audio_bitrate','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','audio_channels','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','audio_sample_rate','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','audio_resolution','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','width','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','height','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','frame_rate','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','gop_size','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','two_pass','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','conversion_engines','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','conversion_engines_extra_params','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','view_order','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','bypass_by_extension','Missing Value');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','creation_mode','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','deinterlice','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','rotate','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','engine_version','-1');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','created_at','2099-01-01 00:00:00');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','updated_at','2099-01-01 00:00:00');
INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_flavor_pararms','deleted_at','2099-01-01 00:00:00');

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_flavor_params', 'partner_id', 'dwh_update_date','dwh_dim_partners','partner_id',1);

INSERT INTO ri_mapping (table_name, column_name, date_id_column_name, reference_table, reference_column, perform_check)
VALUES ('dwh_dim_flavor_params', 'ready_behavior', 'dwh_update_date','dwh_dim_ready_behavior','ready_behavior_id',1);

INSERT INTO ri_defaults (table_name, default_field, default_value)
VALUES ('dwh_dim_ready_behavior', 'ready_behavior_name', 'Missing Value');