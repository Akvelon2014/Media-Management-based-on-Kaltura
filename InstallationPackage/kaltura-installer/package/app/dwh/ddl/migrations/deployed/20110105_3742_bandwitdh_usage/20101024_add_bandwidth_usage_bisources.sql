CREATE TABLE kalturadw_bisources.bisources_bandwidth_source
(`bandwidth_source_id` INT, `bandwidth_source_name` VARCHAR(50),
PRIMARY KEY(`bandwidth_source_id`));

INSERT INTO kalturadw_bisources.bisources_bandwidth_source VALUES (1, 'WWW'),(2, 'LLN'),(3,'LEVEL3'), (4,'AKAMAI'), (5, 'FMS Streaming');

CREATE TABLE kalturadw.dwh_dim_bandwidth_source
(`bandwidth_source_id` INT,
`bandwidth_source_name` VARCHAR(50),
dwh_creation_date DATETIME ,
dwh_update_date DATETIME ,
ri_ind TINYINT DEFAULT 0,
PRIMARY KEY(`bandwidth_source_id`));

INSERT INTO kalturadw.bisources_tables VALUES('bandwidth_source',1);
