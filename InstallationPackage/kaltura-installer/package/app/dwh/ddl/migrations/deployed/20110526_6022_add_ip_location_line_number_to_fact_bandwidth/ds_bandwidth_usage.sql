ALTER TABLE kalturadw_ds.ds_bandwidth_usage  
	ADD `line_number` INT (10) DEFAULT NULL FIRST,
	ADD `user_ip` VARCHAR(15) DEFAULT NULL,
  	ADD `user_ip_number` INT(10) UNSIGNED DEFAULT NULL,
  	ADD `country_id` INT(11) DEFAULT NULL,
  	ADD `location_id` INT(11) DEFAULT NULL;
