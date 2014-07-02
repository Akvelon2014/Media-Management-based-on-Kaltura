/*
SELECT d.* FROM kalturadw.dwh_dim_audio_codec d, 
		(SELECT audio_codec, COUNT(*) FROM kalturadw.dwh_dim_audio_codec
			GROUP BY audio_codec
			HAVING COUNT(*) > 1) dup
WHERE d.audio_codec = dup.audio_codec;

UPDATE kalturadw.dwh_dim_media_info
SET     audio_codec_id = case audio_codec_id when 9 then 8 else audio_codec_id end,
        audio_format_id = case audio_format_id when 9 then 8 else audio_format_id end;

UPDATE kalturadw.dwh_dim_flavor_params
SET     audio_codec_id = case audio_codec_id when 9 then 8 else audio_codec_id end;

UPDATE kalturadw.dwh_dim_flavor_params_output
SET     audio_codec_id = case audio_codec_id when 9 then 8 else audio_codec_id end;

DELETE FROM kalturadw.dwh_dim_audio_codec
WHERE audio_codec_id = 9;
*/

ALTER TABLE kalturadw.dwh_dim_audio_codec CHANGE audio_codec audio_codec VARCHAR(333);
ALTER TABLE kalturadw.dwh_dim_audio_codec ADD UNIQUE KEY (audio_codec);
