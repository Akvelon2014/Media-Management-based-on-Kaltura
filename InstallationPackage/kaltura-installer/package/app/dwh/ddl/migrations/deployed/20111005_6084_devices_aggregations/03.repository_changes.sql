INSERT INTO kalturadw_ds.aggr_name_resolver (aggr_name, aggr_table, aggr_id_field, aggr_join_stmt)
VALUES ('devices', 'dwh_hourly_events_devices', 'country_id,location_id,os_id,browser_id,ui_conf_id, entry_id','');

UPDATE kalturadw_ds.staging_areas  SET post_transfer_aggregations = REPLACE(post_transfer_aggregations, ')',',\'devices\')');
