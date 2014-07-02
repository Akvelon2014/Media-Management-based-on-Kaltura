DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `fms_sessionize`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `fms_sessionize`(
  partition_id INTEGER)
BEGIN
  DECLARE SESSION_DATE_IDS VARCHAR(4000);
  DECLARE FMS_STALE_SESSION_PURGE DATETIME;
 
  SELECT SUBDATE(NOW(),INTERVAL 3 DAY) INTO FMS_STALE_SESSION_PURGE;
 
  DROP TABLE IF EXISTS ds_temp_fms_session_aggr;
  DROP TABLE IF EXISTS ds_temp_fms_sessions;
 
  CREATE TEMPORARY TABLE ds_temp_fms_session_aggr (
    agg_session_id       	VARCHAR(20) NOT NULL,
    agg_session_time     	DATETIME    NOT NULL,
    agg_client_ip	 	VARCHAR(15),
    agg_client_ip_number 	INT(10) UNSIGNED,
    agg_client_country_id 	INT(10) UNSIGNED,
    agg_client_location_id 	INT(10) UNSIGNED,
    agg_session_date_id  	INT(11)     UNSIGNED,
    agg_con_cs_bytes     	BIGINT      UNSIGNED,
    agg_con_sc_bytes     	BIGINT      UNSIGNED,
    agg_dis_cs_bytes     	BIGINT      UNSIGNED,
    agg_dis_sc_bytes     	BIGINT      UNSIGNED,
    agg_partner_id       	INT(10)     UNSIGNED
  ) ENGINE = MEMORY;
 
  CREATE TEMPORARY TABLE ds_temp_fms_sessions (
    session_id         		VARCHAR(20) NOT NULL,
    session_time       		DATETIME    NOT NULL,
    session_date_id    		INT(11)     UNSIGNED,
    session_client_ip	 	VARCHAR(15),
    session_client_ip_number 	INT(10) UNSIGNED,
    session_client_country_id 	INT(10) UNSIGNED,
    session_client_location_id 	INT(10) UNSIGNED,
    session_partner_id 		INT(10)     UNSIGNED,
    total_bytes        		BIGINT      UNSIGNED
   ) ENGINE = MEMORY;
    
  INSERT INTO ds_temp_fms_session_aggr (agg_session_id,agg_session_time,agg_session_date_id, agg_client_ip, agg_client_ip_number, agg_client_country_id, agg_client_location_id,
              agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id)
  SELECT session_id,MAX(event_time),MAX(event_date_id), MAX(client_ip), MAX(client_ip_number), MAX(client_country_id), MAX(client_location_id), 
    SUM(IF(t.event_type='connect',client_to_server_bytes,0)) con_cs_bytes,
    SUM(IF(t.event_type='connect',server_to_client_bytes,0)) con_sc_bytes,
    SUM(IF(t.event_type='disconnect',client_to_server_bytes,0)) dis_cs_bytes,
    SUM(IF(t.event_type='disconnect',server_to_client_bytes,0)) dis_sc_bytes,
    MAX(partner_id) partner_id 
  FROM ds_fms_session_events e
 INNER JOIN kalturadw.dwh_dim_fms_event_type t ON e.event_type_id = t.event_type_id
  WHERE cycle_id = partition_id
  GROUP BY session_id;
 
 
    INSERT INTO ds_temp_fms_sessions (session_id,session_time,session_date_id, session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id, session_partner_id,total_bytes)
  SELECT agg_session_id,agg_session_time,agg_session_date_id,agg_client_ip, agg_client_ip_number, agg_client_country_id, agg_client_location_id, agg_partner_id,
  CAST(CAST(agg_dis_sc_bytes AS SIGNED)-CAST(agg_con_sc_bytes AS SIGNED)+CAST(agg_dis_cs_bytes AS SIGNED)-CAST(agg_con_cs_bytes AS SIGNED) AS UNSIGNED)
  FROM ds_temp_fms_session_aggr
  WHERE agg_partner_id IS NOT NULL AND agg_dis_cs_bytes >0 AND agg_con_cs_bytes > 0;
  
  
  INSERT INTO fms_incomplete_sessions (session_id,session_time,updated_time,session_date_id, session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id, con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,partner_id)
  SELECT agg_session_id,agg_session_time,NOW() AS agg_update_time,agg_session_date_id,agg_client_ip, agg_client_ip_number, agg_client_country_id, agg_client_location_id,
         agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id
  FROM ds_temp_fms_session_aggr
  WHERE agg_con_cs_bytes = 0 OR agg_dis_cs_bytes = 0 OR agg_partner_id IS NULL
  ON DUPLICATE KEY UPDATE
    session_time=GREATEST(session_time,VALUES(session_time)),
    session_date_id=GREATEST(session_date_id,VALUES(session_date_id)),
    session_client_ip=VALUES(session_client_ip),
    session_client_ip_number=VALUES(session_client_ip_number), 
    session_client_location_id=VALUES(session_client_location_id),
    session_client_country_id=VALUES(session_client_country_id),
    con_cs_bytes=con_cs_bytes+VALUES(con_cs_bytes),
    con_sc_bytes=con_sc_bytes+VALUES(con_sc_bytes),
    dis_cs_bytes=dis_cs_bytes+VALUES(dis_cs_bytes),
    dis_sc_bytes=dis_sc_bytes+VALUES(dis_sc_bytes),
    partner_id=IF(partner_id IS NULL,VALUES(partner_id),partner_id),
    updated_time=GREATEST(updated_time,VALUES(updated_time));
  
  INSERT INTO ds_temp_fms_sessions (session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,session_partner_id,total_bytes)
  SELECT session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,partner_id,
  CAST(CAST(dis_sc_bytes AS SIGNED)-CAST(con_sc_bytes AS SIGNED)+CAST(dis_cs_bytes AS SIGNED)-CAST(con_cs_bytes AS SIGNED) AS UNSIGNED)
  FROM fms_incomplete_sessions
  WHERE partner_id IS NOT NULL AND dis_cs_bytes >0 AND con_cs_bytes > 0;
    
  INSERT INTO fms_stale_sessions (partner_id,session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,last_update_time,purge_time)
  SELECT partner_id,session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,updated_time,NOW()
  FROM fms_incomplete_sessions
  WHERE GREATEST(session_time,updated_time) < FMS_STALE_SESSION_PURGE AND (partner_id IS NULL OR dis_cs_bytes =0 OR con_cs_bytes = 0);
  
  DELETE FROM fms_incomplete_sessions
  WHERE (partner_id IS NOT NULL AND dis_cs_bytes >0 AND con_cs_bytes > 0) OR
        GREATEST(session_time,updated_time) < FMS_STALE_SESSION_PURGE;
  
  INSERT INTO kalturadw.dwh_fact_fms_sessions (session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,session_partner_id,total_bytes)
  SELECT session_id,session_time,session_date_id,session_client_ip, session_client_ip_number, session_client_country_id, session_client_location_id,session_partner_id,total_bytes
  FROM ds_temp_fms_sessions;
  
  SELECT CAST(GROUP_CONCAT(DISTINCT session_date_id) AS CHAR)
  INTO SESSION_DATE_IDS
  FROM ds_temp_fms_sessions;
END$$

DELIMITER ;
