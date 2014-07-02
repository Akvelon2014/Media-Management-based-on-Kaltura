insert into kalturadw.temp_referrer_update (date_id, is_calculated)
select distinct aggr_day_int, 0 from kalturadw.aggr_managment where aggr_day_int < DATE(now())*1;