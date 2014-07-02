DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_partner_billing_data`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `calc_partner_billing_data`(p_date_id INT(11),partner_id VARCHAR(100))
BEGIN
SET @current_date_id=LEAST(p_date_id,DATE(NOW())*1);
SET @current_partner_id=partner_id;
SELECT
	FLOOR(continuous_partner_storage.date_id/100) month_id,
	SUM(continuous_aggr_storage/DAY(LAST_DAY(continuous_partner_storage.date_id))) avg_continuous_aggr_storage_mb,
	SUM(continuous_partner_storage.count_bandwidth) sum_partner_bandwidth_kb
FROM
(	
		SELECT 
			all_times.day_id date_id,
			@current_partner_id partner_id,
			IFNULL(SUM(aggr_p.count_bandwidth_kb), 0) count_bandwidth,
			IFNULL(IF(SUM(IF(aggr_p.bandwidth_source_id=1,1,0)) = 1, SUM(aggr_p.aggr_storage_mb),
				(SELECT aggr_storage_mb FROM dwh_hourly_partner_usage inner_a_p 
				 WHERE 	inner_a_p.partner_id=@current_partner_id AND 
					inner_a_p.date_id<all_times.day_id AND 
					inner_a_p.hour_id = 0 AND 
					inner_a_p.bandwidth_source_id = 1
					ORDER BY inner_a_p.date_id DESC LIMIT 1)), 0) continuous_aggr_storage
		FROM 
			dwh_hourly_partner_usage aggr_p RIGHT JOIN
			dwh_dim_time all_times
			ON (all_times.day_id=aggr_p.date_id 
				AND aggr_p.partner_id=@current_partner_id
				AND aggr_p.hour_id = 0
				)
		WHERE 	
			all_times.day_id>=20081230 AND all_times.day_id <= @current_date_id
		GROUP BY all_times.day_id
	) continuous_partner_storage
	GROUP BY month_id
	WITH ROLLUP;	
END$$

DELIMITER ;
