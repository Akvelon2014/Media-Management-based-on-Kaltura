UPDATE kalturadw_ds.aggr_name_resolver
set aggr_join_stmt = 'USE INDEX (event_hour_id_event_date_id_partner_id) inner join kalturadw.dwh_dim_entries as entry on(ev.entry_id = entry.entry_id)'
where aggr_name = 'uid';
