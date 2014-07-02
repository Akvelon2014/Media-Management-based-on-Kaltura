update kalturadw.aggr_managment 
set is_calculated = 0
where aggr_name = 'domain_referrer' and aggr_day_int like '201006%';