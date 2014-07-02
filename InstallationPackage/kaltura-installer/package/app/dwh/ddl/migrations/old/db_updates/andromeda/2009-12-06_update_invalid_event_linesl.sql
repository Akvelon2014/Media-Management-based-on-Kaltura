alter table `kalturadw_ds`.`invalid_event_lines`
add column `date_id`  INTEGER default NULL,
add column `partner_id`  VARCHAR(20) default NULL,
add KEY `date_id_partner_id` (`date_id`,`partner_id`) ;