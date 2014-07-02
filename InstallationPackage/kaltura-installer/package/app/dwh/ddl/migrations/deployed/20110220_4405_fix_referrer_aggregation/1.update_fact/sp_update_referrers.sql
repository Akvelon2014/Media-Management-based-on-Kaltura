DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `update_referrers`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `update_referrers`()
BEGIN
	DECLARE v_date_id INT;
	DECLARE done INT DEFAULT 0;	
	DECLARE update_referrers_cursor CURSOR FOR SELECT date_id FROM kalturadw.temp_referrer_update WHERE is_calculated = 0 ORDER BY date_id;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	OPEN update_referrers_cursor;
	
	read_loop: LOOP
		FETCH update_referrers_cursor INTO v_date_id;
		IF done THEN
			LEAVE read_loop;
		END IF;
		
		UPDATE kalturadw.temp_referrer_update SET start_time = NOW() WHERE date_id = v_date_id;
		
		INSERT IGNORE INTO kalturadw.dwh_dim_referrer(referrer)
			SELECT DISTINCT IFNULL(referrer,'')
			FROM kalturadw.dwh_fact_events
			WHERE event_date_id BETWEEN v_date_id AND (DATE(v_date_id) + INTERVAL 1 DAY)*1;
		
		UPDATE kalturadw.dwh_fact_events e, kalturadw.dwh_dim_referrer r SET e.referrer_id = r.referrer_id
		WHERE event_date_id BETWEEN v_date_id AND (DATE(v_date_id) + INTERVAL 1 DAY)*1
		AND IFNULL(e.referrer,'') = r.referrer;
	
		UPDATE kalturadw.temp_referrer_update SET end_time = NOW(), is_calculated = 1 WHERE date_id = v_date_id;
	END LOOP;
	CLOSE update_referrers_cursor;
	
    END$$

DELIMITER ;