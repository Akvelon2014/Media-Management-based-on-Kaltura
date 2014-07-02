# add lines & err_lines to kalturadw_ds.files
ALTER TABLE kalturadw.`dwh_aggr_events_country` ADD `count_buf_start` INT DEFAULT NULL,ADD  `count_buf_end` INT DEFAULT NULL;
ALTER TABLE kalturadw.`dwh_aggr_events_domain` ADD `count_buf_start` INT DEFAULT NULL,ADD  `count_buf_end` INT DEFAULT NULL;
ALTER TABLE kalturadw.`dwh_aggr_events_entry` ADD `count_buf_start` INT DEFAULT NULL,ADD  `count_buf_end` INT DEFAULT NULL;
ALTER TABLE kalturadw.`dwh_aggr_events_widget` ADD `count_buf_start` INT DEFAULT NULL,ADD  `count_buf_end` INT DEFAULT NULL;
ALTER TABLE kalturadw.`dwh_aggr_partner` ADD `count_buf_start` INT DEFAULT NULL,ADD  `count_buf_end` INT DEFAULT NULL;


