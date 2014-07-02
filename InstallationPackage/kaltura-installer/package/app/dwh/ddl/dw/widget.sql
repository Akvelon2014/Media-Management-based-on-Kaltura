CREATE TABLE `kalturadw`.`dwh_dim_widget` (                                     
    `widget_id` VARCHAR(32) NOT NULL,                                
    `widget_int_id` INT NOT NULL,                 
    `source_widget_id` VARCHAR(32) DEFAULT NULL,              
    `root_widget_id` VARCHAR(32) DEFAULT NULL,                
    `partner_id` INT DEFAULT -1,                        
    `subp_id` INT DEFAULT -1,                           
    `kshow_id` VARCHAR(20) DEFAULT NULL,                      
    `entry_id` VARCHAR(20) DEFAULT NULL,                      
    `ui_conf_id` INT DEFAULT -1,                        
    `custom_data` VARCHAR(1024) DEFAULT NULL,                 
    `security_type` SMALLINT DEFAULT -1,                 
    `security_policy` SMALLINT DEFAULT -1,               
    `created_at` DATETIME DEFAULT NULL,
    `created_date_id` INT DEFAULT '-1',
    created_hour_id TINYINT DEFAULT '-1',
    `updated_at` DATETIME DEFAULT NULL,
    updated_date_id INT DEFAULT '-1',
    updated_hour_id TINYINT DEFAULT '-1',                 
    `partner_data` VARCHAR(4096) DEFAULT NULL, 
    dwh_creation_date TIMESTAMP NOT NULL DEFAULT 0,
    dwh_update_date TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
    ri_ind TINYINT NOT NULL DEFAULT '0',                                       
       
    PRIMARY KEY (`widget_id`),                                       
    KEY `widget_int_id_index` (`widget_int_id`),                            
    KEY `widget_FI_1` (`kshow_id`),                           
    KEY `widget_FI_2` (`entry_id`),                           
    KEY `widget_FI_3` (`ui_conf_id`),                         
    KEY `partner_id_index` (`partner_id`),                    
    KEY `created_at_index` (`created_at`)  ,                   
    KEY `source_widget_id` (`source_widget_id`),                     
    KEY `root_widget_id` (`root_widget_id`),                     
    KEY `widget_id_updated_at` (widget_id,`updated_at`),
    KEY `dwh_update_date` (`dwh_update_date`)                                              
) ENGINE=MYISAM DEFAULT CHARSET=utf8  ;
                
CREATE TRIGGER `kalturadw`.`dwh_dim_widget_setcreationtime_oninsert` BEFORE INSERT
    ON `kalturadw`.`dwh_dim_widget`
    FOR EACH ROW 
	SET new.dwh_creation_date = NOW();  
