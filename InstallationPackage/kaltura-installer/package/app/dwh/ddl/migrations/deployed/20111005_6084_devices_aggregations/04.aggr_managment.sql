USE kalturadw;

DELETE FROM aggr_managment where aggr_name = 'devices';

INSERT INTO aggr_managment (aggr_name, aggr_day_int, aggr_day, hour_id, is_calculated)
SELECT distinct 'devices' aggr_name, aggr_day_int, aggr_day, hour_id, IF(aggr_day_int <= date(now())*1,1,0) FROM aggr_managment;
