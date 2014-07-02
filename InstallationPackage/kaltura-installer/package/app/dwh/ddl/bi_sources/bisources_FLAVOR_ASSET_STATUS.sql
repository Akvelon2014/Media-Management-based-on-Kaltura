DROP TABLE IF EXISTS `kalturadw_bisources`.`bisources_flavor_asset_status`;

CREATE TABLE `kalturadw_bisources`.`bisources_flavor_asset_status` (
  `flavor_asset_status_id` SMALLINT NOT NULL ,
  `flavor_asset_status_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`flavor_asset_status_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

	

INSERT INTO kalturadw_bisources.bisources_flavor_asset_status (flavor_asset_status_id,flavor_asset_status_name) VALUES(-1,'STATUS_ERROR'); 
INSERT INTO kalturadw_bisources.bisources_flavor_asset_status (flavor_asset_status_id,flavor_asset_status_name) VALUES(0,'STATUS_QUEUED'); 
INSERT INTO kalturadw_bisources.bisources_flavor_asset_status (flavor_asset_status_id,flavor_asset_status_name) VALUES(1,'STATUS_CONVERTING'); 
INSERT INTO kalturadw_bisources.bisources_flavor_asset_status (flavor_asset_status_id,flavor_asset_status_name) VALUES(2,'STATUS_READY'); 
INSERT INTO kalturadw_bisources.bisources_flavor_asset_status (flavor_asset_status_id,flavor_asset_status_name) VALUES(3,'STATUS_DELETED'); 
INSERT INTO kalturadw_bisources.bisources_flavor_asset_status (flavor_asset_status_id,flavor_asset_status_name) VALUES(4,'STATUS_NOT_APPLICABLE'); 
