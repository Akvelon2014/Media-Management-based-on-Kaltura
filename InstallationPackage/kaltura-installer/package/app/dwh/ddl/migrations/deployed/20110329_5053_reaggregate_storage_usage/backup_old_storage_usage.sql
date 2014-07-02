CREATE TABLE kalturadw.temp_aggr_old_storage_usage AS 
SELECT partner_id, date_id, count_storage FROM kalturadw.dwh_hourly_partner
WHERE count_storage IS NOT NULL AND count_storage > 0
AND date_id < 20100119;