INSERT INTO kalturadw.aggr_managment (aggr_name,aggr_day_int,aggr_day,is_calculated,start_time,end_time)
SELECT 'storage_usage_kuser_sync' tn,DATE_FORMAT(DATE(date_field), '%Y%m%d'),DATE(date_field) d,if (DATE(date_field) + interval 1 day >= date(now()), 0, 1) i,NULL ts,NULL  te
FROM kalturadw.dwh_dim_time
WHERE date_field BETWEEN date('2011-01-01') AND date('2015-01-01'); 