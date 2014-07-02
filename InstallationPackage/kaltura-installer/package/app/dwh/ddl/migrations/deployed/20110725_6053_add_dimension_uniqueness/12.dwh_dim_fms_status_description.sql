/*

SELECT d.* FROM kalturadw.dwh_dim_fms_status_description d, 
		(SELECT status_number, event_type, COUNT(*) FROM kalturadw.dwh_dim_fms_status_description
			GROUP BY status_number, event_type
			HAVING COUNT(*) > 1) dup
WHERE d.status_number = dup.status_number AND d.event_type = dup.event_type;

DELETE FROM dwh_dim_fms_status_description WHERE status_description_id IN (104, 105);

*/

ALTER TABLE kalturadw.dwh_dim_fms_status_description ADD UNIQUE KEY (status_number, event_type);
