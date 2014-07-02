USE kalturadw;

INSERT INTO aggr_managment(aggr_name, aggr_day_int, aggr_day, is_calculated, start_time, end_time)
SELECT 'uid' aggr_name, aggr_day_int, aggr_day, 0 is_calculated, NULL start_time, NULL end_time FROM aggr_managment WHERE aggr_name = 'entry';