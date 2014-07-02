DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `calc_entries_sizes`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `calc_entries_sizes`(p_date_id INT(11))
BEGIN
	DECLARE v_date DATETIME;
	SET v_date = DATE(p_date_id);
	UPDATE aggr_managment SET start_time = NOW() WHERE aggr_name = 'storage_usage' AND aggr_day_int = p_date_id;
	
	DROP TABLE IF EXISTS today_file_sync_subset; 
	
	/* Attach today's file_sync records with entries and flavor_assets*/
	CREATE TEMPORARY TABLE today_file_sync_subset AS
	SELECT DISTINCT s.id, s.partner_id, IFNULL(a.entry_id, object_id) entry_id, object_id, object_type, object_sub_type, IFNULL(file_size, 0) file_size
	FROM kalturadw.dwh_dim_file_sync s LEFT OUTER JOIN kalturadw.dwh_dim_flavor_asset a
	ON (object_type = 4 AND s.object_id = a.id AND a.entry_id IS NOT NULL)
	WHERE s.updated_at BETWEEN v_date AND v_date + INTERVAL 1 DAY
	AND object_type IN (1,4)
	AND original = 1
	AND s.STATUS IN (2,3)
	AND s.partner_id NOT IN (100 /*Default Partner*/ , -1 /*Batch Partner*/ , -2 /*Admin Console*/, 0/* Common Partner Content*/, 99/*Template Partner*/);
	
	ALTER TABLE today_file_sync_subset ADD INDEX id (`id`);	
	
	/* Get the latest version of each file_sync group according to object_id, object_type, object_sub_type */
	DROP TABLE IF EXISTS today_file_sync_max_version_ids;
	CREATE TEMPORARY TABLE today_file_sync_max_version_ids AS
	SELECT MAX(id) id, partner_id, entry_id, object_id, object_type, object_sub_type FROM today_file_sync_subset
	GROUP BY partner_id, entry_id, object_id, object_type, object_sub_type;
	DROP TABLE IF EXISTS today_sizes;
	
	/* Get the sizes of the latest versions */
	CREATE TEMPORARY TABLE today_sizes AS
	SELECT max_id.partner_id, max_id.entry_id, max_id.object_id, max_id.object_type, max_id.object_sub_type, original.file_size 
	FROM today_file_sync_max_version_ids max_id, today_file_sync_subset original
	WHERE max_id.id = original.id;
	
	ALTER TABLE today_sizes ADD UNIQUE INDEX unique_key (`partner_id`, `entry_id`, `object_id`, `object_type`, `object_sub_type`);
	
	/* Append today file_sync record with empty file_sync records of deleted flavor_assets */
	INSERT INTO today_sizes 
		SELECT DISTINCT f.partner_id, f.entry_id, f.id, s.object_type, s.object_sub_type, 0 file_size
		FROM kalturadw.dwh_dim_flavor_asset f, kalturadw.dwh_dim_file_sync s
		WHERE f.STATUS = 3
		AND f.deleted_at BETWEEN v_date AND v_date + INTERVAL 1 DAY
		AND f.id = s.object_id
		AND s.object_type = 4
		AND s.updated_at < v_date
		AND s.file_size > 0
	ON DUPLICATE KEY UPDATE
		file_size = VALUES(file_size);
	
	ALTER TABLE today_sizes DROP INDEX unique_key;
	
	/* Get the the file_sync records that were created before today and are actually
	earlier version of all the file_sync records of today (including the records we appended of deleted file_sync records */
	DROP TABLE IF EXISTS yesterday_file_sync_subset; 
	CREATE TEMPORARY TABLE yesterday_file_sync_subset AS
	SELECT f.id, f.partner_id, f.object_id, f.object_type, f.object_sub_type, IFNULL(f.file_size, 0) file_size
	FROM today_sizes today, kalturadw.dwh_dim_file_sync f
	WHERE f.object_id = today.object_id
	AND f.partner_id = today.partner_id
	AND f.object_type = today.object_type
	AND f.object_sub_type = today.object_sub_type
	AND f.updated_at < v_date
	AND f.original = 1
	AND f.STATUS IN (2,3);
	
	/* Get the latest version of the past's file_sync record*/
	DROP TABLE IF EXISTS yesterday_file_sync_max_version_ids;
	CREATE TEMPORARY TABLE yesterday_file_sync_max_version_ids AS
	SELECT MAX(id) id, partner_id, object_id, object_type, object_sub_type FROM yesterday_file_sync_subset
	GROUP BY partner_id, object_id, object_type, object_sub_type;
	/* Get the sizes of the latest versions of the past file_sync records*/
	DROP TABLE IF EXISTS yesterday_sizes;
	CREATE TEMPORARY TABLE yesterday_sizes AS
	SELECT max_id.partner_id, max_id.object_id, max_id.object_type, max_id.object_sub_type, original.file_size 
	FROM yesterday_file_sync_max_version_ids max_id, yesterday_file_sync_subset original
	WHERE max_id.id = original.id;
	
	/* Get the delta between today's file_sync record size and its previous version*/
	INSERT INTO kalturadw.dwh_fact_entries_sizes (partner_id, entry_id, entry_additional_size_kb, entry_size_date, entry_size_date_id)
	SELECT t.partner_id, t.entry_id, ROUND(SUM(t.file_size - IFNULL(Y.file_size, 0))/1024, 3) entry_additional_size_kb,v_date, p_date_id 
	FROM today_sizes t LEFT OUTER JOIN yesterday_sizes Y
	ON t.object_id = Y.object_id
	AND t.partner_id = Y.partner_id
	AND t.object_type = Y.object_type
	AND t.object_sub_type = Y.object_sub_type
	AND t.file_size <> Y.file_size
	GROUP BY t.partner_id, t.entry_id
	ON DUPLICATE KEY UPDATE 
		entry_additional_size_kb = VALUES(entry_additional_size_kb);
	
	/* Get deleted entries and created a record in the fact_entry_sizes table with a negative number which is the size of the entry before its deletion. */
	DROP TABLE IF EXISTS deleted_entries;
	CREATE TEMPORARY TABLE deleted_entries AS
		SELECT es.partner_id partner_id, es.entry_id entry_id, v_date entry_size_date, p_date_id entry_size_date_id, -SUM(entry_additional_size_kb) entry_additional_size_kb
		FROM kalturadw.dwh_dim_entries e USE INDEX (modified_at) INNER JOIN kalturadw.dwh_fact_entries_sizes es
		WHERE e.modified_at BETWEEN v_date AND v_date + INTERVAL 1 DAY
		AND e.entry_id = es.entry_id 
		AND e.partner_id = es.partner_id 
		AND e.partner_id NOT IN (100 /*Default Partner*/ , -1 /*Batch Partner*/ , -2 /*Admin Console*/, 0/* Common Partner Content*/, 99/*Template Partner*/)
		AND e.entry_type_id = 1
		AND e.entry_status_id = 3
		AND es.entry_size_date_id < p_date_id
		GROUP BY es.partner_id, es.entry_id
		HAVING SUM(entry_additional_size_kb) > 0;
	
	INSERT INTO kalturadw.dwh_fact_entries_sizes (partner_id, entry_id, entry_size_date, entry_size_date_id, entry_additional_size_kb)
		SELECT partner_id, entry_id, entry_size_date, entry_size_date_id, entry_additional_size_kb FROM deleted_entries
	ON DUPLICATE KEY UPDATE 
		entry_additional_size_kb = VALUES(entry_additional_size_kb);
	
	UPDATE aggr_managment SET is_calculated = 1, end_time = NOW() WHERE aggr_name = 'storage_usage' AND aggr_day_int = p_date_id;
	UPDATE aggr_managment SET is_calculated = 0 WHERE aggr_name = 'partner_usage' AND aggr_day_int >= p_date_id;
	
END$$

DELIMITER ;