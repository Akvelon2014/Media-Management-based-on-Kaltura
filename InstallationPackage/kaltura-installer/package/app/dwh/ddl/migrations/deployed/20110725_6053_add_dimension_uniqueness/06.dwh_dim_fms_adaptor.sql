/*
SELECT d.* FROM kalturadw.dwh_dim_fms_adaptor d, 
		(SELECT adaptor, COUNT(*) FROM kalturadw.dwh_dim_fms_adaptor
			GROUP BY adaptor
			HAVING COUNT(*) > 1) dup
WHERE d.adaptor = dup.adaptor;

DELETE FROM kalturadw.dwh_dim_fms_adaptor
WHERE adaptor_id = 4;

UPDATE  kalturadw.dwh_fact_fms_session_events
SET	adaptor_id = CASE adaptor_id WHEN 10 THEN 9 WHEN 13 THEN 12 WHEN 23 THEN 22 WHEN 25 THEN 24
			WHEN 32 THEN 31 WHEN 35 THEN 34 WHEN 36 THEN 34 WHEN 38 THEN 37 WHEN 39 THEN 37 
			WHEN 42 THEN 41 WHEN 43 THEN 41 WHEN 46 THEN 45 WHEN 47 THEN 45 ELSE adaptor_id END;
*/

ALTER TABLE kalturadw.dwh_dim_fms_adaptor ADD UNIQUE KEY (adaptor);
