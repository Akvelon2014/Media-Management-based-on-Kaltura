DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `populate_new_aggrs`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `populate_new_aggrs`()
BEGIN
	DECLARE v_start_date_id INT;
	DECLARE v_end_date_id INT;
	DECLARE done INT DEFAULT 0;	
	DECLARE populate_new_aggr_cursor CURSOR FOR SELECT day_id start_date_id, LAST_DAY(DATE(day_id))*1 end_date_id FROM kalturadw.dwh_dim_time WHERE day_of_month = 1;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	OPEN populate_new_aggr_cursor;
	
	read_loop: LOOP
		FETCH populate_new_aggr_cursor INTO v_start_date_id, v_end_date_id;
		IF done THEN
			LEAVE read_loop;
		END IF;
		
		INSERT INTO kalturadw.dwh_hourly_events_country_innodb
		select * from kalturadw.dwh_hourly_events_country
		WHERE date_id between v_start_date_id AND v_end_date_id;
		
		INSERT INTO kalturadw.dwh_hourly_events_domain_innodb
                select * from kalturadw.dwh_hourly_events_domain
                WHERE date_id between v_start_date_id AND v_end_date_id;

		INSERT INTO kalturadw.dwh_hourly_events_domain_referrer_innodb
                select * from kalturadw.dwh_hourly_events_domain_referrer
                WHERE date_id between v_start_date_id AND v_end_date_id;

		INSERT INTO kalturadw.dwh_hourly_events_entry_innodb
                select * from kalturadw.dwh_hourly_events_entry
                WHERE date_id between v_start_date_id AND v_end_date_id;

		INSERT INTO kalturadw.dwh_hourly_events_uid_innodb
                select * from kalturadw.dwh_hourly_events_uid
                WHERE date_id between v_start_date_id AND v_end_date_id;

		INSERT INTO kalturadw.dwh_hourly_events_widget_innodb
                select * from kalturadw.dwh_hourly_events_widget
                WHERE date_id between v_start_date_id AND v_end_date_id;

		INSERT INTO kalturadw.dwh_hourly_partner_innodb
                select * from kalturadw.dwh_hourly_partner
                WHERE date_id between v_start_date_id AND v_end_date_id;
	END LOOP;
	CLOSE populate_new_aggr_cursor;
	
	ALTER TABLE kalturadw.dwh_hourly_events_country_innodb
	  	ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`country_id`,`location_id`);

	ALTER TABLE kalturadw.dwh_hourly_events_domain_innodb
		ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`domain_id`);

	ALTER TABLE kalturadw.dwh_hourly_events_domain_referrer_innodb
	  	ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`domain_id`,`referrer_id`);

	ALTER TABLE kalturadw.dwh_hourly_events_entry_innodb
	  	ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`entry_id`),
		ADD KEY `entry_id` (`entry_id`);

	ALTER TABLE kalturadw.dwh_hourly_events_uid_innodb
		ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`kuser_id`);

	ALTER TABLE kalturadw.dwh_hourly_events_widget_innodb
		ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`widget_id`);

	ALTER TABLE kalturadw.dwh_hourly_partner_innodb
  		ADD PRIMARY KEY (`partner_id`,`date_id`,`hour_id`);

	RENAME TABLE kalturadw.dwh_hourly_events_country TO kalturadw.dwh_hourly_events_country_myisam;
	RENAME TABLE kalturadw.dwh_hourly_events_country_innodb TO kalturadw.dwh_hourly_events_country;
	RENAME TABLE kalturadw.dwh_hourly_events_domain TO kalturadw.dwh_hourly_events_domain_myisam;
	RENAME TABLE kalturadw.dwh_hourly_events_domain_innodb TO kalturadw.dwh_hourly_events_domain;
	RENAME TABLE kalturadw.dwh_hourly_events_domain_referrer TO kalturadw.dwh_hourly_events_domain_referrer_myisam;
	RENAME TABLE kalturadw.dwh_hourly_events_domain_referrer_innodb TO kalturadw.dwh_hourly_events_domain_referrer;
	RENAME TABLE kalturadw.dwh_hourly_events_entry TO kalturadw.dwh_hourly_events_entry_myisam;
	RENAME TABLE kalturadw.dwh_hourly_events_entry_innodb TO kalturadw.dwh_hourly_events_entry;
	RENAME TABLE kalturadw.dwh_hourly_events_uid TO kalturadw.dwh_hourly_events_uid_myisam;
	RENAME TABLE kalturadw.dwh_hourly_events_uid_innodb TO kalturadw.dwh_hourly_events_uid;
	RENAME TABLE kalturadw.dwh_hourly_events_widget TO kalturadw.dwh_hourly_events_widget_myisam;
	RENAME TABLE kalturadw.dwh_hourly_events_widget_innodb TO kalturadw.dwh_hourly_events_widget;
	RENAME TABLE kalturadw.dwh_hourly_partner TO kalturadw.dwh_hourly_partner_myisam;
	RENAME TABLE kalturadw.dwh_hourly_partner_innodb TO kalturadw.dwh_hourly_partner;
    END$$

DELIMITER ;

CALL kalturadw.populate_new_aggrs();
DROP PROCEDURE IF EXISTS kalturadw.populate_new_aggrs;
