ALTER TABLE kalturadw.dwh_dim_partners 	CHANGE max_monthly_bandwidth_kb max_monthly_bandwidth_kb DECIMAL(15,3),
					CHANGE charge_monthly_bandwidth_kb_unit charge_monthly_bandwidth_kb_unit DECIMAL(15,3),
					CHANGE max_monthly_storage_mb max_monthly_storage_mb DECIMAL(15,3),
					CHANGE charge_monthly_storage_mb_unit charge_monthly_storage_mb_unit DECIMAL(15,3),
					CHANGE max_monthly_total_usage_mb max_monthly_total_usage_mb DECIMAL(15,3),
					CHANGE charge_monthly_total_usage_mb_unit charge_monthly_total_usage_mb_unit DECIMAL(15,3);
