/*
SELECT d.* FROM kalturadw.dwh_dim_fms_client_protocol d, 
		(SELECT client_protocol, COUNT(*) FROM kalturadw.dwh_dim_fms_client_protocol
			GROUP BY client_protocol
			HAVING COUNT(*) > 1) dup
WHERE d.client_protocol = dup.client_protocol;

DELETE FROM dwh_dim_fms_client_protocol
WHERE client_protocol IN (11,12,14,15);
*/

ALTER TABLE kalturadw.dwh_dim_fms_client_protocol ADD UNIQUE KEY (client_protocol);
