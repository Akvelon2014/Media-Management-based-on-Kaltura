/*Find aggregations in aggr_managment that cover the time frame of the day before yesterday which didn't run yet*/

SELECT 	aggr_name, 
	DATE(date_id) DATE, 
	MAX(data_insert_time) latest_data_insert_time
FROM kalturadw.aggr_managment
WHERE 	(IFNULL(start_time,DATE(19700101)) < data_insert_time 
			OR
			start_time > end_time /* Handle Failed aggregations*/)
	AND data_insert_time < NOW() - INTERVAL 8 HOUR
GROUP BY date_id, aggr_name
ORDER BY date_id, aggr_name