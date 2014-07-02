/*Search if no new data was added to dwh_hourly_events_widget in the day before yesterday*/
SELECT 'Widget aggregation did not enter any new data' stat, (DATE(NOW())-INTERVAL 2 DAY)*1 DAY
FROM kalturadw.dwh_hourly_events_widget
WHERE date_id = (DATE(NOW())-INTERVAL 2 DAY)*1
HAVING COUNT(*)  = 0