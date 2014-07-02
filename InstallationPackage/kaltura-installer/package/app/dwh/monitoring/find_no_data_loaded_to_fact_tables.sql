/*Find if no new data has been added to the facts the day before yesterday*/
SELECT 'No new events loaded to dwh_fact_events' stat, (DATE(NOW())-INTERVAL 2 DAY)*1 DAY
FROM 
(
SELECT *
FROM kalturadw.dwh_fact_events
WHERE event_date_id = (DATE(NOW())-INTERVAL 2 DAY)*1
LIMIT 1) a
HAVING COUNT(*) = 0
UNION
SELECT 'No new sessions loaded to dwh_fact_fms_sessions' stat, (DATE(NOW())-INTERVAL 2 DAY)*1 DAY
FROM 
(
SELECT *
FROM kalturadw.dwh_fact_fms_sessions
WHERE session_date_id = (DATE(NOW())-INTERVAL 2 DAY)*1
LIMIT 1) a
HAVING COUNT(*) = 0
UNION
SELECT 'No new data loaded to dwh_fact_bandwidth_usage' stat, (DATE(NOW())-INTERVAL 2 DAY)*1 DAY
FROM 
(
SELECT *
FROM kalturadw.dwh_fact_bandwidth_usage
WHERE activity_date_id = (DATE(NOW())-INTERVAL 2 DAY)*1
LIMIT 1) a
HAVING COUNT(*) = 0