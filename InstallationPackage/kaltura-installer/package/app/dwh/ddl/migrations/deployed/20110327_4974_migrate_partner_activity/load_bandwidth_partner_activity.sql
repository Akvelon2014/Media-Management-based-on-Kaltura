INSERT INTO kalturadw.dwh_fact_bandwidth_usage (file_id, partner_id, activity_date_id, activity_hour_id, bandwidth_source_id, bandwidth_bytes)
SELECT -1, IFNULL(partner_id, -1), activity_date_id, activity_hour_id, partner_sub_activity_id, SUM(amount)*1024 FROM kalturadw.dwh_fact_partner_activities
WHERE partner_activity_id  = 1 
AND partner_sub_activity_id IN (1,2,3,4)
AND activity_date_id between 20110101 and 20110320
GROUP BY partner_id, activity_date, activity_date_id, activity_hour_id, partner_sub_activity_id
