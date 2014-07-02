ALTER TABLE kalturadw.dwh_dim_file_sync 
DROP INDEX unique_key,
DROP INDEX object_id_object_type_version_subtype_index,
DROP INDEX partner_id_object_id_object_type_index,
ADD UNIQUE KEY `unique_key` (`object_type`,`object_id`,`object_sub_type`,`version`,`dc`);