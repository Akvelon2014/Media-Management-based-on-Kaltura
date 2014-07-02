DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `fms_sessionize`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `fms_sessionize`(
  partition_id INTEGER)
BEGIN
  DECLARE SESSION_DATE_IDS VARCHAR(4000);
  DECLARE FMS_STALE_SESSION_PURGE DATETIME;

  SELECT SUBDATE(NOW(),INTERVAL 3 DAY) INTO FMS_STALE_SESSION_PURGE;

  # FMS_STALE_SESSION_PURGE decides when incomplete session are purged

  # because mysql doesn't support multi-table insert (as opposed to oracle)
  # ods_temp_fms_session_aggr is used as a helper table
  # for storing an intermediate aggregate of sessions in ods_fms_session_events
  # *its basically just an optimization to prevent the same query from running twice on
  # ods_fms_session_events which has an order of magnitude or two less data after aggregation

  # table creations are in the stored procedure because they're temporary tables (get destroyed
  # when the connection is disconnected) and results in self-documenting/less code to manage since
  # the temp tables only serve internal data processing optimizations
  DROP TABLE IF EXISTS ds_temp_fms_session_aggr;
  DROP TABLE IF EXISTS ds_temp_fms_sessions;

  CREATE TEMPORARY TABLE ds_temp_fms_session_aggr (
    agg_session_id       VARCHAR(20) NOT NULL,
    agg_session_time     DATETIME    NOT NULL,
    agg_session_date_id  INT(11)     UNSIGNED,
    agg_con_cs_bytes     BIGINT      UNSIGNED,
    agg_con_sc_bytes     BIGINT      UNSIGNED,
    agg_dis_cs_bytes     BIGINT      UNSIGNED,
    agg_dis_sc_bytes     BIGINT      UNSIGNED,
    agg_partner_id       INT(10)     UNSIGNED
  ) ENGINE = MEMORY;

  CREATE TEMPORARY TABLE ds_temp_fms_sessions (
    session_id         VARCHAR(20) NOT NULL,
    session_time       DATETIME    NOT NULL,
    session_date_id    INT(11)     UNSIGNED,
    session_partner_id INT(10)     UNSIGNED,
    total_bytes        BIGINT      UNSIGNED
   ) ENGINE = MEMORY;


    # 1. aggregate data per session from ds
  INSERT INTO ds_temp_fms_session_aggr (agg_session_id,agg_session_time,agg_session_date_id,
              agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id)
  SELECT session_id,MAX(event_time),MAX(event_date_id),  #regarding the "max" aggregate, see comment below in "on duplicate key"
    SUM(IF(t.event_type='connect',client_to_server_bytes,0)) con_cs_bytes,
    SUM(IF(t.event_type='connect',server_to_client_bytes,0)) con_sc_bytes,
    SUM(IF(t.event_type='disconnect',client_to_server_bytes,0)) dis_cs_bytes,
    SUM(IF(t.event_type='disconnect',server_to_client_bytes,0)) dis_sc_bytes,
    MAX(partner_id) partner_id # assuming there a max of 1 partnerid per session (i.e. no switching between partner in an fms session)
  FROM ds_fms_session_events e
 INNER JOIN kalturadw.dwh_dim_fms_event_type t ON e.event_type_id = t.event_type_id
  WHERE cycle_id = partition_id
  GROUP BY session_id;


  # 2. complete sessions that are "self contained" (have connect, disconnect and partner_id data within the current partition)
  # are considered complete and can be immediately aggregated for the partner
  INSERT INTO ds_temp_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
  SELECT agg_session_id,agg_session_time,agg_session_date_id,agg_partner_id,
  CAST(CAST(agg_dis_sc_bytes AS SIGNED)-CAST(agg_con_sc_bytes AS SIGNED)+CAST(agg_dis_cs_bytes AS SIGNED)-CAST(agg_con_cs_bytes AS SIGNED) AS UNSIGNED)
  FROM ds_temp_fms_session_aggr
  WHERE agg_partner_id IS NOT NULL AND agg_dis_cs_bytes >0 AND agg_con_cs_bytes > 0;



  # 3. incomplete sessions which are missing either a partner id or connect/disconnect data counters are merged into a persistent table
  # the "agg_" column alias prefix is due to a mysql bug regarding same column names in "on duplicate key update"
  INSERT INTO fms_incomplete_sessions (session_id,session_time,updated_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,partner_id)
  SELECT agg_session_id,agg_session_time,NOW() AS agg_update_time,agg_session_date_id,
         agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id
  FROM ds_temp_fms_session_aggr
  WHERE agg_con_cs_bytes = 0 OR agg_dis_cs_bytes = 0 OR agg_partner_id IS NULL
  ON DUPLICATE KEY UPDATE
    # sessions are chronologically classified based on their end date
    # they are also billed based on their end date
    # this is a conscious decision - we don't want sessions to "update" billed days retroactively because it would
    # complicate the billing process
    session_time=GREATEST(session_time,VALUES(session_time)),
    session_date_id=GREATEST(session_date_id,VALUES(session_date_id)),
    # add up bytes
    con_cs_bytes=con_cs_bytes+VALUES(con_cs_bytes),
    con_sc_bytes=con_sc_bytes+VALUES(con_sc_bytes),
    dis_cs_bytes=dis_cs_bytes+VALUES(dis_cs_bytes),
    dis_sc_bytes=dis_sc_bytes+VALUES(dis_sc_bytes),
    # once a partner_id is found, stick to it
    partner_id=IF(partner_id IS NULL,VALUES(partner_id),partner_id),
    # record the last received event
    updated_time=GREATEST(updated_time,VALUES(updated_time));


    # 4. gather newly completed sessions
  INSERT INTO ds_temp_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
  SELECT session_id,session_time,session_date_id,partner_id,
  CAST(CAST(dis_sc_bytes AS SIGNED)-CAST(con_sc_bytes AS SIGNED)+CAST(dis_cs_bytes AS SIGNED)-CAST(con_cs_bytes AS SIGNED) AS UNSIGNED)
  FROM fms_incomplete_sessions
  WHERE partner_id IS NOT NULL AND dis_cs_bytes >0 AND con_cs_bytes > 0;


    # 5. store stale sessions
  INSERT INTO fms_stale_sessions (partner_id,session_id,session_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,last_update_time,purge_time)
  SELECT partner_id,session_id,session_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,updated_time,NOW()
  FROM fms_incomplete_sessions
  WHERE GREATEST(session_time,updated_time) < FMS_STALE_SESSION_PURGE AND (partner_id IS NULL OR dis_cs_bytes =0 OR con_cs_bytes = 0);


  # 6. purge completed and stale sessions
  DELETE FROM fms_incomplete_sessions


  WHERE (partner_id IS NOT NULL AND dis_cs_bytes >0 AND con_cs_bytes > 0) OR
       # we choose the last of session and updated time, so that old files being processed have some time before they're pushed out
        GREATEST(session_time,updated_time) < FMS_STALE_SESSION_PURGE;

  # 7. add all new partner activities to dwh fact table
  INSERT INTO kalturadw.dwh_fact_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
  SELECT session_id,session_time,session_date_id,session_partner_id,total_bytes
  FROM ds_temp_fms_sessions;

  # 8. mark changed dates
  SELECT CAST(GROUP_CONCAT(DISTINCT session_date_id) AS CHAR)
  INTO SESSION_DATE_IDS
  FROM ds_temp_Fms_sessions;
END$$

DELIMITER ;
