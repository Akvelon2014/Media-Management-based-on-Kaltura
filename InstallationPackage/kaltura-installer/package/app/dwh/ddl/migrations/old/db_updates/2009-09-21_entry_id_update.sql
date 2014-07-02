
# add field on kalturadw.dwh_fact_events
ALTER TABLE kalturadw.dwh_fact_events MODIFY entry_id VARCHAR(20);

# add field on kalturadw_ds.DS_Events
ALTER TABLE kalturadw_ds.DS_Events MODIFY entry_id VARCHAR(20);

# add field on kalturadw_ds.dwh_aggr_events_entry
ALTER TABLE kalturadw.dwh_aggr_events_entry MODIFY entry_id VARCHAR(20);

# fix entries
ALTER TABLE kalturadw.dwh_dim_entries MODIFY entry_id VARCHAR(20), MODIFY kshow_id VARCHAR(20);

# fix widgets
ALTER TABLE kalturadw.dwh_dim_widget MODIFY entry_id VARCHAR(20), MODIFY kshow_id VARCHAR(20);




