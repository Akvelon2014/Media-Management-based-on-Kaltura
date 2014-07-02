ALTER TABLE kalturadw.dwh_dim_ready_behavior
change ri_ind ri_ind tinyint(4) NOT NULL DEFAULT 1,
change `dwh_creation_date` `dwh_creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
change `dwh_update_date` `dwh_update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00';
