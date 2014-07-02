ALTER TABLE kalturadw.dwh_hourly_events_domain_referrer 
DROP KEY date_id,
DROP KEY domain_id_referrer_id,
DROP PRIMARY KEY ,
ADD PRIMARY KEY (partner_id, domain_id, date_id, hour_id, referrer_id);