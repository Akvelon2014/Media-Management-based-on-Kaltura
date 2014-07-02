UPDATE kalturadw_bisources.bisources_partner_group_type
SET partner_group_type_name = CASE partner_group_type_id WHEN 1 THEN 'Publisher' WHEN 2 THEN 'VAR' WHEN 3 THEN 'Group' END
