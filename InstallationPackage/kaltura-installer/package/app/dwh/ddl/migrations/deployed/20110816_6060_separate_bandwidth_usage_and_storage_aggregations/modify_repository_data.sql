UPDATE kalturadw.aggr_managment SET aggr_name = 'bandwidth_usage' where aggr_name = 'partner_usage';
UPDATE kalturadw_ds.staging_areas SET post_transfer_aggregations = '(\'bandwidth_usage\')' where post_transfer_aggregations = '(\'partner_usage\')';
