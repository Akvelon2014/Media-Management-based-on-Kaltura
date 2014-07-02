DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `fms_sessionize_by_date_id`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `fms_sessionize_by_date_id`(p_event_date_id INT)
BEGIN
	DROP TABLE IF EXISTS ds_temp_fms_session_aggr;
	DROP TABLE IF EXISTS ds_temp_fms_sessions;
 
	CREATE TEMPORARY TABLE ds_temp_fms_session_aggr (
	    agg_session_id       	VARCHAR(20) NOT NULL,
	    agg_session_time     	DATETIME    NOT NULL,
	    agg_client_ip	 	VARCHAR(15),
	    agg_client_ip_number 	INT(10),
	    agg_client_country_id 	INT(10),
	    agg_client_location_id 	INT(10),
	    agg_session_date_id  	INT(11),
	    agg_con_cs_bytes     	BIGINT,
	    agg_con_sc_bytes     	BIGINT,
	    agg_dis_cs_bytes     	BIGINT,
	    agg_dis_sc_bytes     	BIGINT,
	    agg_partner_id       	INT(10),
	    agg_bandwidth_source_id     INT(11)
	  ) ENGINE = MEMORY;
	 
	  CREATE TABLE ds_temp_fms_sessions (
	    session_id         		VARCHAR(20) NOT NULL,
	    session_time       		DATETIME    NOT NULL,
	    session_date_id    		INT(11),
	    session_client_ip	 	VARCHAR(15),
	    session_client_ip_number 	INT(10),
	    session_client_country_id 	INT(10),
	    session_client_location_id 	INT(10),
	    session_partner_id 		INT(10),
	    bandwidth_source_id		INT(11),
	    total_bytes        		BIGINT      
	   ) ENGINE = MEMORY;
	    
	
	INSERT INTO ds_temp_fms_session_aggr (agg_session_id,agg_session_time,agg_session_date_id, agg_client_ip, agg_client_ip_number, agg_client_country_id, agg_client_location_id,
		agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id, agg_bandwidth_source_id)
		SELECT session_id, MAX(event_time), MAX(event_date_id), MAX(client_ip), MAX(client_ip_number), MAX(client_country_id), MAX(client_location_id),  	
			SUM(IF(t.event_type='connect',client_to_server_bytes,0)) con_cs_bytes,
			SUM(IF(t.event_type='connect',server_to_client_bytes,0)) con_sc_bytes,
			SUM(IF(t.event_type='disconnect',client_to_server_bytes,0)) dis_cs_bytes,
			SUM(IF(t.event_type='disconnect',server_to_client_bytes,0)) dis_sc_bytes,
			MAX(partner_id) partner_id, MAX(bandwidth_source_id) bandwidth_source_id
		FROM kalturadw.dwh_fact_fms_session_events e 
		INNER JOIN kalturadw.dwh_dim_fms_event_type t ON e.event_type_id = t.event_type_id
		INNER JOIN files f ON e.file_id = f.file_id
		LEFT OUTER JOIN kalturadw.dwh_dim_fms_bandwidth_source fbs ON (e.fms_app_id = fbs.fms_app_id AND f.process_id = fbs.process_id)
		WHERE e.event_date_id = p_event_date_id 
		GROUP BY session_id
		HAVING MAX(bandwidth_source_id) IS NOT NULL;
	 
	INSERT INTO ds_temp_fms_sessions (session_id,session_time,session_date_id, session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id, session_partner_id, bandwidth_source_id, total_bytes)
		SELECT agg_session_id,agg_session_time,agg_session_date_id,agg_client_ip, agg_client_ip_number, agg_client_country_id, agg_client_location_id, agg_partner_id,agg_bandwidth_source_id,
		GREATEST(agg_dis_sc_bytes - agg_con_sc_bytes + agg_dis_cs_bytes -agg_con_cs_bytes, 0)
		FROM ds_temp_fms_session_aggr
		WHERE agg_partner_id IS NOT NULL AND agg_partner_id NOT IN (100  , -1  , -2  , 0 , 99 ) AND agg_dis_cs_bytes >0 AND agg_con_cs_bytes > 0;
	
	INSERT INTO kalturadw.dwh_fact_fms_sessions (session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,session_partner_id,bandwidth_source_id,total_bytes)
	SELECT session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,session_partner_id,bandwidth_source_id,total_bytes
	FROM ds_temp_fms_sessions
	ON DUPLICATE KEY UPDATE
		total_bytes=VALUES(total_bytes),
		session_partner_id=VALUES(session_partner_id),
		session_time=VALUES(session_time),
		session_client_ip=VALUES(session_client_ip),
		session_client_ip_number=VALUES(session_client_ip_number),
		session_client_country_id=VALUES(session_client_country_id),
		session_client_location_id=VALUES(session_client_location_id),
		bandwidth_source_id=VALUES(bandwidth_source_id);
END$$

DELIMITER ;
