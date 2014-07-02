DROP TABLE IF EXISTS `kalturadw`.`dwh_dim_locations`;

CREATE TABLE `kalturadw`.`dwh_dim_locations` (                               
     `location_id` INT NOT NULL AUTO_INCREMENT,              
      location_type_name VARCHAR(8) DEFAULT NULL,                  
     `location_name` VARCHAR(50) DEFAULT NULL,     
     `country` VARCHAR(50) NOT NULL DEFAULT '',
      country_id INT DEFAULT NULL,
      country_name VARCHAR(64) DEFAULT NULL,
     `region` VARCHAR(50) DEFAULT NULL,                                                          
      region_id INT DEFAULT NULL,
     `state` VARCHAR(50) NOT NULL DEFAULT '',                              
      state_id INT DEFAULT NULL,
     `city` VARCHAR(50) NOT NULL DEFAULT '',                               
      dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
      dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
      ri_ind TINYINT NOT NULL DEFAULT '0',	      
     PRIMARY KEY (`location_id`),                                   
     KEY `idx_dwh_dim_locations_tk` (`location_id`), 
     KEY `idx_dwh_dim_locations_country_id` (country_id),               
     KEY `idx_dwh_dim_locations_lookup` (`country`,`region`,`state`,`city`),
	 UNIQUE KEY (`location_name`, `location_type_name`, `country`, `state`, `city`)
   ) ENGINE=MYISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ; 
   
CREATE TRIGGER `kalturadw`.`dwh_dim_locations_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_locations`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();