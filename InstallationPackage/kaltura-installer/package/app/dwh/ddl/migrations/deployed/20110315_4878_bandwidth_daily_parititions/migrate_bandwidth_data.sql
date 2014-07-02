INSERT INTO kalturadw.dwh_fact_bandwidth_usage_new (file_id, partner_id, activity_date_id, activity_hour_id, bandwidth_source_id, url, bandwidth_bytes)
SELECT file_id, partner_id, activity_date_id, activity_hour_id, bandwidth_source_id, url, bandwidth_kb*1000 FROM kalturadw.dwh_fact_bandwidth_usage;


RENAME TABLE kalturadw.dwh_fact_bandwidth_usage TO kalturadw.dwh_fact_bandwidth_usage_old;
RENAME TABLE kalturadw.dwh_fact_bandwidth_usage_new TO kalturadw.dwh_fact_bandwidth_usage;
