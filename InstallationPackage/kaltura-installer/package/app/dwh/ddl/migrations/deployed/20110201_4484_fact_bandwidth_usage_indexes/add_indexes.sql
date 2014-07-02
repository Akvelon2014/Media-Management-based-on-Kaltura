ALTER TABLE kalturadw.dwh_fact_bandwidth_usage
ADD INDEX activity_date_id(activity_date_id),
ADD INDEX activity_date_id_partner_id(activity_date_id, partner_id),
ADD INDEX partner_id(partner_id),
ADD INDEX file_id(file_id)
