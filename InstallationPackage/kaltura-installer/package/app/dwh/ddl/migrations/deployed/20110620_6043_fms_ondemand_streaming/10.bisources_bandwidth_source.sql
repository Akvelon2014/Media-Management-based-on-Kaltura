UPDATE kalturadw_bisources.bisources_bandwidth_source
SET bandwidth_source_name = CASE bandwidth_source_id WHEN 4 THEN 'akamai_vod_http' WHEN 5 THEN 'akamai_live_fms' END
WHERE bandwidth_source_id IN (4,5);

INSERT INTO kalturadw_bisources.bisources_bandwidth_source (bandwidth_source_id, bandwidth_source_name) values (6, 'akamai_vod_fms');
