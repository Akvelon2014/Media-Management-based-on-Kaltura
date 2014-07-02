DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `populate_new_bandwidth_fact`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `populate_new_bandwidth_fact`()
BEGIN
        DECLARE v_date_id INT;
        DECLARE done INT DEFAULT 0;
        DECLARE populate_new_fact_cursor CURSOR FOR SELECT day_id FROM kalturadw.dwh_dim_time;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
        OPEN populate_new_fact_cursor;

        read_loop: LOOP
                FETCH populate_new_fact_cursor INTO v_date_id;
                IF done THEN
                        LEAVE read_loop;
                END IF;

                INSERT INTO kalturadw.dwh_fact_bandwidth_usage_new (file_id, partner_id, activity_date_id, activity_hour_id, bandwidth_source_id, url, bandwidth_bytes)
                SELECT file_id, partner_id, activity_date_id, activity_hour_id, bandwidth_source_id, url, bandwidth_bytes FROM kalturadw.dwh_fact_bandwidth_usage
                WHERE activity_date_id = v_date_id;
        END LOOP;
        CLOSE populate_new_fact_cursor;

        ALTER TABLE kalturadw.dwh_fact_bandwidth_usage_new
                ADD UNIQUE KEY (`file_id`,`line_number`,`activity_date_id`),
		ADD KEY `partner_id` (`partner_id`);

        RENAME TABLE kalturadw.dwh_fact_bandwidth_usage TO kalturadw.dwh_fact_bandwidth_usage_old;
        RENAME TABLE kalturadw.dwh_fact_bandwidth_usage_new TO kalturadw.dwh_fact_bandwidth_usage;
    END$$

DELIMITER ;

CALL populate_new_bandwidth_fact();
