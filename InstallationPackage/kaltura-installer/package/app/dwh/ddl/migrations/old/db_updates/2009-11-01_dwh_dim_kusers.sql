ALTER TABLE kalturadw.dwh_dim_kusers   
    ADD storage_size INT,
    ADD puser_id VARCHAR(64),
    ADD admin_tags TEXT,
    ADD indexed_partner_data_int INT,
    ADD indexed_partner_data_string VARCHAR(64);