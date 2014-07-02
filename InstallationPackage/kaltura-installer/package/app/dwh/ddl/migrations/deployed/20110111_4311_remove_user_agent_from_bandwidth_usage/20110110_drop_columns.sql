ALTER TABLE kalturadw_ds.ds_bandwidth_usage DROP COLUMN user_agent_id; 
ALTER TABLE kalturadw.dwh_fact_bandwidth_usage DROP COLUMN user_agent_id;
TRUNCATE TABLE kalturadw.dwh_dim_user_agent;