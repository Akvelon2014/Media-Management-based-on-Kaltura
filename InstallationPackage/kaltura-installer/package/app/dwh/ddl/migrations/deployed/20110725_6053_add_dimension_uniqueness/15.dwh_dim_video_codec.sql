/*
SELECT d.* FROM kalturadw.dwh_dim_video_codec d, 
		(SELECT video_codec, COUNT(*) FROM kalturadw.dwh_dim_video_codec
			GROUP BY video_codec
			HAVING COUNT(*) > 1) dup
WHERE d.video_codec = dup.video_codec;

DELETE FROM kalturadw.dwh_dim_video_codec WHERE video_codec_id IN (15,18, 34,154,177);

UPDATE kalturadw.dwh_dim_media_info
SET     audio_codec_id = case audio_codec_id when 9 then 8 else audio_codec_id end,
        audio_format_id = case audio_format_id when 9 then 8 else audio_format_id end,
		video_codec_id = CASE video_codec_id WHEN 15 THEN 14 WHEN 18 THEN 16 WHEN 34 THEN 33 WHEN 154 THEN 153 WHEN 177 THEN 176 ELSE video_codec_id END,
		video_format_id = CASE video_format_id WHEN 15 THEN 14 WHEN 18 THEN 16 WHEN 34 THEN 33 WHEN 154 THEN 153 WHEN 177 THEN 176 ELSE video_format_id END,
		container_format_id = case container_format_id when 3 then 2 when 19 then 18 when 29 then 28 else container_format_id end;

UPDATE kalturadw.dwh_dim_flavor_params
SET     audio_codec_id = case audio_codec_id when 9 then 8 else audio_codec_id end,
		video_codec_id = CASE video_codec_id WHEN 15 THEN 14 WHEN 18 THEN 16 WHEN 34 THEN 33 WHEN 154 THEN 153 WHEN 177 THEN 176 ELSE video_codec_id END,
		flavor_format_id = case flavor_format_id when 4 then 3 when 6 then 5 else flavor_format_id end;

UPDATE kalturadw.dwh_dim_flavor_params_output
SET     audio_codec_id = case audio_codec_id when 9 then 8 else audio_codec_id end,
		flavor_format_id = case flavor_format_id when 4 then 3 when 6 then 5 else flavor_format_id end,
		video_codec_id = CASE video_codec_id WHEN 15 THEN 14 WHEN 18 THEN 16 WHEN 34 THEN 33 WHEN 154 THEN 153 WHEN 177 THEN 176 ELSE video_codec_id END;

UPDATE kalturadw.dwh_dim_flavor_asset
SET     container_format_id = case container_format_id when 3 then 2 when 19 then 18 when 29 then 28 else container_format_id end,
		file_ext_id = case file_ext_id when 22 then 21 when 51 then 49 else file_ext_id end,
		video_codec_id = CASE video_codec_id WHEN 15 THEN 14 WHEN 18 THEN 16 WHEN 34 THEN 33 WHEN 154 THEN 153 WHEN 177 THEN 176 ELSE video_codec_id END;
*/

ALTER TABLE kalturadw.dwh_dim_video_codec CHANGE video_codec video_codec varchar(333);
ALTER TABLE kalturadw.dwh_dim_video_codec ADD UNIQUE KEY (video_codec);
