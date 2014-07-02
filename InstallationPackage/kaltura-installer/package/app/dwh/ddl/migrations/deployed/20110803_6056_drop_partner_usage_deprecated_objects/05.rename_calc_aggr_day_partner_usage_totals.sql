DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_aggr_day_partner_usage_totals`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_aggr_day_partner_usage_totals_20110803`(date_val DATE)
BEGIN
        
        DROP TABLE IF EXISTS temp_aggr_storage;
        CREATE TEMPORARY TABLE temp_aggr_storage(
                partner_id              INT(11) NOT NULL,
                date_id                 INT(11) NOT NULL,
                hour_id                 TINYINT(4) NOT NULL,
                aggr_storage_mb         DECIMAL(19,4) NOT NULL
        ) ENGINE = MEMORY;
        
        
        INSERT INTO temp_aggr_storage (partner_id, date_id, hour_id, aggr_storage_mb)
        SELECT a.partner_id, a.date_id, a.hour_id, SUM(b.count_storage_mb)
        FROM dwh_hourly_partner_usage a, dwh_hourly_partner_usage b 
        WHERE   a.partner_id=b.partner_id AND a.date_id >=b.date_id 
                AND a.date_id=DATE(date_val)*1 AND a.hour_id = 0 AND a.bandwidth_source_id = 1 
                AND b.hour_id = 0 AND b.bandwidth_source_id = 1 and b.count_storage_mb<>0
        GROUP BY a.date_id, a.hour_id, a.partner_id;
        
        INSERT INTO kalturadw.dwh_hourly_partner_usage (partner_id, date_id, hour_id, bandwidth_source_id, aggr_storage_mb)
        SELECT partner_id, date_id, hour_id, 1  , aggr_storage_mb 
        FROM temp_aggr_storage
        ON DUPLICATE KEY UPDATE aggr_storage_mb=VALUES(aggr_storage_mb);
        
        
        INSERT INTO kalturadw.dwh_hourly_partner (partner_id, date_id, hour_id, aggr_storage)
        SELECT partner_id, date_id, hour_id, aggr_storage_mb 
        FROM temp_aggr_storage
        ON DUPLICATE KEY UPDATE aggr_storage=VALUES(aggr_storage);
END$$

DELIMITER ;

