DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `add_partitions`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `add_partitions`()
BEGIN
  CALL add_partition_for_fact_table('dwh_fact_events');
  CALL add_partition_for_fact_table('dwh_fact_fms_session_events');
  CALL add_partition_for_fact_table('dwh_fact_fms_sessions');
  CALL add_partition_for_fact_table('dwh_fact_bandwidth_usage');
	CALL add_partition_for_table('dwh_aggr_events_entry');
	CALL add_partition_for_table('dwh_aggr_events_domain');
	CALL add_partition_for_table('dwh_aggr_events_country');
	CALL add_partition_for_table('dwh_aggr_events_widget');
	CALL add_partition_for_table('dwh_aggr_events_uid');		
	CALL add_partition_for_table('dwh_aggr_partner');		
	CALL add_partition_for_table('dwh_hourly_events_entry');
	CALL add_partition_for_table('dwh_hourly_events_domain');
	CALL add_partition_for_table('dwh_hourly_events_country');
	CALL add_partition_for_table('dwh_hourly_events_widget');
	CALL add_partition_for_table('dwh_hourly_events_uid');		
	CALL add_partition_for_table('dwh_hourly_partner');		
	CALL add_partition_for_table('dwh_aggr_partner_daily_usage');
END$$

DELIMITER ;