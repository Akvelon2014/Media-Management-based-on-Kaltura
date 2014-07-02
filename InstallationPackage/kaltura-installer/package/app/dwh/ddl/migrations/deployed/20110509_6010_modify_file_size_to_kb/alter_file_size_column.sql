UPDATE kalturadw_ds.files set file_size = file_size/1024;
ALTER TABLE kalturadw_ds.files CHANGE file_size file_size_kb INT (11);
