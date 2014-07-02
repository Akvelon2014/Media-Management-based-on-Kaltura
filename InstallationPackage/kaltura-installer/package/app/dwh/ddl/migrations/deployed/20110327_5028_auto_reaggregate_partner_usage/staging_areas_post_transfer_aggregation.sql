UPDATE kalturadw_ds.staging_areas
SET post_transfer_aggregations = IF(id IN (2,4,5,6,7), '(''partner_usage'')', post_transfer_aggregations),
	aggr_date_field = CASE 	WHEN (id = 2) THEN ('event_date_id')
							WHEN (id IN (4,5,6,7)) THEN ('activity_date_id') 
							ELSE (aggr_date_field)
						END;