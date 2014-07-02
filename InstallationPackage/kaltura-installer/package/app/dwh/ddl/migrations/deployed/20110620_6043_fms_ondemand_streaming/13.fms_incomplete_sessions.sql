DELETE FROM kalturadw_ds.fms_incomplete_sessions USING kalturadw_ds.fms_incomplete_sessions INNER JOIN (SELECT session_id, MAX(updated_time) updated_time FROM kalturadw_ds.fms_incomplete_sessions
GROUP BY session_id
HAVING COUNT(*) > 1) dup_session_ids
WHERE fms_incomplete_sessions.session_id = dup_session_ids.session_id AND fms_incomplete_sessions.updated_time = dup_session_ids.updated_time;

ALTER TABLE kalturadw_ds.fms_incomplete_sessions ADD bandwidth_source_id INT(11) NOT NULL DEFAULT 5, ADD PRIMARY KEY (session_id);
ALTER TABLE kalturadw_ds.fms_incomplete_sessions CHANGE bandwidth_source_id bandwidth_source_id INT(11) NOT NULL;
