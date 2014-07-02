/*Search if no new data was added to dwh_hourly_events_context_entry_user_app in the day before yesterday*/
SELECT 'User aggregation did not enter any new data' stat, (DATE(NOW())-INTERVAL 2 DAY)*1 DAY
FROM kalturadw.dwh_hourly_events_context_entry_user_app
WHERE date_id = (DATE(NOW())-INTERVAL 2 DAY)*1
HAVING COUNT(*)  = 0