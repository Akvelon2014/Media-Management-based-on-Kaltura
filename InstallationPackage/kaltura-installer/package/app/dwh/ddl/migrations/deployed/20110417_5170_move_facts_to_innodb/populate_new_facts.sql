DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `populate_new_facts`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `populate_new_facts`()
BEGIN
	DECLARE v_start_date_id INT;
	DECLARE v_end_date_id INT;
	DECLARE done INT DEFAULT 0;	
	DECLARE populate_new_fact_cursor CURSOR FOR SELECT day_id start_date_id, (DATE(day_id) + INTERVAL 1 MONTH)*1 end_date_id FROM kalturadw.dwh_dim_time WHERE day_of_month = 1;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	OPEN populate_new_fact_cursor;
	
	read_loop: LOOP
		FETCH populate_new_fact_cursor INTO v_start_date_id, v_end_date_id;
		IF done THEN
			LEAVE read_loop;
		END IF;
		
		INSERT INTO kalturadw.dwh_fact_events_innodb
		SELECT file_id, event_id, event_type_id, client_version, event_time, event_date_id, event_hour_id, session_id, 
		partner_id, entry_id, unique_viewer, widget_id, ui_conf_id, uid, current_point, duration, user_ip, 
		user_ip_number, country_id, location_id, process_duration, control_id, seek, new_point, domain_id, 
		entry_media_type_id, entry_partner_id, referrer_id FROM kalturadw.dwh_fact_events
		WHERE event_time >= DATE(v_start_date_id) AND event_time < DATE(v_end_date_id);
	
		INSERT INTO kalturadw.dwh_fact_bandwidth_usage_innodb
		SELECT * FROM kalturadw.dwh_fact_bandwidth_usage
		WHERE activity_date_id >= v_start_date_id AND activity_date_id < v_end_date_id;
		
		INSERT INTO kalturadw.dwh_fact_fms_session_events_innodb
		SELECT * FROM kalturadw.dwh_fact_fms_session_events
		WHERE event_time >= DATE(v_start_date_id) AND event_time < DATE(v_end_date_id);
	
		INSERT INTO kalturadw.dwh_fact_fms_sessions_innodb
		SELECT * FROM kalturadw.dwh_fact_fms_sessions
		WHERE session_time >= DATE(v_start_date_id) AND session_time < DATE(v_end_date_id);
	END LOOP;
	CLOSE populate_new_fact_cursor;
	
	ALTER TABLE kalturadw.dwh_fact_events_innodb
		ADD PRIMARY KEY (`file_id`,`event_id`,`event_date_id`),
		ADD KEY `Entry_id` (`entry_id`),
		ADD KEY `partner_id_event_type_id_time` (`partner_id`,`event_type_id`,`event_time`),
		ADD KEY `event_date_id` (`event_date_id`),
		ADD KEY `domain_id` (`domain_id`);

	ALTER TABLE kalturadw.dwh_fact_bandwidth_usage_innodb
		ADD KEY `partner_id` (`partner_id`),
		ADD KEY `file_id` (`file_id`);
 
	ALTER TABLE kalturadw.dwh_fact_fms_session_events_innodb
		ADD KEY `partner_id_event_type_id_time` (`partner_id`,`event_type_id`,`event_time`);
	
	RENAME TABLE kalturadw.dwh_fact_events TO kalturadw.dwh_fact_events_myisam;
	RENAME TABLE kalturadw.dwh_fact_events_innodb TO kalturadw.dwh_fact_events;
	RENAME TABLE kalturadw.dwh_fact_bandwidth_usage TO kalturadw.dwh_fact_bandwidth_usage_myisam;
	RENAME TABLE kalturadw.dwh_fact_bandwidth_usage_innodb TO kalturadw.dwh_fact_bandwidth_usage;
	RENAME TABLE kalturadw.dwh_fact_fms_session_events TO kalturadw.dwh_fact_fms_session_events_myisam;
	RENAME TABLE kalturadw.dwh_fact_fms_session_events_innodb TO kalturadw.dwh_fact_fms_session_events;
	RENAME TABLE kalturadw.dwh_fact_fms_sessions TO kalturadw.dwh_fact_fms_sessions_myisam;
	RENAME TABLE kalturadw.dwh_fact_fms_sessions_innodb TO kalturadw.dwh_fact_fms_sessions;
    END$$

DELIMITER ;

CALL kalturadw.populate_new_facts();