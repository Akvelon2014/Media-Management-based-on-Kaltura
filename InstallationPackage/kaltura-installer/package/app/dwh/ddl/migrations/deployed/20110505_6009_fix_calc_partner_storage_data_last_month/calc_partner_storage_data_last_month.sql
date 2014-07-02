DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `calc_partner_storage_data_last_month`$$

CREATE DEFINER=`root`@`localhost` FUNCTION `calc_partner_storage_data_last_month`(month_id INT ,partner_id INT ) RETURNS INT(11)
    DETERMINISTIC
BEGIN
	DECLARE avg_cont_aggr_storage INT;
	SET @current_month_id=month_id;
	SET @current_partner_id=partner_id;

	SELECT	SUM(continuous_aggr_storage/DAY(LAST_DAY(continuous_partner_storage.date_id))) avg_continuous_aggr_storage_mb
	INTO avg_cont_aggr_storage
	FROM (		SELECT 	all_times.day_id date_id,
				aggr_p.aggr_storage aggr_storage,
				IF(aggr_p.aggr_storage IS NOT NULL, aggr_p.aggr_storage,
					(SELECT aggr_storage FROM dwh_hourly_partner inner_a_p 
					WHERE 	inner_a_p.partner_id=@current_partner_id AND 
						inner_a_p.date_id<all_times.day_id AND 
						inner_a_p.hour_id = 0 AND
						inner_a_p.aggr_storage IS NOT NULL ORDER BY inner_a_p.date_id DESC LIMIT 1)) continuous_aggr_storage
			FROM 	dwh_hourly_partner aggr_p RIGHT JOIN
				dwh_dim_time all_times
				ON (all_times.day_id=aggr_p.date_id 
					AND all_times.day_id>=20081230
					AND kalturadw.calc_month_id(all_times.day_id) <= @current_month_id 
					AND aggr_p.partner_id=@current_partner_id)
			WHERE 	all_times.day_id>=20081230 AND kalturadw.calc_month_id(all_times.day_id) <= @current_month_id AND aggr_p.hour_id = 0
		) continuous_partner_storage
	WHERE 	kalturadw.calc_month_id(continuous_partner_storage.date_id)=@current_month_id
	GROUP BY kalturadw.calc_month_id(continuous_partner_storage.date_id);

	RETURN avg_cont_aggr_storage;
END$$

DELIMITER ;
