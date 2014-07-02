USE kalturadw_ds;

DROP TABLE IF EXISTS pentaho_sequences;

CREATE TABLE pentaho_sequences (
	seq_id INT(11),
	job_name VARCHAR(250) NOT NULL,
	job_number INT(11),
	is_active BOOLEAN, 
	UNIQUE(seq_id, job_number));

INSERT INTO pentaho_sequences VALUES(1,'dimensions/refresh_bisources_tables.ktr',1,TRUE);	
INSERT INTO pentaho_sequences VALUES(2,'dimensions/update_partners.ktr',1,TRUE);
INSERT INTO pentaho_sequences VALUES(3,'dimensions/update_entries.ktr',1,TRUE),(3,'dimensions/update_flavor_asset.ktr',2,TRUE),(3,'dimensions/update_file_sync.ktr',3,TRUE),(3,'dimensions/update_media_info.ktr',4,TRUE),(3,'dimensions/update_flavor_params.ktr',5,TRUE),(3,'dimensions/update_flavor_params_output.ktr',6,TRUE);
INSERT INTO pentaho_sequences VALUES(4,'dimensions/update_locations_for_kusers.ktr',1,TRUE),(4,'dimensions/update_kusers.ktr',2,TRUE);
INSERT INTO pentaho_sequences VALUES(5,'dimensions/update_ui_conf.ktr',1,TRUE);	
INSERT INTO pentaho_sequences VALUES(6,'dimensions/update_widget.ktr',1,TRUE);
INSERT INTO pentaho_sequences VALUES(7,'dimensions/update_convertsion_profile.ktr',1,TRUE);
INSERT INTO pentaho_sequences VALUES(8,'dimensions/update_flavor_params_conversion_profile.ktr',1,TRUE);
