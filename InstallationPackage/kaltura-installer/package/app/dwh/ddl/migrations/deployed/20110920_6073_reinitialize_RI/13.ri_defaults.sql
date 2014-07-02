USE kalturadw;

UPDATE ri_defaults 
SET 	default_value = CONCAT('"',default_value,'"'),
	table_name = lower(table_name),
	default_field = lower(default_field);

DELETE FROM ri_defaults
WHERE table_name IN ('dwh_dim_control','Dwh_Dim_Entry_Type', 'Dwh_Dim_Entry_Media_Type', 'Dwh_Dim_Entry_Media_Source', 'Dwh_Dim_Entry_Status',
'Dwh_Dim_UI_conf_type', 'Dwh_Dim_UI_conf_status', 'Dwh_Dim_partner_status', 'Dwh_Dim_partner_type', 'Dwh_Dim_user_status', 'Dwh_Dim_moderation_status',
'Dwh_Dim_widget_security_type', 'Dwh_Dim_partner_group_type', 'dwh_dim_ready_behavior', 'dwh_dim_file_sync_object_type' , 'dwh_dim_file_sync_status',
'dwh_dim_creation_mode');

UPDATE ri_defaults
SET default_value = 'CONCAT(a.domain_id, "-Missing Value")'
WHERE table_name = 'dwh_dim_domains' AND default_field = 'domain_name';

INSERT INTO ri_defaults (table_name, default_field, default_value)  
VALUES ('dwh_dim_referrer', 'referrer', 'CONCAT(a.referrer_id, "-Missing Value")'),
 	('dwh_dim_locations', 'location_type_name', '"Missing Value"'),
		('dwh_dim_locations', 'location_name', 'CONCAT(a.location_id, " - Missing Value")'),
		('dwh_dim_locations', 'country', '"Missing Value"'),
		('dwh_dim_locations', 'country_id', '"-1"'),
		('dwh_dim_locations', 'country_name', '"Missing Value"'),
		('dwh_dim_locations', 'region', '"Missing Value"'),
		('dwh_dim_locations', 'region_id', '"-1"'),
		('dwh_dim_locations', 'state', '"Missing Value"'),
		('dwh_dim_locations', 'state_id', '"-1"'),
		('dwh_dim_locations', 'city', '"Missing Value"'),
		('dwh_dim_locations', 'dwh_creation_date', '"2099-01-01 00:00:00"'),
		('dwh_dim_locations', 'dwh_update_date', '"2099-01-01 00:00:00"');
