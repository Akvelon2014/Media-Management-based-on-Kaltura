ALTER TABLE kalturadw.dwh_hourly_events_country DROP INDEX country_id, DROP INDEX date_id;
ALTER TABLE kalturadw.dwh_hourly_events_domain DROP INDEX domain_id, DROP INDEX date_id;
ALTER TABLE kalturadw.dwh_hourly_events_domain_referrer DROP PRIMARY KEY, ADD PRIMARY KEY (partner_id, date_id, hour_id, domain_id, referrer_id);
ALTER TABLE kalturadw.dwh_hourly_events_entry DROP INDEX entry_id, DROP INDEX date_id;
ALTER TABLE kalturadw.dwh_hourly_events_uid DROP INDEX uid, DROP INDEX date_id;
ALTER TABLE kalturadw.dwh_hourly_events_widget DROP INDEX widget_id, DROP INDEX date_id;
ALTER TABLE kalturadw.dwh_hourly_partner DROP INDEX date_id;
