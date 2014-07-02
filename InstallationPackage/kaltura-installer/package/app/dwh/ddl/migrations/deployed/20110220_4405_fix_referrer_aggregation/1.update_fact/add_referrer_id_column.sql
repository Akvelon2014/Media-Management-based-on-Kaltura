 ALTER TABLE kalturadw.dwh_fact_events 
 DROP PRIMARY KEY,
 DROP INDEX entry_id ,
 DROP INDEX partner_id_event_type_id_time,
 DROP INDEX event_date_id,
 DROP INDEX domain_id;

 ALTER TABLE kalturadw.dwh_fact_events 
 ADD COLUMN referrer_id INT(11);

 ALTER TABLE kalturadw.dwh_fact_events 
 ADD PRIMARY KEY (`file_id`,`event_id`,`event_time`),
 ADD KEY `Entry_id` (`entry_id`),
 ADD KEY `partner_id_event_type_id_time` (`partner_id`,`event_type_id`,`event_time`),
 ADD KEY `event_date_id` (`event_date_id`),
 ADD KEY `domain_id` (`domain_id`);

 ALTER TABLE kalturadw_ds.ds_events ADD COLUMN referrer_id INT(11);
