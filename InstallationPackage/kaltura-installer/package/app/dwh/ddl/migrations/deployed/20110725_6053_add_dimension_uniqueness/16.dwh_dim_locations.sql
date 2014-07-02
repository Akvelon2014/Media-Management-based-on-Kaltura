ALTER TABLE kalturadw.dwh_dim_locations 
ADD UNIQUE KEY (location_name, location_type_name, country, state, city),
CHANGE country country VARCHAR(50) NOT NULL DEFAULT '',
CHANGE state state VARCHAR(50) NOT NULL DEFAULT '',
CHANGE city city VARCHAR(50) NOT NULL DEFAULT '';
