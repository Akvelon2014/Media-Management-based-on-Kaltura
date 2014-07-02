INSERT INTO kalturadw_bisources.bisources_ui_conf_type (ui_conf_type_id,ui_conf_type_name)
VALUES (8, 'KDP3'), (14, 'Silverlight player'), (15, 'CLIENTSIDE_ENCODER'), (0, 'GENERIC'), 
(7, 'KRecord'), (11, 'KMC_CONTENT'), (12, 'KMC_DASHBOARD'), (9, 'KMC_ACCOUNT'), (10, 'KMC_ANALYTICS'), (17, 'KMC_ROLES_AND_PERMISSIONS'), (16, 'KMC_GENERAL'), (18, 'Clipper');
	
UPDATE kalturadw_bisources.bisources_ui_conf_type 
SET ui_conf_type_name = 'KDP'
WHERE ui_conf_type_id = 1;
