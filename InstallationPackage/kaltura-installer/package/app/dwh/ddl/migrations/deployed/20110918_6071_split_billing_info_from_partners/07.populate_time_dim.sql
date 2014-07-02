DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `populate_time_dim`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `populate_time_dim`(start_date datetime, end_date datetime)
    DETERMINISTIC
BEGIN    

    WHILE start_date <= end_date DO
        INSERT IGNORE INTO kalturadw.dwh_dim_time 
        SELECT 1*DATE(d), d, YEAR(d), MONTH(d), DAYOFYEAR(d),DAYOFMONTH(d),DAYOFWEEK(d),WEEK(d),DAYNAME(d),DATE_FORMAT(d,'%a'),MONTHNAME(d), DATE_FORMAT(d, '%b'), QUARTER(d)
        FROM(SELECT start_date d) a;
        
        SET start_date = DATE_ADD(start_date, INTERVAL 1 DAY);
    END WHILE;
    
END$$

DELIMITER ;
