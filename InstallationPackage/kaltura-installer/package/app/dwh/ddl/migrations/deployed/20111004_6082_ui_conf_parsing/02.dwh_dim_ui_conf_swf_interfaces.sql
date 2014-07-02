DROP TABLE IF EXISTS kalturadw.dwh_dim_ui_conf_swf_interfaces;
CREATE TABLE kalturadw.dwh_dim_ui_conf_swf_interfaces (
	id INT NOT NULL AUTO_INCREMENT,
	swf_file varchar(255) NOT NULL,
	tags_search_string varchar(255) NOT NULL DEFAULT '',
	display_name varchar(255),
	PRIMARY KEY (id),
	UNIQUE KEY (swf_file,tags_search_string)) ENGINE=MYISAM CHARSET=latin1;
