DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `populate_new_fms_facts`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `populate_new_fms_facts`()
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

                INSERT INTO kalturadw.dwh_fact_fms_session_events_new (file_id, event_type_id, event_category_id, event_time, event_time_tz, event_date_id, event_hour_id, context, entry_id, partner_id, external_id, server_ip_number, server_process_id, server_cpu_load, server_memory_load, adaptor_id, virtual_host_id, app_id, app_instance_id, duration_secs, status_id, status_desc_id, client_ip, client_ip_number, client_country_id, client_location_id, client_protocol_id, uri, uri_stem, uri_query, referrer, user_agent, session_id, client_to_server_bytes, server_to_client_bytes, stream_name, stream_query, stream_file_name, stream_type_id, stream_size_bytes, stream_length_secs, stream_position, client_to_server_stream_bytes, server_to_client_stream_bytes, server_to_client_qos_bytes)
                SELECT file_id, event_type_id, event_category_id, event_time, event_time_tz, event_date_id, event_hour_id, context, entry_id, partner_id, external_id, server_ip, server_process_id, server_cpu_load, server_memory_load, adaptor_id, virtual_host_id, app_id, app_instance_id, duration_secs, status_id, status_desc_id, client_ip_str, client_ip, client_country_id, client_location_id, client_protocol_id, uri, uri_stem, uri_query, referrer, user_agent, session_id, client_to_server_bytes, server_to_client_bytes, stream_name, stream_query, stream_file_name, stream_type_id, stream_size_bytes, stream_length_secs, stream_position, client_to_server_stream_bytes, server_to_client_stream_bytes, server_to_client_qos_bytes FROM kalturadw.dwh_fact_fms_session_events
                WHERE event_date_id = v_date_id;
		
		INSERT INTO kalturadw.dwh_fact_fms_sessions_new (session_id, session_time, session_date_id, session_partner_id, total_bytes)
		SELECT session_id, session_time, session_date_id, session_partner_id, total_bytes FROM kalturadw.dwh_fact_fms_sessions
		WHERE session_date_id = v_date_id;
        END LOOP;

        CLOSE populate_new_fact_cursor;

        ALTER TABLE kalturadw.dwh_fact_fms_session_events_new
                ADD UNIQUE KEY (`file_id`,`line_number`,`event_date_id`),
		ADD KEY `partner_id` (`partner_id`);

	ALTER TABLE kalturadw.dwh_fact_fms_sessions_new
                ADD KEY `session_partner_id` (`session_partner_id`);

        RENAME TABLE kalturadw.dwh_fact_fms_session_events TO kalturadw.dwh_fact_fms_session_events_old;
        RENAME TABLE kalturadw.dwh_fact_fms_session_events_new TO kalturadw.dwh_fact_fms_session_events;
        RENAME TABLE kalturadw.dwh_fact_fms_sessions TO kalturadw.dwh_fact_fms_sessions_old;
        RENAME TABLE kalturadw.dwh_fact_fms_sessions_new TO kalturadw.dwh_fact_fms_sessions;
    END$$

DELIMITER ;

CALL populate_new_fms_facts();
