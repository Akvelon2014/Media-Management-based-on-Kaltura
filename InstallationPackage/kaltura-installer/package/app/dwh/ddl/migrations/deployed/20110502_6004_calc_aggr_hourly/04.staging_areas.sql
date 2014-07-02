USE `kalturadw_ds`;

ALTER TABLE `staging_areas` ADD COLUMN hour_id_field VARCHAR(45);

UPDATE staging_areas
SET hour_id_field = 'activity_hour_id' WHERE aggr_date_field = 'activity_date_id';

UPDATE staging_areas
SET hour_id_field = 'event_hour_id' WHERE aggr_date_field = 'event_date_id';
