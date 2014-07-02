DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `calc_partner_storage_data_time_range`$$

CREATE DEFINER=`root`@`localhost` FUNCTION `calc_partner_storage_data_time_range`(start_date_id INT, end_date_id INT ,partner_id INT ) RETURNS DECIMAL(19,4)
    DETERMINISTIC
BEGIN	
	DECLARE avg_cont_aggr_storage DECIMAL (19,4);
	SET @current_partner_id=partner_id;
	SET @current_start_date_id=start_date_id;
	SET @current_end_date_id=LEAST(end_date_id, DATE(NOW())*1);
	
	SELECT	SUM(continuous_aggr_storage/DAY(LAST_DAY(continuous_partner_storage.date_id))) avg_continuous_aggr_storage_mb
	INTO avg_cont_aggr_storage
	FROM (SELECT * FROM (
			SELECT 	all_times.day_id date_id,
				IF(SUM(aggr_p.aggr_storage_mb) IS NOT NULL, SUM(aggr_p.aggr_storage_mb),
                                (SELECT aggr_storage_mb FROM dwh_hourly_partner_usage inner_a_p 
                                 WHERE  inner_a_p.partner_id=@current_partner_id AND 
                                        inner_a_p.date_id<all_times.day_id AND 
                                        inner_a_p.aggr_storage_mb IS NOT NULL 
                                        AND inner_a_p.hour_id = 0 
                                        ORDER BY inner_a_p.date_id DESC LIMIT 1)) continuous_aggr_storage
			FROM 	dwh_hourly_partner_usage aggr_p RIGHT JOIN
				dwh_dim_time all_times
				ON (all_times.day_id=aggr_p.date_id 
					AND aggr_p.bandwidth_source_id = 1 
					AND aggr_p.hour_id = 0 
					AND aggr_p.partner_id=@current_partner_id)
			WHERE 	all_times.day_id BETWEEN 20081230 AND all_times.day_id<=@current_end_date_id
			GROUP BY all_times.day_id) results
			WHERE date_id >= @current_start_date_id AND date_id <=@current_end_date_id
		) continuous_partner_storage;
	RETURN avg_cont_aggr_storage;
END$$

DELIMITER ;
