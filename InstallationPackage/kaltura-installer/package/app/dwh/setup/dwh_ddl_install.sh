#!/bin/bash
USER="etl"
PW="etl"
KITCHEN=/usr/local/pentaho/pdi
ROOT_DIR=/opt/kaltura/dwh
HOST=localhost
PORT=3306

while getopts "u:p:k:d:h:P:" o
do	case "$o" in
	u)	USER="$OPTARG";;
	p)	PW="$OPTARG";;
    k)	KITCHEN="$OPTARG";;
    d)	ROOT_DIR="$OPTARG";;
	h)	HOST="$OPTARG";;
	P)	PORT="$OPTARG";;
	[?])	echo >&2 "Usage: $0 [-u username] [-p password] [-k  pdi-path] [-d dwh-path] [-h host-name] [-P port]"
		exit 1;;
	esac
done

function mysqlexec {
        echo "now executing $1"
        mysql -u$USER -p$PW -h$HOST -P$PORT < $1

		ret_val=$?
        if [ $ret_val -ne 0 ];then
			echo $ret_val
			echo "Error - bailing out!"
			exit
        fi
}

ETL_ROOT_DIR=$ROOT_DIR/etlsource/
SETUP_ROOT_DIR=$ROOT_DIR/setup
DDL_ROOT_DIR=$ROOT_DIR/ddl/
BISOURCE_ROOT_DIR=$DDL_ROOT_DIR/bi_sources/
DS_ROOT_DIR=$DDL_ROOT_DIR/ds/
DW_ROOT_DIR=$DDL_ROOT_DIR/dw/
DDL_SETUP_ROOT_DIR=$DDL_ROOT_DIR/setup/

#general
mysqlexec $DDL_ROOT_DIR/db_create.sql

#bisource
mysqlexec $BISOURCE_ROOT_DIR/bisources_ENTRY_MEDIA_SOURCE.sql 
mysqlexec $BISOURCE_ROOT_DIR/bisources_ENTRY_MEDIA_TYPE.sql 
mysqlexec $BISOURCE_ROOT_DIR/bisources_ENTRY_STATUS.sql 
mysqlexec $BISOURCE_ROOT_DIR/bisources_ENTRY_TYPE.sql 
mysqlexec $BISOURCE_ROOT_DIR/bisources_FLAVOR_ASSET_STATUS.sql 
mysqlexec $BISOURCE_ROOT_DIR/bisources_MODERATION_STATUS.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_PARTNER_GROUP_TYPE.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_UI_CONF_STATUS.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_UI_CONF_TYPE.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_WIDGET_SECURITY_POLICY.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_WIDGET_SECURITY_TYPE.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_control.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_event_type.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_gender.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_partner_status.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_partner_type.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_user_status.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_asset_status.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_file_sync_object_type.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_file_sync_status.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_ready_behavior.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_creation_mode.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_batch_job_error_type.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_batch_job_status.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_batch_job_type.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_fms_app.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_partner_vertical.sql
mysqlexec $BISOURCE_ROOT_DIR/bisources_partner_class_of_service.sql

#ds/
mysqlexec $DS_ROOT_DIR/files.sql
mysqlexec $DS_ROOT_DIR/events.sql
mysqlexec $DS_ROOT_DIR/invalid_event_lines.sql
mysqlexec $DS_ROOT_DIR/ds_fms_sessions_events.sql
mysqlexec $DS_ROOT_DIR/invalid_fms_event_lines.sql
mysqlexec $DS_ROOT_DIR/ds_events_partitions_view.sql
mysqlexec $DS_ROOT_DIR/empty_cycle_partition_procedure.sql
mysqlexec $DS_ROOT_DIR/add_cycle_partition_procedure.sql
mysqlexec $DS_ROOT_DIR/drop_cycle_partition_procedure.sql
mysqlexec $DS_ROOT_DIR/transfer_cycle_partition_procedure.sql
mysqlexec $DS_ROOT_DIR/register_file_procedure.sql
mysqlexec $DS_ROOT_DIR/get_ip_country_location_function.sql
mysqlexec $DS_ROOT_DIR/restore_file_status_procedure.sql
mysqlexec $DS_ROOT_DIR/set_file_status_full_procedure.sql
mysqlexec $DS_ROOT_DIR/set_file_status_procedure.sql
mysqlexec $DS_ROOT_DIR/aggr_name_resolver.sql
mysqlexec $DS_ROOT_DIR/parameters.sql
mysqlexec $DS_ROOT_DIR/processes.sql
mysqlexec $DS_ROOT_DIR/staging_areas.sql
mysqlexec $DS_ROOT_DIR/populate_repository_for_events.sql
mysqlexec $DS_ROOT_DIR/populate_repository_for_fms_streaming.sql
mysqlexec $DS_ROOT_DIR/populate_repository_for_bandwidth_usage.sql
mysqlexec $DS_ROOT_DIR/populate_repository_for_api_calls.sql
mysqlexec $DS_ROOT_DIR/populate_repository_for_transcoding.sql
mysqlexec $DS_ROOT_DIR/fms_incomplete_session.sql
mysqlexec $DS_ROOT_DIR/fms_stale_sessions.sql
mysqlexec $DS_ROOT_DIR/fms_sessionize.sql
mysqlexec $DS_ROOT_DIR/fms_sessionize_by_date_id.sql
mysqlexec $DS_ROOT_DIR/cycles.sql
mysqlexec $DS_ROOT_DIR/get_error_code.sql
mysqlexec $DS_ROOT_DIR/insert_invalid_ds_line.sql
mysqlexec $DS_ROOT_DIR/invalid_ds_lines.sql
mysqlexec $DS_ROOT_DIR/invalid_ds_lines_error_codes.sql
mysqlexec $DS_ROOT_DIR/set_cycle_status.sql
mysqlexec $DS_ROOT_DIR/ds_bandwidth_usage.sql
mysqlexec $DS_ROOT_DIR/locks.sql
mysqlexec $DS_ROOT_DIR/populate_locks.sql
mysqlexec $DS_ROOT_DIR/pentaho_sequences.sql
mysqlexec $DS_ROOT_DIR/version_table.sql
mysqlexec $DS_ROOT_DIR/etl_servers.sql
mysqlexec $DS_ROOT_DIR/retention_policy.sql
mysqlexec $DS_ROOT_DIR/ds_api_calls.sql
mysqlexec $DS_ROOT_DIR/ds_incomplete_api_calls.sql
mysqlexec $DS_ROOT_DIR/unify_incomplete_api_calls.sql
mysqlexec $DS_ROOT_DIR/ds_errors.sql
mysqlexec $DS_ROOT_DIR/operational_syncs.sql

