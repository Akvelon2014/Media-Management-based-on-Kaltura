insert into kalturadw.aggr_managment (aggr_name, aggr_day, aggr_day_int, is_calculated) 
select distinct 'domain_referrer', date(aggr_day_int), aggr_day_int, if(aggr_day_int > date(now())*1, 0, 1) from kalturadw.aggr_managment 
