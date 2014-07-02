/* 
SELECT d.* FROM kalturadw.dwh_dim_fms_virtual_host d, 
		(SELECT virtual_host, COUNT(*) FROM kalturadw.dwh_dim_fms_virtual_host
			GROUP BY virtual_host
			HAVING COUNT(*) > 1) dup
WHERE d.virtual_host = dup.virtual_host;

DELETE FROM dwh_dim_fms_virtual_host WHERE virtual_host_id IN (4);
*/

ALTER TABLE kalturadw.dwh_dim_fms_virtual_host ADD UNIQUE KEY (virtual_host);
