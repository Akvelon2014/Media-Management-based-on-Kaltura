# add lines & err_lines to kalturadw_ds.files
ALTER TABLE kalturadw_ds.files ADD 	`lines` INT DEFAULT NULL,ADD  `err_lines` INT DEFAULT NULL;
ALTER TABLE kalturadw_ds.files ADD 	`file_size` INT DEFAULT NULL;


