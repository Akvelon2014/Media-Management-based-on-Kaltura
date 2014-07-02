SELECT CONCAT('update kalturadw_bisources.bisources_',table_name,' set ',table_name,'_name = upper(',table_name,'_name);')
FROM kalturadw.bisources_tables