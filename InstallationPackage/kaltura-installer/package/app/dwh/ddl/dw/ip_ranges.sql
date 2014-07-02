CREATE TABLE kalturadw.`dwh_dim_ip_ranges_raw` (                                         
 `IP_FROM`  INT UNSIGNED DEFAULT NULL,                                       
 `IP_TO`  INT UNSIGNED DEFAULT NULL,                                         
 `COUNTRY_CODE` VARCHAR(2) DEFAULT NULL,                                 
 `COUNTRY_NAME` VARCHAR(64) DEFAULT NULL,                                 
 `REGION` VARCHAR(128) DEFAULT NULL,
 `CITY` VARCHAR(128) DEFAULT NULL,
 `ISP_NAME` VARCHAR(255) DEFAULT NULL,
 `DOMAIN_NAME` VARCHAR(128) DEFAULT NULL
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
                   
CREATE TABLE kalturadw.`ip_ranges_last_update` (  
                         `last_update` DATETIME DEFAULT NULL   
                       ) ENGINE=MYISAM DEFAULT CHARSET=latin1;
INSERT INTO kalturadw.`ip_ranges_last_update` VALUES('1999-01-01');

CREATE TABLE kalturadw.`dwh_dim_ip_ranges` (                                         
     `IP_FROM`  INT UNSIGNED DEFAULT NULL,                                       
     `IP_TO`  INT UNSIGNED DEFAULT NULL,                                         
     `COUNTRY_CODE` VARCHAR(2) DEFAULT NULL,                                 
     `COUNTRY_NAME` VARCHAR(64) DEFAULT NULL,                                 
     `REGION` VARCHAR(128) DEFAULT NULL,
     `CITY` VARCHAR(128) DEFAULT NULL,
     `ISP_NAME` VARCHAR(255) DEFAULT NULL,
     `DOMAIN_NAME` VARCHAR(128) DEFAULT NULL,
     `country_id` INT DEFAULT NULL,  
     `location_id` int(11) DEFAULT NULL,
     UNIQUE KEY `ip_ranges_from_country` (`IP_FROM`,`country_id`,`location_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;


