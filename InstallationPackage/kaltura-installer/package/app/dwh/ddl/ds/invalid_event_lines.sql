CREATE TABLE kalturadw_ds.`invalid_event_lines` (    
  		  `line_id` INT NOT NULL AUTO_INCREMENT, 
		  `line_number` INT DEFAULT NULL,
          `file_id` INT NOT NULL,
		  `error_reason_code` SMALLINT,
		  `error_reason` VARCHAR(255),
          `event_line` VARCHAR(1023),
		  `insert_time` DATETIME DEFAULT NULL,
          `date_id` INT DEFAULT NULL,
	      `partner_id` VARCHAR(20),
          PRIMARY KEY (`line_id`)  ,
		  KEY `date_id_partner_id` (`date_id`,`partner_id`) ,
		  INDEX `file_reason_code` (`file_id` ASC, `error_reason_code` ASC) 
        ) ENGINE=INNODB DEFAULT CHARSET=utf8
