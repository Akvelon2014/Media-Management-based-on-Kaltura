/*
SELECT d.* FROM kalturadw.dwh_dim_file_ext d, 
		(SELECT file_ext, COUNT(*) FROM kalturadw.dwh_dim_file_ext
			GROUP BY file_ext
			HAVING COUNT(*) > 1) dup
WHERE d.file_ext = dup.file_ext;

UPDATE kalturadw.dwh_dim_flavor_asset
SET	file_ext_id = case file_ext_id when 22 then 21 when 51 then 49 else file_ext_id end;

DELETE FROM kalturadw.dwh_dim_file_ext
WHERE file_ext_id IN (22,51);*/

ALTER TABLE kalturadw.dwh_dim_file_ext CHANGE file_ext file_ext VARCHAR(333);
ALTER TABLE kalturadw.dwh_dim_file_ext ADD UNIQUE KEY (file_ext);
