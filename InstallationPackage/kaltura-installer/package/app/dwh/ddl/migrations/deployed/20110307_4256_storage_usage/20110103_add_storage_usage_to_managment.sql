INSERT INTO kalturadw.aggr_managment (aggr_name,aggr_day_int,aggr_day,is_calculated,start_time,end_time)
SELECT 'storage_usage' tn,DATE_FORMAT(DATE(date_field), '%Y%m%d'),DATE(date_field) d,0 i,NULL ts,NULL  te
FROM kalturadw.dwh_dim_time
WHERE date_field BETWEEN date('2010-01-13') AND date('2015-01-01'); 