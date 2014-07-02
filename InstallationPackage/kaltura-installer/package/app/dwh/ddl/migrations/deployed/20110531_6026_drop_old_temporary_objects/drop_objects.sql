DROP TABLE kalturadw.dwh_fact_bandwidth_usage_old;
DROP TABLE kalturadw.dwh_fact_fms_session_events_old;
DROP TABLE kalturadw.dwh_fact_fms_sessions_old;
DROP PROCEDURE IF EXISTS kalturadw.populate_new_bandwidth_fact;
DROP PROCEDURE IF EXISTS kalturadw.populate_new_fms_facts;
DROP PROCEDURE IF EXISTS kalturadw.populate_new_facts;
DROP PROCEDURE IF EXISTS kalturadw_ds.update_referrers;
