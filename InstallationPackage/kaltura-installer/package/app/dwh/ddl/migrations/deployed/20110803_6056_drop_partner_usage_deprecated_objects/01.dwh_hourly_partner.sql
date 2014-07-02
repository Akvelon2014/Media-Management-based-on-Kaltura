ALTER TABLE kalturadw.dwh_hourly_partner
        change column count_bandwidth count_bandwidth_20110803 BIGINT(20),
        change column count_streaming count_streaming_20110803 BIGINT(20),
        change column count_storage count_storage_20110803 BIGINT(20),
        change column aggr_bandwidth aggr_bandwidth_20110803 BIGINT(20),
        change column aggr_streaming aggr_streaming_20110803 BIGINT(20),
        change column aggr_storage aggr_storage_20110803 BIGINT(20);
