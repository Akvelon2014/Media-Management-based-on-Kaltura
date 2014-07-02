/*SELECT d.* FROM kalturadw.dwh_dim_fms_app d, 
		(SELECT fms_app_name, COUNT(*) FROM kalturadw.dwh_dim_fms_app
			GROUP BY fms_app_name
			HAVING COUNT(*) > 1) dup
WHERE d.fms_app_name = dup.fms_app_name;

DELETE FROM kalturadw.dwh_dim_fms_app
WHERE fms_app_id IN (10,13,23,25,32,35,36,38,39,42,43,46,47);

UPDATE  kalturadw.dwh_fact_fms_session_events
SET 	fms_app_id = CASE fms_app_id WHEN 4 THEN 3 ELSE fms_app_id END;
*/

ALTER TABLE kalturadw.dwh_dim_fms_app ADD UNIQUE KEY (fms_app_name);
