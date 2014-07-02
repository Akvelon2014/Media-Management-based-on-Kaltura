DELIMITER $$

USE kalturadw$$

DROP VIEW IF EXISTS dwh_view_partners_monthly_billing_last_updated_at$$
CREATE VIEW `kalturadw`.`dwh_view_partners_monthly_billing_last_updated_at` AS (

	SELECT  FLOOR(months.day_id / 100) AS month_id,
		p.partner_id AS partner_id,
		MAX(p.updated_at) AS updated_at
	FROM dwh_dim_time months, dwh_dim_partners_billing p 
	WHERE 	p.updated_at <= LAST_DAY(months.day_id) 
		AND months.day_id = LAST_DAY(months.day_id)*1
	GROUP BY FLOOR(months.day_id/100),p.partner_id

)$$

DELIMITER ;
