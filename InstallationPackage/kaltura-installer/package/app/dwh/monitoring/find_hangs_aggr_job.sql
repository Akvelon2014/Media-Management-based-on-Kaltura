/* Find aggregations from aggr_managment that have been running for more than an hour*/
SELECT 	aggr_name, 
	DATE(date_id) + INTERVAL hour_id HOUR, 
	data_insert_time, 
	start_time, 
	end_time
	 
	FROM 
	kalturadw.aggr_managment 
	WHERE  date_id < DATE(NOW())*1
			AND start_time < NOW() - INTERVAL 1 HOUR
			AND IFNULL(end_time,DATE(19700101)) < start_time
			AND data_insert_time > IFNULL(end_time,DATE(19700101))
