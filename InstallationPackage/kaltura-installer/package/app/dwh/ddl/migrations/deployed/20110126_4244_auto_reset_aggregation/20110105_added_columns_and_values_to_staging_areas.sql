ALTER TABLE kalturadw_ds.staging_areas ADD post_transfer_aggregations VARCHAR(255);
UPDATE kalturadw_ds.staging_areas SET aggr_date_field = 'event_date_id', post_transfer_aggregations = '(\'country\',\'domain\',\'entry\',\'partner\',\'plays_views\',\'uid\',\'widget\')' WHERE id = 1;
UPDATE kalturadw_ds.staging_areas SET aggr_date_field = 'event_date_id', post_transfer_aggregations = '(\'fms_sessions\')' WHERE id IN (2);
-- UPDATE kalturadw_ds.staging_areas SET aggr_date_field = 'activity_date_id', post_transfer_aggregations = '(\'partner\')' WHERE id IN (4,5,6,7);
