ALTER TABLE kalturadw.dwh_dim_fms_app CHANGE app_id  fms_app_id INT(11) unsigned, CHANGE app fms_app_name varchar(45);
  
ALTER TABLE kalturadw.dwh_dim_fms_app
  add `dwh_creation_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  add `dwh_update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  add `ri_ind` TINYINT(4) NOT NULL DEFAULT '1',
  drop primary key;

ALTER TABLE kalturadw.dwh_dim_fms_app CHANGE fms_app_id fms_app_id INT(11) NOT NULL AUTO_INCREMENT KEY;
