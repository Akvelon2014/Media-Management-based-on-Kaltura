DROP TABLE IF EXISTS `kalturadw`.dwh_dim_ui_conf;

CREATE TABLE kalturadw.`dwh_dim_ui_conf` (                                      
           `ui_conf_id` INT NOT NULL ,                       
           `ui_conf_type_id` SMALLINT DEFAULT -1,                        
           `partner_id` INT DEFAULT -1,                          
           `subp_id` INT DEFAULT -1,                              
           `conf_file_path` VARCHAR(128) DEFAULT NULL,                 
           `ui_conf_name` VARCHAR(128) DEFAULT NULL,                           
           `width` VARCHAR(10) DEFAULT NULL,                           
           `height` VARCHAR(10) DEFAULT NULL,                          
           `html_params` VARCHAR(256) DEFAULT NULL,                    
           `swf_url` VARCHAR(256) DEFAULT NULL,                        
	   `created_at` DATETIME DEFAULT NULL,
	   `created_date_id` INT DEFAULT '-1',
	    created_hour_id TINYINT DEFAULT '-1',
	   `updated_at` DATETIME DEFAULT NULL,
            updated_date_id INT DEFAULT '-1',
	    updated_hour_id TINYINT DEFAULT '-1',                 
           `conf_vars` VARCHAR(4096) DEFAULT NULL,                     
           `use_cdn` TINYINT  DEFAULT NULL,                           
           `tags` TEXT,                                                
           `custom_data` TEXT,  
            UI_Conf_Status_ID	INT DEFAULT -1,
	    description VARCHAR(4096)  DEFAULT NULL,
	    display_in_search TINYINT  DEFAULT NULL,
            dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
	    dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
 	    ri_ind TINYINT NOT NULL DEFAULT '0',                                       
	    version varchar(60), 
	    swf_interface_id INT(11),
           PRIMARY KEY (`ui_conf_id`),
           KEY ui_conf_type_id (ui_conf_type_id),                                          
           KEY ui_conf_status_id (ui_conf_status_id),                                          
           KEY partner_id (partner_id)  ,                                        
  	   KEY `ui_conf_id_updated_at` (ui_conf_id,`updated_at`),
	  KEY `dwh_update_date` (`dwh_update_date`)                                               
         ) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
         
CREATE TRIGGER `kalturadw`.`dwh_dim_ui_conf_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_ui_conf`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();  
