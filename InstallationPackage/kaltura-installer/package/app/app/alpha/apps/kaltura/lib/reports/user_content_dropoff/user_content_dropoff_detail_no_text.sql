SELECT 
	us.name name,
	count_plays,
	count_plays_25,
	count_plays_50,
	count_plays_75,
	count_plays_100,
	play_through_ratio
FROM
(
	SELECT 
		user_id,
		SUM(count_plays) count_plays,
		SUM(count_plays_25) count_plays_25,
		SUM(count_plays_50) count_plays_50,
		SUM(count_plays_75) count_plays_75,
		SUM(count_plays_100) count_plays_100,
		( SUM(count_plays_100) / SUM(count_plays) ) play_through_ratio
	FROM 
		dwh_hourly_events_context_entry_user_app ev
	WHERE 	{OBJ_ID_CLAUSE} # ev.entry_id in 
		AND partner_id =  {PARTNER_ID} # PARTNER_ID
        AND date_id BETWEEN IF({TIME_SHIFT}>0,(DATE({FROM_DATE_ID}) - INTERVAL 1 DAY)*1, {FROM_DATE_ID})  
    			AND     IF({TIME_SHIFT}<=0,(DATE({TO_DATE_ID}) + INTERVAL 1 DAY)*1, {TO_DATE_ID})
			AND hour_id >= IF (date_id = IF({TIME_SHIFT}>0,(DATE({FROM_DATE_ID}) - INTERVAL 1 DAY)*1, {FROM_DATE_ID}), IF({TIME_SHIFT}>0, 24 - {TIME_SHIFT}, ABS({TIME_SHIFT})), 0)
			AND hour_id < IF (date_id = IF({TIME_SHIFT}<=0,(DATE({TO_DATE_ID}) + INTERVAL 1 DAY)*1, {TO_DATE_ID}), IF({TIME_SHIFT}>0, 24 - {TIME_SHIFT}, ABS({TIME_SHIFT})), 24)
		AND ( count_plays > 0 OR
		  count_plays_25 > 0 OR
		  count_plays_50 > 0 OR
		  count_plays_75 > 0 OR
		  count_plays_100 > 0 )
	GROUP BY user_id
	ORDER BY {SORT_FIELD}
) AS ev_stats, dwh_dim_pusers us
WHERE ev_stats.user_id = us.puser_id
LIMIT {PAGINATION_FIRST},{PAGINATION_SIZE}  /* pagination  */