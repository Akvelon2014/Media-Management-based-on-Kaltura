USE kalturadw;

ALTER TABLE dwh_dim_entries
DROP KEY operational_measures_updated_at,
DROP COLUMN operation_measures_updated_at;