DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `populate_time_dim`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `populate_time_dim`(start_date datetime, end_date datetime)
    DETERMINISTIC
BEGIN    

    WHILE start_date <= end_date DO
	INSERT INTO kalturadw.dwh_dim_time 
	(day_id, date_field, datetime_field, day_eng_name, YEAR, month_str, month_id, month_eng_name, MONTH, day_of_year, day_of_month, 
	day_of_week, week_id, week_of_year, week_eng_name, day_of_week_desc, day_of_week_short_desc, month_desc, month_short_desc, 
	QUARTER, quarter_id, quarter_eng_name)
	SELECT 1*DATE(d), d, d, DATE_FORMAT(d, '%b %e, %Y'), YEAR(d), DATE_FORMAT(d, '%Y-%m'), DATE_FORMAT(d, '%Y%m')*1, DATE_FORMAT(d, '%b-%y'), MONTH(d), DAYOFYEAR(d),DAYOFMONTH(d),
	DAYOFWEEK(d), DATE_FORMAT(d, '%Y%U')*1, WEEK(d), DATE_FORMAT(d, 'Week %U, %Y'), DAYNAME(d),DATE_FORMAT(d,'%a'),MONTHNAME(d), DATE_FORMAT(d, '%b'), 
	QUARTER(d), YEAR(d)*10+QUARTER(d), CONCAT('Quarter ', QUARTER(d), ',', YEAR(d))
        FROM(SELECT start_date d) a;
        
        SET start_date = DATE_ADD(start_date, INTERVAL 1 DAY);
    END WHILE;
    
END$$

DELIMITER ;
CALL populate_time_dim(date_format(now() - interval 30 day,'%Y-%m-%d 00:00:00'), date_format(now() + interval 10 year,'%Y-%m-%d 00:00:00'));
