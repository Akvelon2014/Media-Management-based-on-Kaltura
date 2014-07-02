/*SELECT d.* FROM kalturadw.dwh_dim_flavor_format d, 
		(SELECT flavor_format, COUNT(*) FROM kalturadw.dwh_dim_flavor_format
			GROUP BY flavor_format
			HAVING COUNT(*) > 1) dup
WHERE d.flavor_format = dup.flavor_format;


UPDATE kalturadw.dwh_dim_flavor_params
SET     flavor_format_id = case flavor_format_id when 4 then 3 when 6 then 5 else flavor_format_id end;

UPDATE kalturadw.dwh_dim_flavor_params_output
SET     flavor_format_id = case flavor_format_id when 4 then 3 when 6 then 5 else flavor_format_id end;
	
DELETE FROM kalturadw.dwh_dim_flavor_format
WHERE flavor_format_id IN (4,6);*/

ALTER TABLE kalturadw.dwh_dim_flavor_format CHANGE flavor_format flavor_format VARCHAR(333);
ALTER TABLE kalturadw.dwh_dim_flavor_format ADD UNIQUE KEY (flavor_format);
