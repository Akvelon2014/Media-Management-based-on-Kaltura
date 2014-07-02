UPDATE kalturadw.dwh_hourly_partner_usage
set aggr_storage_mb = null where aggr_storage_mb = 0;

ALTER TABLE kalturadw.dwh_hourly_partner_usage CHANGE aggr_storage_mb aggr_storage_mb DECIMAL (19,4);
