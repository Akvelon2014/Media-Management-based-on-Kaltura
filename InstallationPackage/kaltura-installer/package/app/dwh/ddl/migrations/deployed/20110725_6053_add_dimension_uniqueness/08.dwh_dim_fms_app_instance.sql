/*SELECT d.* FROM kalturadw.dwh_dim_fms_app d, 
		(SELECT fms_app_name, COUNT(*) FROM kalturadw.dwh_dim_fms_app
			GROUP BY fms_app_name
			HAVING COUNT(*) > 1) dup
WHERE d.fms_app_name = dup.fms_app_name;

DELETE FROM kalturadw.dwh_dim_fms_app
WHERE fms_app_id IN (10,13,23,25,32,35,36,38,39,42,43,46,47);

UPDATE  kalturadw.dwh_fact_fms_session_events
	app_instance_id = CASE app_instance_id 	WHEN 13 THEN 12 WHEN 15 THEN 14 WHEN 45 THEN 44 WHEN 46 THEN 44 WHEN 48 THEN 47
				WHEN 54 THEN 53 WHEN 55 THEN 53 WHEN 61 THEN 60 WHEN 62 THEN 60 WHEN 70 THEN 69
				WHEN 78 THEN 77 WHEN 85 THEN 84 WHEN 92 THEN 91 WHEN 111 THEN 110 WHEN 115 THEN 114
				WHEN 116 THEN 114 WHEN 118 THEN 117 WHEN 120 THEN 119 WHEN 122 THEN 121 WHEN 131 THEN 130
				WHEN 135 THEN 134 WHEN 138 THEN 137 ELSE app_instance_id END;
*/


ALTER TABLE kalturadw.dwh_dim_fms_app_instance CHANGE app_instance app_instance VARCHAR(333);
ALTER TABLE kalturadw.dwh_dim_fms_app_instance ADD UNIQUE KEY (app_instance);
