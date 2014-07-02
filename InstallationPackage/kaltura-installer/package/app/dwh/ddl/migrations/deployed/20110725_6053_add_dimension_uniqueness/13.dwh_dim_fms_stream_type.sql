/*

SELECT d.* FROM kalturadw.dwh_dim_fms_stream_type d, 
		(SELECT stream_type, COUNT(*) FROM kalturadw.dwh_dim_fms_stream_type
			GROUP BY stream_type
			HAVING COUNT(*) > 1) dup
WHERE d.stream_type = dup.stream_type;

DELETE FROM dwh_dim_fms_stream_type WHERE stream_type_id IN (7, 9, 14, 15);

*/

ALTER TABLE kalturadw.dwh_dim_fms_stream_type ADD UNIQUE KEY (stream_type);
