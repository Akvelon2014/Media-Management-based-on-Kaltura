/*
SELECT d.* FROM kalturadw.dwh_dim_fms_event_type d, 
		(SELECT event_type, COUNT(*) FROM kalturadw.dwh_dim_fms_event_type
			GROUP BY event_type
			HAVING COUNT(*) > 1) dup
WHERE d.event_type = dup.event_type;

DELETE FROM dwh_dim_fms_event_type WHERE 0=1;
*/

ALTER TABLE kalturadw.dwh_dim_fms_event_type ADD UNIQUE KEY (event_type);