#etl_log
mysqlexec $DDL_ROOT_DIR/log/etl_log.sql

#dw
mysqlexec $DW_ROOT_DIR/batch_jobs.sql
mysqlexec $DW_ROOT_DIR/bi_sources.sql
mysqlexec $DW_ROOT_DIR/dw_control.sql
mysqlexec $DW_ROOT_DIR/dw_EDITOR_TYPE.sql
mysqlexec $DW_ROOT_DIR/dw_ENTRY_MEDIA_SOURCE.sql
mysqlexec $DW_ROOT_DIR/dw_ENTRY_MEDIA_TYPE.sql
mysqlexec $DW_ROOT_DIR/dw_ENTRY_STATUS.sql
mysqlexec $DW_ROOT_DIR/dw_ENTRY_TYPE.sql
mysqlexec $DW_ROOT_DIR/dw_event_type.sql
mysqlexec $DW_ROOT_DIR/dw_gender.sql
mysqlexec $DW_ROOT_DIR/dw_MODERATION_STATUS.sql
mysqlexec $DW_ROOT_DIR/dw_partner_group_type.sql
mysqlexec $DW_ROOT_DIR/dw_partner_status.sql
mysqlexec $DW_ROOT_DIR/dw_partner_type.sql
mysqlexec $DW_ROOT_DIR/dw_UI_CONF_STATUS.sql
mysqlexec $DW_ROOT_DIR/dw_UI_CONF_TYPE.sql
mysqlexec $DW_ROOT_DIR/dw_user_status.sql
mysqlexec $DW_ROOT_DIR/dw_WIDGET_SECURITY_POLICY.sql
mysqlexec $DW_ROOT_DIR/dw_widget_security_type.sql
mysqlexec $DW_ROOT_DIR/ip_ranges.sql
mysqlexec $DW_ROOT_DIR/locations.sql
mysqlexec $DW_ROOT_DIR/locations_init.sql
mysqlexec $DW_ROOT_DIR/time.sql
mysqlexec $DW_ROOT_DIR/widget.sql
mysqlexec $DW_ROOT_DIR/countries_states_view.sql
mysqlexec $DW_ROOT_DIR/countries_view.sql
mysqlexec $DW_ROOT_DIR/generate_daily_usage_report.sql
mysqlexec $DW_ROOT_DIR/dwh_daily_usage_reports.sql

#dw/facts
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_events.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_events_archive.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_bandwidth_usage.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_bandwidth_usage_archive.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_fms_sessions.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_fms_sessions_archive.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_fms_session_events.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_fms_session_events_archive.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_api_calls.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_api_calls_archive.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_errors.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_errors_archive.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_incomplete_api_calls.sql
mysqlexec $DW_ROOT_DIR/facts/dwh_fact_entries_sizes.sql

#dw/dimensions
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_kuser.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_entries.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_partners.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_partners_billing.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_partner_vertical.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_partner_class_of_service.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_domain.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_asset_status.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_audio_codec.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_container_format.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_file_ext.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_file_sync.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_file_sync_object_type.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_file_sync_status.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_flavor_asset.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_flavor_format.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_flavor_params.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_ready_behavior.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_video_codec.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_conversion_profile.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_creation_mode.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_flavor_params_conversion_profile.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_flavor_params_output.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_media_info.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_user_agent.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_bandwidth_source.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_domain_referrer.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_referrer.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_batch_job.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_batch_job_error_type.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_batch_job_status.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_batch_job_sub_type.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_batch_job_type.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_browser.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_os.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_categories.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_entry_categories.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_entry_type_display.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_tags.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_flavor_asset_tags.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_ui_conf.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_ui_conf_swf_interfaces.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_api_actions.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_error_codes.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_client_tags.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_hosts.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_error_object_types.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_hours.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_applications.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_permissions.sql
mysqlexec $DW_ROOT_DIR/dimensions/dwh_dim_pusers.sql


