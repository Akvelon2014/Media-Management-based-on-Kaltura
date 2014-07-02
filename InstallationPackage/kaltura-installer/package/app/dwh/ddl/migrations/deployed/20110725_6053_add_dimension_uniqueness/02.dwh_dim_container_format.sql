/*

SELECT d.* FROM kalturadw.dwh_dim_container_format d, 
		(SELECT container_format, COUNT(*) FROM kalturadw.dwh_dim_container_format
			GROUP BY container_format
			HAVING COUNT(*) > 1) dup
WHERE d.container_format = dup.container_format;

UPDATE kalturadw.dwh_dim_media_info
SET	container_format_id = case container_format_id when 3 then 2 when 19 then 18 when 29 then 28 else container_format_id end;

UPDATE kalturadw.dwh_dim_flavor_asset
SET	container_format_id = case container_format_id when 3 then 2 when 19 then 18 when 29 then 28 else container_format_id end;

DELETE FROM kalturadw.dwh_dim_container_format
WHERE container_format_id IN (3,19,29);
*/

ALTER TABLE kalturadw.dwh_dim_container_format CHANGE container_format container_format VARCHAR(333);
ALTER TABLE kalturadw.dwh_dim_container_format ADD UNIQUE KEY (container_format);
