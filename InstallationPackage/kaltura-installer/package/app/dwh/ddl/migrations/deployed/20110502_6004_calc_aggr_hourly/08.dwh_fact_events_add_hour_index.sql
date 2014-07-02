ALTER TABLE kalturadw.dwh_fact_events
DROP KEY `partner_id_event_type_id_time`,
DROP KEY `event_date_id`,
DROP KEY `domain_id`,
ADD KEY `event_hour_id_event_date_id_partner_id` (event_hour_id, event_date_id, partner_id)