create table kalturadw.ri_defaults_partner_activity_20110607 as select * FROM kalturadw.ri_defaults WHERE table_name IN ('Dwh_Dim_partner_sub_activity','Dwh_Dim_partner_activity');
create table kalturadw.ri_mapping_partner_activity_20110607 as select * FROM kalturadw.ri_mapping WHERE table_name = 'dwh_fact_Partner_Activities';
create table kalturadw.bisources_tables_20110607 as select * FROM kalturadw.bisources_tables where table_name in ('partner_sub_activity', 'partner_activity');
DELETE FROM kalturadw.ri_defaults WHERE table_name IN ('Dwh_Dim_partner_sub_activity','Dwh_Dim_partner_activity');
DELETE FROM kalturadw.ri_mapping WHERE table_name = 'dwh_fact_Partner_Activities';
DELETE from kalturadw.bisources_tables where table_name in ('partner_sub_activity', 'partner_activity');
