DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_monthly_billing`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `calc_monthly_billing`(p_month_id INT(11))
BEGIN
	SET @current_month_id=p_month_id;
	
	DELETE FROM kalturadw.dwh_billing
	WHERE month_id = @current_month_id;
	
	
	DROP TABLE IF EXISTS paying_partners;
	CREATE TEMPORARY TABLE paying_partners (
		partner_id 		INT(10),
		partner_parent_id 	INT(10)
		) ENGINE = MEMORY;
	
	INSERT INTO 
		paying_partners
	SELECT 
		dwh_dim_partners.partner_id, parent_partners.partner_id
	FROM 
		kalturadw.dwh_dim_partners USE INDEX (partner_package_indx)
		LEFT OUTER JOIN kalturadw.dwh_dim_partners parent_partners
		ON dwh_dim_partners.partner_parent_id = parent_partners.partner_id
	WHERE 
		dwh_dim_partners.partner_package>1 OR parent_partners.partner_id IS NOT NULL;
	
	
	INSERT INTO kalturadw.dwh_billing (month_id, partner_id, partner_parent_id, plays)
	SELECT
		@current_month_id,
		paying_partners.partner_id,
		paying_partners.partner_parent_id,
		SUM(count_plays)
	FROM 
		kalturadw.dwh_hourly_partner hourly_partner,
		paying_partners
	WHERE 
		hourly_partner.partner_id=paying_partners.partner_id AND 
		hourly_partner.date_id BETWEEN @current_month_id*100 + 1 AND LAST_DAY(@current_month_id*100 + 1)*1
	GROUP BY 
		hourly_partner.partner_id
	ON DUPLICATE KEY UPDATE 
		partner_parent_id=VALUES(partner_parent_id),
		plays=VALUES(plays);
		

	INSERT INTO kalturadw.dwh_billing (month_id, partner_id, partner_parent_id, bandwidth_gb, livestreaming_gb)
	SELECT
		@current_month_id,
		paying_partners.partner_id,
		paying_partners.partner_parent_id,
		SUM(IF(is_live=0,hourly_partner_usage.count_bandwidth_kb,0))/1024/1024,
		SUM(IF(is_live=1,hourly_partner_usage.count_bandwidth_kb,0))/1024/1024
	FROM 
		kalturadw.dwh_hourly_partner_usage hourly_partner_usage,
		paying_partners,
		kalturadw.dwh_dim_bandwidth_source bandwidth_source
	WHERE 
		hourly_partner_usage.partner_id=paying_partners.partner_id AND 
		hourly_partner_usage.bandwidth_source_id = bandwidth_source.bandwidth_source_id AND
		hourly_partner_usage.date_id BETWEEN @current_month_id*100 + 1 AND LAST_DAY(@current_month_id*100 + 1)*1
	GROUP BY 
		hourly_partner_usage.partner_id
	ON DUPLICATE KEY UPDATE 
		partner_parent_id=VALUES(partner_parent_id),
		bandwidth_gb=VALUES(bandwidth_gb),
		livestreaming_gb=VALUES(livestreaming_gb);

	
	INSERT INTO 
		kalturadw.dwh_billing (month_id, partner_id, partner_parent_id, storage_gb)
	SELECT 
		@current_month_id,
		partner_id,
		partner_parent_id,
		calc_partner_storage_data_last_month(@current_month_id, partner_id)/1024 
	FROM 
		paying_partners
	ON DUPLICATE KEY UPDATE 
		partner_parent_id=VALUES(partner_parent_id),
		storage_gb=VALUES(storage_gb);
	
	
	INSERT INTO
		kalturadw.dwh_billing (month_id, partner_id, partner_parent_id, entries)
	SELECT 
		@current_month_id, paying_partners.partner_id, paying_partners.partner_parent_id, COUNT(*) 
	FROM 
		kalturadw.dwh_dim_entries, paying_partners
	WHERE 
		dwh_dim_entries.partner_id = paying_partners.partner_id	AND 
		entry_status_id <> 3  AND
		created_at < (@current_month_id*100 + 1) + INTERVAL 1 MONTH
	GROUP BY 
		paying_partners.partner_id
	ON DUPLICATE KEY UPDATE 
		partner_parent_id=VALUES(partner_parent_id),
		entries=VALUES(entries);

	SELECT * FROM dwh_billing WHERE month_id = @current_month_id;
		
END$$

DELIMITER ;
