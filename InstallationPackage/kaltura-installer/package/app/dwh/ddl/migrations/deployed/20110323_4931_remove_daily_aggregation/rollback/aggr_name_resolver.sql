use kalturadw_ds;
alter table aggr_name_resolver add column hourly_aggr_table;
UPDATE kalturadw_ds.aggr_name_resolver 
SET aggr_table = 
	CASE (aggr_name)
	WHEN ('entry') THEN 'dwh_aggr_events_entry' 
	WHEN ('domain') THEN 'dwh_aggr_events_domain' 
	WHEN ('country') THEN 'dwh_aggr_events_country' 
	WHEN ('partner') THEN 'dwh_aggr_partner' 
	WHEN ('widget') THEN 'dwh_aggr_events_widget' 
	WHEN ('uid') THEN 'dwh_aggr_events_uid' 
	WHEN ('domain_referrer') THEN ''
	END,
hourly_aggr_table = 
	CASE (aggr_name) 
	WHEN ('entry') THEN 'dwh_hourly_events_entry' 
	WHEN ('domain') THEN 'dwh_hourly_events_domain' 
	WHEN ('country') THEN 'dwh_hourly_events_country' 
	WHEN ('partner') THEN 'dwh_hourly_partner' 
	WHEN ('widget') THEN 'dwh_hourly_events_widget' 
	WHEN ('uid') THEN 'dwh_hourly_events_uid' 
	WHEN ('domain_referrer') THEN 'dwh_hourly_events_domain_referrer' 
	END 