/*Search if no new data was added to dwh_hourly_user_usage in the day before yesterday*/
SELECT 'User Usage aggregation did not enter any new data' stat, (DATE(NOW())-INTERVAL 2 DAY)*1 DAY
FROM kalturadw.dwh_hourly_user_usage
WHERE date_id = (DATE(NOW())-INTERVAL 2 DAY)*1
HAVING COUNT(*)  = 0