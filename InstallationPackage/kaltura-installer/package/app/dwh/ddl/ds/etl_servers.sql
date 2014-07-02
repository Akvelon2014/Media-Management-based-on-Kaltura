USE kalturadw_ds;
CREATE TABLE `etl_servers` (
  `etl_server_id` INT(11) NOT NULL AUTO_INCREMENT,
  `etl_server_name` VARCHAR(64) NOT NULL,
  `lb_constant` FLOAT NOT NULL DEFAULT 1,
  PRIMARY KEY (`etl_server_id`),
  UNIQUE KEY (`etl_server_name`)
) ENGINE=MYISAM DEFAULT CHARSET=UTF8