#dw/maintenance
mysqlexec $DW_ROOT_DIR/maintenance/add_partition_procedure.sql
mysqlexec $DW_ROOT_DIR/maintenance/move_innodb_to_archive.sql
mysqlexec $DW_ROOT_DIR/maintenance/table_data_migration_procedures.sql
mysqlexec $DW_ROOT_DIR/maintenance/apply_partitions_to_target_table.sql

#dw/aggr
mysqlexec $DW_ROOT_DIR/aggr/aggr_managment.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_country.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_domain.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_domain_referrer.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_entry.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_widget.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_uid.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_partner.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_partner_usage.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_user_usage.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_devices.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_api_calls.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_errors.sh
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_context_entry_user_app.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_hourly_events_context_app.sql
mysqlexec $DW_ROOT_DIR/aggr/time_zone_helper_function.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_aggr_day_procedure.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_aggr_day_bandwidth.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_aggr_day_partner_storage.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_aggr_day_user_usage.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_aggr_day_api_calls.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_aggr_day_errors.sql
mysqlexec $DW_ROOT_DIR/aggr/calc_entries_sizes.sql
mysqlexec $DW_ROOT_DIR/aggr/post_aggregation_widget.sql
mysqlexec $DW_ROOT_DIR/aggr/post_aggregation_partner.sql
mysqlexec $DW_ROOT_DIR/aggr/post_aggregation_entry.sql
mysqlexec $DW_ROOT_DIR/aggr/pre_aggregation_entry.sql
mysqlexec $DW_ROOT_DIR/aggr/add_plays_views.sql
mysqlexec $DW_ROOT_DIR/aggr/remove_plays_views.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_entry_plays_views.sql
mysqlexec $DW_ROOT_DIR/aggr/resolve_aggr_name_function.sql
mysqlexec $DW_ROOT_DIR/aggr/dwh_aggr_events_partitions_view.sql
mysqlexec $DW_ROOT_DIR/aggr/old_entries_table.sql

#dw/functions/
mysqlexec $DW_ROOT_DIR/functions/calc_month_id_function.sql
mysqlexec $DW_ROOT_DIR/functions/calc_time_shift.sql
mysqlexec $DW_ROOT_DIR/functions/calc_partner_monthly_storage.sql
mysqlexec $DW_ROOT_DIR/functions/calc_partner_storage_data_time_range.sql
mysqlexec $DW_ROOT_DIR/functions/get_overage_charge.sql
mysqlexec $DW_ROOT_DIR/functions/calc_partner_overage.sql
mysqlexec $DW_ROOT_DIR/functions/calc_partner_billing_data_procedure.sql
mysqlexec $DW_ROOT_DIR/functions/get_data_for_operational.sql
mysqlexec $DW_ROOT_DIR/functions/mark_operational_as_done.sql
mysqlexec $DW_ROOT_DIR/functions/calc_partner_usage_data.sql
#dw/ri/ 
mysqlexec $DW_ROOT_DIR/ri/ri_defaults.sql
mysqlexec $DW_ROOT_DIR/ri/ri_mapping.sql
mysqlexec $DW_ROOT_DIR/ri/ri_defaults_grouped_view.sql
mysqlexec $DW_ROOT_DIR/ri/ri_mapping_and_defaults_view.sql

#dw/views/
mysqlexec $DW_ROOT_DIR/views/dwh_dim_entries_v.sql
mysqlexec $DW_ROOT_DIR/views/dwh_dim_partners_v.sql
mysqlexec $DW_ROOT_DIR/views/dwh_dim_uiconf_v.sql
mysqlexec $DW_ROOT_DIR/views/dwh_view_entry_type.sql
mysqlexec $DW_ROOT_DIR/views/dwh_view_partners_monthly_billing_last_updated_at.sql
mysqlexec $DW_ROOT_DIR/views/dwh_view_partners_monthly_billing.sql
mysqlexec $DW_ROOT_DIR/views/dwh_view_monthly_active_partners.sql
mysqlexec $DW_ROOT_DIR/views/dwh_dim_user_reports_allowed_partners.sql

#dw/fms/
mysqlexec $DW_ROOT_DIR/fms/fms_dim_tables.sql
 
mysqlexec $DW_ROOT_DIR/maintenance/populate_table_partitions.sql
mysqlexec $DDL_SETUP_ROOT_DIR/populate_time_dim.sql
mysqlexec $DDL_SETUP_ROOT_DIR/populate_dwh_dim_ip_ranges.sql
