ALTER TABLE kalturadw.dwh_dim_kusers MODIFY screen_name VARCHAR(127);
ALTER TABLE kalturadw.dwh_dim_kusers MODIFY email VARCHAR(100);
ALTER TABLE kalturadw.dwh_dim_kusers MODIFY puser_id VARCHAR(100);
ALTER TABLE kalturadw.dwh_dim_kusers ADD first_name VARCHAR(40);
ALTER TABLE kalturadw.dwh_dim_kusers ADD last_name VARCHAR(40);