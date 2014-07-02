CREATE VIEW kalturadw.dwh_dim_referrer_v AS SELECT referrer_id, CONCAT(d.domain_name, '/', r.referrer) referrer FROM kalturadw.dwh_dim_domain_referrer r, kalturadw.dwh_dim_domain d
WHERE r.domain_id = d.domain_id;
