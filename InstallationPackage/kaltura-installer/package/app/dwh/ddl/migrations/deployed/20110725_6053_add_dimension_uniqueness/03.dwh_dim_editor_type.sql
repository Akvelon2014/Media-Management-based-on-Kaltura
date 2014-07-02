ALTER TABLE kalturadw.dwh_dim_editor_type DROP KEY editor_type_name_index;
ALTER TABLE kalturadw.dwh_dim_editor_type 
	ADD UNIQUE KEY (editor_type_name), 
	CHANGE editor_type_id editor_type_id smallint(6) auto_increment not null;

DROP TABLE kalturadw_bisources.bisources_editor_type;
DELETE FROM kalturadw.bisources_tables WHERE table_name = 'editor_type';
