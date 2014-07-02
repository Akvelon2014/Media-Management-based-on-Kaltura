USE kalturadw_ds;

DROP TABLE IF EXISTS retention_policy;
CREATE TABLE retention_policy (
	table_name VARCHAR(256) NOT NULL,
	archive_start_days_back INT DEFAULT 180,
	archive_delete_days_back INT DEFAULT 365,
	archive_last_partition DATE)
ENGINE=MYISAM DEFAULT CHARSET=utf8;

INSERT INTO retention_policy VALUES 
('dwh_fact_events', 180, 365, DATE('2011-01-01')),
('dwh_fact_bandwidth_usage', 180, 365, DATE('2011-01-01')),
('dwh_fact_fms_session_events', 180, 365, DATE('2011-01-01')),
('dwh_fact_fms_sessions', 180, 365, DATE('2011-01-01'));