DELIMITER $$

DROP FUNCTION IF EXISTS `kalturadw_ds`.`get_ip_country_location`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION kalturadw_ds.`get_ip_country_location`(ip BIGINT) RETURNS VARCHAR(30)
DETERMINISTIC
READS SQL DATA
BEGIN
	DECLARE res VARCHAR(30);
	SELECT CONCAT(country_id,",",location_id)
	INTO res
	FROM kalturadw.dwh_dim_ip_ranges
	WHERE ip_from = (
	SELECT MAX(ip_from) 
	FROM kalturadw.dwh_dim_ip_ranges
	WHERE ip >= ip_from
	) ;
	RETURN res;
    END$$

DELIMITER ;