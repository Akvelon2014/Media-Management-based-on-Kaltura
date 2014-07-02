/* Delete duplicate files, leave only last entry */

USE kalturadw_ds;

CREATE TEMPORARY TABLE files_to_delete
AS (SELECT file_id
FROM kalturadw_ds.files a,
(SELECT MAX(file_id) max_id,file_name
FROM kalturadw_ds.files b
GROUP BY b.file_name
HAVING COUNT(b.file_name) > 1) b
WHERE a.file_name  = b.file_name
AND a.file_id <> b.max_id);

DELETE FROM kalturadw_ds.files
WHERE file_id IN (SELECT * FROM files_to_delete);

ALTER TABLE files ADD CONSTRAINT UNIQUE(file_name);
