INSERT INTO kalturadw.aggr_managment (aggr_name, aggr_day_int, aggr_day, is_calculated, start_time, end_time, hour_id)
SELECT aggr_name, aggr_day_int, aggr_day, is_calculated, start_time, end_time, hours.hour_id
FROM kalturadw.aggr_managment aggr, 
(SELECT 1 hour_id UNION 
SELECT 2 UNION
SELECT 3 UNION
SELECT 4 UNION
SELECT 5 UNION
SELECT 6 UNION
SELECT 7 UNION
SELECT 8 UNION
SELECT 9 UNION
SELECT 10 UNION
SELECT 11 UNION
SELECT 12 UNION
SELECT 13 UNION
SELECT 14 UNION
SELECT 15 UNION
SELECT 16 UNION
SELECT 17 UNION
SELECT 18 UNION
SELECT 19 UNION
SELECT 20 UNION
SELECT 21 UNION
SELECT 22 UNION
SELECT 23 ) hours
WHERE aggr_name IN ('partner_usage','storage_usage_kuser_sync','plays_views');
