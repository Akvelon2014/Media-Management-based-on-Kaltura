DROP TABLE IF EXISTS `kalturadw_ds`.`fms_incomplete_sessions`;
CREATE TABLE  `kalturadw_ds`.`fms_incomplete_sessions` (
  `session_id` varchar(20) DEFAULT NULL,
  `session_time` datetime DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  `session_date_id` int(11) unsigned DEFAULT NULL,
  `con_cs_bytes` bigint(20) unsigned DEFAULT NULL,
  `con_sc_bytes` bigint(20) unsigned DEFAULT NULL,
  `dis_cs_bytes` bigint(20) unsigned DEFAULT NULL,
  `dis_sc_bytes` bigint(20) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `kalturadw_ds`.`fms_stale_sessions`;
CREATE TABLE  `kalturadw_ds`.`fms_stale_sessions` (
  `session_id` varchar(20) DEFAULT NULL,
  `session_time` datetime DEFAULT NULL,
  `last_update_time` datetime DEFAULT NULL,
  `purge_time` datetime DEFAULT NULL,
  `session_date_id` int(11) unsigned DEFAULT NULL,
  `con_cs_bytes` bigint(20) unsigned DEFAULT NULL,
  `con_sc_bytes` bigint(20) unsigned DEFAULT NULL,
  `dis_cs_bytes` bigint(20) unsigned DEFAULT NULL,
  `dis_sc_bytes` bigint(20) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DELIMITER $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`fms_sessionize`$$
CREATE DEFINER=`etl`@`localhost` PROCEDURE  `kalturadw_ds`.`fms_sessionize`(
  partition_id INTEGER)
BEGIN
  DECLARE SESSION_DATE_IDS VARCHAR(4000);
  DECLARE FMS_STALE_SESSION_PURGE datetime;
  select subdate(now(),INTERVAL 3 DAY) into FMS_STALE_SESSION_PURGE;
  # FMS_STALE_SESSION_PURGE decides when incomplete session are purged

  # because mysql doesn't support multi-table insert (as opposed to oracle)
  # ods_temp_fms_session_aggr is used as a helper table
  # for storing an intermediate aggregate of sessions in ods_fms_session_events
  # *its basically just an optimization to prevent the same query from running twice on
  # ods_fms_session_events which has an order of magnitude or two less data after aggregation

  # table creations are in the stored procedure because they're temporary tables (get destroyed
  # when the connection is disconnected) and results in self-documenting/less code to manage since
  # the temp tables only serve internal data processing optimizations
  DROP TABLE IF EXISTS ods_temp_fms_session_aggr;
  DROP TABLE IF EXISTS ods_temp_fms_sessions;

  CREATE TEMPORARY TABLE ods_temp_fms_session_aggr (
    agg_session_id       varchar(20) not null,
    agg_session_time     datetime    not null,
    agg_session_date_id  int(11)     unsigned,
    agg_con_cs_bytes     bigint      unsigned,
    agg_con_sc_bytes     bigint      unsigned,
    agg_dis_cs_bytes     bigint      unsigned,
    agg_dis_sc_bytes     bigint      unsigned,
    agg_partner_id       int(10)     unsigned
  ) engine = memory;

  CREATE TEMPORARY TABLE ods_temp_fms_sessions (
    session_id         varchar(20) not null,
    session_time       datetime    not null,
    session_date_id    int(11)     unsigned,
    session_partner_id int(10)     unsigned,
    total_bytes        bigint      unsigned
   ) engine = memory;


  # 1. aggregate data per session from ods
  insert into ods_temp_fms_session_aggr (agg_session_id,agg_session_time,agg_session_date_id,
              agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id)
  select session_id,max(event_time),max(event_date_id),  #regarding the "max" aggregate, see comment below in "on duplicate key"
    sum(if(t.event_type='connect',client_to_server_bytes,0)) con_cs_bytes,
    sum(if(t.event_type='connect',server_to_client_bytes,0)) con_sc_bytes,
    sum(if(t.event_type='disconnect',client_to_server_bytes,0)) dis_cs_bytes,
    sum(if(t.event_type='disconnect',server_to_client_bytes,0)) dis_sc_bytes,
    max(partner_id) partner_id # assuming there a max of 1 partnerid per session (i.e. no switching between partner in an fms session)
  from ods_fms_session_events e
 inner join kalturadw.dwh_dim_fms_event_type t on e.event_type_id = t.event_type_id
  where file_id = partition_id
  group by session_id;

  # 2. complete sessions that are "self contained" (have connect, disconnect and partner_id data within the current partition)
  # are considered complete and can be immediately aggregated for the partner
  insert into ods_temp_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
  select agg_session_id,agg_session_time,agg_session_date_id,agg_partner_id,
  cast(cast(agg_dis_sc_bytes as signed)-cast(agg_con_sc_bytes as signed)+cast(agg_dis_cs_bytes as signed)-cast(agg_con_cs_bytes as signed) as unsigned)
  from ods_temp_fms_session_aggr
  where agg_partner_id is not null and agg_dis_cs_bytes >0 and agg_con_cs_bytes > 0;

  # 3. incomplete sessions which are missing either a partner id or connect/disconnect data counters are merged into a persistent table
  # the "agg_" column alias prefix is due to a mysql bug regarding same column names in "on duplicate key update"
  insert into fms_incomplete_sessions (session_id,session_time,updated_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,partner_id)
  select agg_session_id,agg_session_time,now() as agg_update_time,agg_session_date_id,
         agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id
  from ods_temp_fms_session_aggr
  where agg_con_cs_bytes = 0 or agg_dis_cs_bytes = 0 or agg_partner_id is null
  on duplicate key update
    # sessions are chronologically classified based on their end date
    # they are also billed based on their end date
    # this is a conscious decision - we don't want sessions to "update" billed days retroactively because it would
    # complicate the billing process
    session_time=greatest(session_time,values(session_time)),
    session_date_id=greatest(session_date_id,values(session_date_id)),
    # add up bytes
    con_cs_bytes=con_cs_bytes+values(con_cs_bytes),
    con_sc_bytes=con_sc_bytes+values(con_sc_bytes),
    dis_cs_bytes=dis_cs_bytes+values(dis_cs_bytes),
    dis_sc_bytes=dis_sc_bytes+values(dis_sc_bytes),
    # once a partner_id is found, stick to it
    partner_id=if(partner_id is null,values(partner_id),partner_id),
    # record the last received event
    updated_time=greatest(updated_time,values(updated_time));

  # 4. gather newly completed sessions
  insert into ods_temp_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
  select session_id,session_time,session_date_id,partner_id,
  cast(cast(dis_sc_bytes as signed)-cast(con_sc_bytes as signed)+cast(dis_cs_bytes as signed)-cast(con_cs_bytes as signed) as unsigned)
  from fms_incomplete_sessions
  where partner_id is not null and dis_cs_bytes >0 and con_cs_bytes > 0;

  # 5. store stale sessions
  insert into fms_stale_sessions (partner_id,session_id,session_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,last_update_time,purge_time)
  select partner_id,session_id,session_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,updated_time,now()
  from fms_incomplete_sessions
  where greatest(session_time,updated_time) < FMS_STALE_SESSION_PURGE and (partner_id is null or dis_cs_bytes =0 or con_cs_bytes = 0);

  # 6. purge completed and stale sessions
  delete from fms_incomplete_sessions
  where (partner_id is not null and dis_cs_bytes >0 and con_cs_bytes > 0) or
       # we choose the last of session and updated time, so that old files being processed have some time before they're pushed out
        greatest(session_time,updated_time) < FMS_STALE_SESSION_PURGE;

  # 7. add all new partner activities to dwh fact table
  insert into kalturadw.dwh_fact_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
  select session_id,session_time,session_date_id,session_partner_id,total_bytes
  from ods_temp_fms_sessions;

  # 8. mark changed dates
  select cast(group_concat(distinct session_date_id) as char)
  into SESSION_DATE_IDS
  from ods_temp_Fms_sessions;

  if length(SESSION_DATE_IDS) > 0 then
    call mark_for_reaggregation(SESSION_DATE_IDS);
  end if;
END $$

DELIMITER ;

use kalturadw_ds;

DROP TABLE IF EXISTS ods_temp_fms_session_aggr;
DROP TABLE IF EXISTS ods_temp_fms_sessions;

CREATE TEMPORARY TABLE ods_temp_fms_session_aggr (
  agg_session_id       varchar(20) not null,
  agg_session_time     datetime    not null,
  agg_session_date_id  int(11)     unsigned,
  agg_con_cs_bytes     bigint      unsigned,
  agg_con_sc_bytes     bigint      unsigned,
  agg_dis_cs_bytes     bigint      unsigned,
  agg_dis_sc_bytes     bigint      unsigned,
  agg_partner_id       int(10)     unsigned
) engine = MyISAM;

CREATE TEMPORARY TABLE ods_temp_fms_sessions (
  session_id         varchar(20) not null,
  session_time       datetime    not null,
  session_date_id    int(11)     unsigned,
  session_partner_id int(10)     unsigned,
  total_bytes        bigint      unsigned
 ) engine = MyISAM;

 # 1. aggregate data per session from ods
insert into ods_temp_fms_session_aggr (agg_session_id,agg_session_time,agg_session_date_id,
            agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id)
select session_id,max(event_time),max(event_date_id),  #regarding the "max" aggregate, see comment below in "on duplicate key"
  sum(if(t.event_type='connect',client_to_server_bytes,0)) con_cs_bytes,
  sum(if(t.event_type='connect',server_to_client_bytes,0)) con_sc_bytes,
  sum(if(t.event_type='disconnect',client_to_server_bytes,0)) dis_cs_bytes,
  sum(if(t.event_type='disconnect',server_to_client_bytes,0)) dis_sc_bytes,
  max(partner_id) partner_id # assuming there a max of 1 partnerid per session (i.e. no switching between partner in an fms session)
from kalturadw.dwh_fact_fms_session_events e
inner join kalturadw.dwh_dim_fms_event_type t on e.event_type_id = t.event_type_id
where file_id = partition_id
group by session_id;

# 2. complete sessions that are "self contained" (have connect, disconnect and partner_id data within the current partition)
# are considered complete and can be immediately aggregated for the partner
insert into ods_temp_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
select agg_session_id,agg_session_time,agg_session_date_id,agg_partner_id,
cast(cast(agg_dis_sc_bytes as signed)-cast(agg_con_sc_bytes as signed)+cast(agg_dis_cs_bytes as signed)-cast(agg_con_cs_bytes as signed) as unsigned)
from ods_temp_fms_session_aggr
where agg_partner_id is not null and agg_dis_cs_bytes >0 and agg_con_cs_bytes > 0;

# 3. incomplete sessions which are missing either a partner id or connect/disconnect data counters are merged into a persistent table
# the "agg_" column alias prefix is due to a mysql bug regarding same column names in "on duplicate key update"
insert into fms_incomplete_sessions (session_id,session_time,updated_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,partner_id)
select agg_session_id,agg_session_time,now() as agg_update_time,agg_session_date_id,
       agg_con_cs_bytes,agg_con_sc_bytes,agg_dis_cs_bytes,agg_dis_sc_bytes,agg_partner_id
from ods_temp_fms_session_aggr
where agg_con_cs_bytes = 0 or agg_dis_cs_bytes = 0 or agg_partner_id is null
on duplicate key update
  # sessions are chronologically classified based on their end date
  # they are also billed based on their end date
  # this is a conscious decision - we don't want sessions to "update" billed days retroactively because it would
  # complicate the billing process
  session_time=greatest(session_time,values(session_time)),
  session_date_id=greatest(session_date_id,values(session_date_id)),
  # add up bytes
  con_cs_bytes=con_cs_bytes+values(con_cs_bytes),
  con_sc_bytes=con_sc_bytes+values(con_sc_bytes),
  dis_cs_bytes=dis_cs_bytes+values(dis_cs_bytes),
  dis_sc_bytes=dis_sc_bytes+values(dis_sc_bytes),
  # once a partner_id is found, stick to it
  partner_id=if(partner_id is null,values(partner_id),partner_id),
  # record the last received event
  updated_time=greatest(updated_time,values(updated_time));

# 4. gather newly completed sessions
insert into ods_temp_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
select session_id,session_time,session_date_id,partner_id,
cast(cast(dis_sc_bytes as signed)-cast(con_sc_bytes as signed)+cast(dis_cs_bytes as signed)-cast(con_cs_bytes as signed) as unsigned)
from fms_incomplete_sessions
where partner_id is not null and dis_cs_bytes >0 and con_cs_bytes > 0;

# 5. store stale sessions
insert into fms_stale_sessions (partner_id,session_id,session_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,last_update_time,purge_time)
select partner_id,session_id,session_time,session_date_id,con_cs_bytes,con_sc_bytes,dis_cs_bytes,dis_sc_bytes,updated_time,now()
from fms_incomplete_sessions
where greatest(session_time,updated_time) < FMS_STALE_SESSION_PURGE and (partner_id is null or dis_cs_bytes =0 or con_cs_bytes = 0);

# 6. purge completed and stale sessions
delete from fms_incomplete_sessions
where (partner_id is not null and dis_cs_bytes >0 and con_cs_bytes > 0) or
     # we choose the last of session and updated time, so that old files being processed have some time before they're pushed out
      greatest(session_time,updated_time) < FMS_STALE_SESSION_PURGE;

# 7. add all new partner activities to dwh fact table
insert into kalturadw.dwh_fact_fms_sessions (session_id,session_time,session_date_id,session_partner_id,total_bytes)
select session_id,session_time,session_date_id,session_partner_id,total_bytes
from ods_temp_fms_sessions;

DROP TABLE IF EXISTS ods_temp_fms_session_aggr;
DROP TABLE IF EXISTS ods_temp_fms_sessions;

update staging_areas set post_transfer_sp = 'fms_sessionize' where id = 2;
