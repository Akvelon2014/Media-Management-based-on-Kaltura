DROP TABLE IF EXISTS `kalturadw`.dwh_dim_partners_billing;

CREATE TABLE `kalturadw`.`dwh_dim_partners_billing` (
    `partner_id` INT NOT NULL ,
    `partner_group_type_id` SMALLINT default 1,
    updated_at timestamp not null default 0,
	max_monthly_bandwidth_kb DECIMAL(15,3),
	charge_monthly_bandwidth_kb_usd DECIMAL(15,3),
	charge_monthly_bandwidth_kb_unit DECIMAL(15,3),
	max_monthly_storage_mb DECIMAL(15,3),
	charge_monthly_storage_mb_usd DECIMAL(15,3),
	charge_monthly_storage_mb_unit DECIMAL(15,3),
	max_monthly_total_usage_mb DECIMAL(15,3),
	charge_monthly_total_usage_mb_usd DECIMAL(15,3),
	charge_monthly_total_usage_mb_unit DECIMAL(15,3),
	max_monthly_entries	 BIGINT(20),
	charge_monthly_entries_usd	 DECIMAL(15,3),
	charge_monthly_entries_unit	 INT(11),
	max_monthly_plays BIGINT(20),
	charge_monthly_plays_usd DECIMAL(15,3),
	charge_monthly_plays_unit INT(11),
	max_kusers BIGINT(20),
	charge_kusers_usd DECIMAL(15,3),
	charge_kusers_unit INT(11),
	max_publishers BIGINT(20),
	charge_publishers_usd DECIMAL(15,3),
	charge_publishers_unit INT(11),
	max_end_users BIGINT(20),
	charge_end_users_usd DECIMAL(15,3),
    charge_end_users_unit INT(11),
	class_of_service_id INT(11),
	vertical_id INT(11),
    is_active TINYINT default 1,
    dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
    dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
  
    PRIMARY KEY (`partner_id`, updated_at)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TRIGGER `kalturadw`.`dwh_dim_partners_billing_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_partners_billing`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();
