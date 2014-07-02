ALTER TABLE kalturadw_ds.files 
	DROP INDEX file_name;
ALTER TABLE kalturadw_ds.files 
	ADD UNIQUE KEY file_name_process_id (file_name, process_id);