DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `generate_QoS_report`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `generate_QoS_report`(p_date_val DATE)
BEGIN
	DECLARE yesterday_date_id INT(11);
	
	DECLARE the_day_before_yesteray_date_id INT(11);
	
	DECLARE 5_days_ago_date_id DATE;
	DECLARE 30_days_ago_date_id DATE;
	
	SET yesterday_date_id = (DATE(p_date_val) - INTERVAL 1 DAY)*1;
	
	SET the_day_before_yesteray_date_id = (DATE(p_date_val) - INTERVAL 2 DAY)*1;
	
	SET 5_days_ago_date_id = (DATE(p_date_val) - INTERVAL 5 DAY)*1;
	SET 30_days_ago_date_id = (DATE(p_date_val) - INTERVAL 30 DAY)*1;
	
	INSERT INTO kalturadw.dwh_qos_reports (measure, classification, DATE, yesterday, the_day_before, diff, last_5_days_avg, last_30_days_avg, outer_order, inner_order)
	
	SELECT 	measure AS Measure, 
		classification AS Classification, 
		IF (measure = 'Bandwidth (MB)',DATE(p_date_val) - INTERVAL 3 DAY, DATE(p_date_val))  AS 'Report Date', 
		IFNULL(yesterday, 0) yesterday, 
		IFNULL(the_day_before, 0) the_day_before, 
		IF(IFNULL(the_day_before, 0) = 0, 0, IFNULL(yesterday, 2)/the_day_before*100 - 100) diff,
		IFNULL(last_5_days, 0) AS last_5_days_avg, 
		IFNULL(last_30_days, 0) AS last_30_days_avg,
		outer_order, inner_order FROM (
	
	SELECT * FROM(
	
	SELECT 'Content' measure, 
		t.caption classification, 
		yesterday, 
		the_day_before, 
		last_5_days, 
		last_30_days,
		1 outer_order,
		sort_order inner_order
	FROM
	(
		SELECT 	IF(entry_media_type_id NOT IN (1, 5, 2, 6), IF (entry_media_type_id IN (11,12,13), -99999, -1), entry_media_type_id) entry_media_type_id,
			SUM(IF (created_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
			SUM(IF (created_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
			SUM(IF (created_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
			COUNT(*)/30 last_30_days			
			FROM kalturadw.dwh_dim_entries 
			WHERE created_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
			GROUP BY IF(entry_media_type_id NOT IN (1, 5, 2, 6), IF (entry_media_type_id IN (11,12,13), -99999, -1), entry_media_type_id)
	) e RIGHT OUTER JOIN 
	(
		SELECT 	entry_media_type_id, 
			CASE entry_media_type_name
				WHEN 'VIDEO' THEN 'Videos'
				WHEN 'AUDIO' THEN 'Audios'
				WHEN 'IMAGE' THEN 'Images'
				WHEN 'SHOW' THEN 'Mixs'
				WHEN 'PDF' THEN 'PDF'
				ELSE 'Other' END caption,
			CASE entry_media_type_name
				WHEN 'VIDEO' THEN 1
				WHEN 'AUDIO' THEN 2
				WHEN 'IMAGE' THEN 3
				WHEN 'SHOW' THEN 4
				WHEN 'PDF' THEN 5
				ELSE 6 END sort_order
		FROM (SELECT entry_media_type_id, entry_media_type_name FROM kalturadw.dwh_dim_entry_media_type UNION SELECT -99999, 'PDF') entry_media_type
		WHERE entry_media_type_id IN (1, 5, 2, 6, -99999, -1)
	) t
	ON e.entry_media_type_id = t.entry_media_type_id
	) Content	
	UNION
	
	SELECT * FROM ( 
	SELECT 	'Deleted' measure, 
		'Entries' Classification, 
		SUM(IF (modified_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
		SUM(IF (modified_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
		SUM(IF (modified_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
		COUNT(*)/30 last_30_days, 2 outer_order, 1 inner_order
		FROM kalturadw.dwh_dim_entries 
		WHERE modified_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
		AND entry_status_id = 3) deleted_entries
	UNION
	
	SELECT * FROM (
	SELECT 'Upload' measure, 
		caption classification, 
		yesterday, 
		the_day_before, 
		last_5_days, 
		last_30_days, 3 outer_order, sort_order inner_order 
		FROM 
		(
			SELECT IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL)) entry_status_id,
			SUM(IF (created_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
			SUM(IF (created_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
			SUM(IF (created_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
			COUNT(*)/30 last_30_days
			FROM kalturadw.dwh_dim_entries
			WHERE created_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
			AND entry_media_source_id = 1
			GROUP BY IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL))
		) e
		RIGHT OUTER JOIN 
		(
			SELECT 0 id, 'Ready' caption, 1 sort_order
			UNION 
			SELECT 1 id, 'Failed' caption, 2 sort_order
		) s
		ON e.entry_status_id = s.id) uploaded_entries
	
	UNION
	
	SELECT * FROM (SELECT 'Web Cam' measure, 
		caption classification, 
		yesterday, 
		the_day_before, 
		last_5_days, 
		last_30_days, 4 outer_order, sort_order inner_order FROM 
		(
			SELECT IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL)) entry_status_id,
			SUM(IF (created_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
			SUM(IF (created_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
			SUM(IF (created_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
			COUNT(*)/30 last_30_days
			FROM kalturadw.dwh_dim_entries
			WHERE created_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
			AND entry_media_source_id = 2
			GROUP BY IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL))
		) e
		RIGHT OUTER JOIN 
		(
			SELECT 0 id, 'Ready' caption, 1 sort_order
			UNION 
			SELECT 1 id, 'Failed' caption, 2 sort_order
		) s
		ON e.entry_status_id = s.id) web_cam
	
	UNION
	
	SELECT * FROM (SELECT 'Import' measure, 
		caption classification, 
		yesterday, 
		the_day_before, 
		last_5_days, 
		last_30_days, 5 outer_order, sort_order inner_order FROM 
		(
			SELECT IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL)) entry_status_id,
			SUM(IF (created_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
			SUM(IF (created_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
			SUM(IF (created_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
			COUNT(*)/30 last_30_days
			FROM kalturadw.dwh_dim_entries
			WHERE created_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
			AND entry_media_source_id NOT IN (1, 2)
			GROUP BY IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL))
		) e
		RIGHT OUTER JOIN 
		(
			SELECT 0 id, 'Ready' caption, 1 sort_order
			UNION 
			SELECT 1 id, 'Failed' caption, 2 sort_order
		) s
		ON e.entry_status_id = s.id) imported_entries
	
	UNION
	
	SELECT * FROM (SELECT 'Conversion' measure, 
		caption classification, 
		yesterday, 
		the_day_before, 
		last_5_days, 
		last_30_days, 6 outer_order, sort_order inner_order  FROM 
		(
			SELECT IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL)) entry_status_id,
			SUM(IF (created_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
			SUM(IF (created_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
			SUM(IF (created_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
			COUNT(*)/30 last_30_days
			FROM kalturadw.dwh_dim_entries
			WHERE created_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
			GROUP BY IF (entry_status_id = 2, 0, IF (entry_status_id IN (-2, -1, 0, 1, 4), 1, NULL))
		) e
		RIGHT OUTER JOIN 
		(
			SELECT 0 id, 'Ready' caption, 1 sort_order
			UNION 
			SELECT 1 id, 'Failed' caption, 2 sort_order
		) s
		ON e.entry_status_id = s.id) conversions
	UNION
	
	SELECT * FROM ( 
	SELECT 	'Storage MB' measure, 
		'Additional daily' Classification, 
		SUM(IF (date_id BETWEEN yesterday_date_id AND yesterday_date_id, count_storage, 0)) yesterday,
		SUM(IF (date_id BETWEEN the_day_before_yesteray_date_id AND the_day_before_yesteray_date_id, count_storage, 0)) the_day_before,
		SUM(IF (date_id BETWEEN 5_days_ago_date_id AND yesterday_date_id, count_storage, 0))/5 last_5_days,
		SUM(count_storage)/30 last_30_days,
		7 outer_order, 1 inner_order
		FROM kalturadw.dwh_aggr_partner 
		WHERE date_id BETWEEN 30_days_ago_date_id AND yesterday_date_id) STORAGE
	
	UNION 
	
	SELECT * FROM ( 
	SELECT 'Playback' Measure, 
		classification, 
		yesterday, the_day_before, last_5_days, last_30_days,
		8 outer_order, sort_order inner_order
	FROM (
		SELECT 'Playback' classification, 
		SUM(IF (date_id BETWEEN yesterday_date_id AND yesterday_date_id, count_plays, 0)) yesterday,
		SUM(IF (date_id BETWEEN the_day_before_yesteray_date_id AND the_day_before_yesteray_date_id, count_plays, 0)) the_day_before,
		SUM(IF (date_id BETWEEN 5_days_ago_date_id AND yesterday_date_id, count_plays, 0))/5 last_5_days,
		SUM(count_plays)/30 last_30_days, 1 sort_order
		FROM kalturadw.dwh_aggr_events_entry
		WHERE date_id BETWEEN 30_days_ago_date_id AND yesterday_date_id
		
		UNION
		SELECT '25%' classification,
		SUM(IF (date_id BETWEEN yesterday_date_id AND yesterday_date_id, count_plays_25, 0)) yesterday,
		SUM(IF (date_id BETWEEN the_day_before_yesteray_date_id AND the_day_before_yesteray_date_id, count_plays_25, 0)) the_day_before,
		SUM(IF (date_id BETWEEN 5_days_ago_date_id AND yesterday_date_id, count_plays_25, 0))/5 last_5_days,
		SUM(count_plays_25)/30 last_30_days, 2 sort_order
		FROM kalturadw.dwh_aggr_events_entry
		WHERE date_id BETWEEN 30_days_ago_date_id AND yesterday_date_id
		UNION 
		SELECT '50%' classification,
		SUM(IF (date_id BETWEEN yesterday_date_id AND yesterday_date_id, count_plays_50, 0)) yesterday,
		SUM(IF (date_id BETWEEN the_day_before_yesteray_date_id AND the_day_before_yesteray_date_id, count_plays_50, 0)) the_day_before,
		SUM(IF (date_id BETWEEN 5_days_ago_date_id AND yesterday_date_id, count_plays_50, 0))/5 last_5_days,
		SUM(count_plays_50)/30 last_30_days, 3 sort_order
		FROM kalturadw.dwh_aggr_events_entry
		WHERE date_id BETWEEN 30_days_ago_date_id AND yesterday_date_id
	
		UNION 
	
		SELECT '75%' classification,
		SUM(IF (date_id BETWEEN yesterday_date_id AND yesterday_date_id, count_plays_75, 0)) yesterday,
		SUM(IF (date_id BETWEEN the_day_before_yesteray_date_id AND the_day_before_yesteray_date_id, count_plays_75, 0)) the_day_before,
		SUM(IF (date_id BETWEEN 5_days_ago_date_id AND yesterday_date_id, count_plays_75, 0))/5 last_5_days,
		SUM(count_plays_75)/30 last_30_days, 4 sort_order
		FROM kalturadw.dwh_aggr_events_entry
		WHERE date_id BETWEEN 30_days_ago_date_id AND yesterday_date_id
		UNION 
		SELECT '100%' classification,
		SUM(IF (date_id BETWEEN yesterday_date_id AND yesterday_date_id, count_plays_100, 0)) yesterday,
		SUM(IF (date_id BETWEEN the_day_before_yesteray_date_id AND the_day_before_yesteray_date_id, count_plays_100, 0)) the_day_before,
		SUM(IF (date_id BETWEEN 5_days_ago_date_id AND yesterday_date_id, count_plays_100, 0))/5 last_5_days,
		SUM(count_plays_100)/30 last_30_days, 5 sort_order
		FROM kalturadw.dwh_aggr_events_entry
		WHERE date_id BETWEEN 30_days_ago_date_id AND yesterday_date_id
	) playback ) playback
	
	UNION
	SELECT * FROM ( 
	SELECT 'Registrations' measure, t.caption classification, 
		yesterday, 
		the_day_before, 
		last_5_days, 
		last_30_days, 9 outer_order, sort_order inner_order 
	FROM
	(
		SELECT 	IF(partner_type_id NOT IN (1, 102, 101, 104, 106, 103), 2, partner_type_id) partner_type_id,
			SUM(IF (created_at BETWEEN DATE(yesterday_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0)) yesterday,
			SUM(IF (created_at BETWEEN DATE(the_day_before_yesteray_date_id) AND DATE(the_day_before_yesteray_date_id) + INTERVAL 1 DAY, 1, 0)) the_day_before,
			SUM(IF (created_at BETWEEN DATE(5_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY, 1, 0))/5 last_5_days,
			COUNT(*)/30 last_30_days
		FROM kalturadw.dwh_dim_partners
		WHERE created_at BETWEEN DATE(30_days_ago_date_id) AND DATE(yesterday_date_id) + INTERVAL 1 DAY
		GROUP BY IF(partner_type_id NOT IN (1, 102, 101, 104, 106, 103), 2, partner_type_id)
	) p
	RIGHT OUTER JOIN 
	(
		SELECT partner_type_id, 
		CASE partner_type_name
			WHEN 'KMC_SIGNUP' THEN 'Kaltura'
			WHEN 'DRUPAL' THEN 'Drupal'
			WHEN 'WORDPRESS' THEN 'WordPress'
			WHEN 'MODDLE' THEN 'Moodle'
			WHEN 'JOOMLA ' THEN 'Joomla'
			WHEN 'MIND_TOUCH' THEN 'MindTouch'
			ELSE 'Other' 
		END caption,
		CASE partner_type_name
			WHEN 'KMC_SIGNUP' THEN 1
			WHEN 'DRUPAL' THEN 2
			WHEN 'WORDPRESS' THEN 3
			WHEN 'MOODLE' THEN 4
			WHEN 'JOOMLA ' THEN 5
			WHEN 'MIND_TOUCH' THEN 6
			ELSE 7 
		END sort_order, 
		partner_type_name
		FROM kalturadw.dwh_dim_partner_type WHERE partner_type_id NOT IN (0,-1, 105, 100)
	) t
	ON (p.partner_type_id = t.partner_type_id)) Registrations
	
	
	UNION
	SELECT * FROM ( 
	SELECT 	'Bandwidth (MB)' measure, 
		caption classification, 
		yesterday,
		the_day_before,
		last_5_days,
		last_30_days, 10 outer_order, sort_order inner_order 
		FROM 
		(
			SELECT partner_sub_activity_id,
			SUM(IF (activity_date_id BETWEEN (DATE(yesterday_date_id) - INTERVAL 3 DAY)*1 AND (DATE(yesterday_date_id) - INTERVAL 3 DAY)*1 , amount, 0))/1024 yesterday,
			SUM(IF (activity_date_id BETWEEN (DATE(the_day_before_yesteray_date_id) - INTERVAL 3 DAY)*1 AND (DATE(the_day_before_yesteray_date_id) - INTERVAL 3 DAY)*1 , amount, 0))/1024 the_day_before,
			SUM(IF (activity_date_id BETWEEN (DATE(5_days_ago_date_id) - INTERVAL 3 DAY)*1 AND (DATE(yesterday_date_id) - INTERVAL 3 DAY)*1 , amount, 0))/5/1024 last_5_days,
			SUM(amount)/30/1024 last_30_days
			FROM kalturadw.dwh_fact_partner_activities 
			WHERE partner_activity_id = 1 AND activity_date_id BETWEEN (DATE(30_days_ago_date_id) - INTERVAL 3 DAY)*1 AND (DATE(yesterday_date_id) - INTERVAL 3 DAY)*1 
			GROUP BY partner_sub_activity_id
		) bandwidth 
		RIGHT OUTER JOIN
		(
			SELECT 1 id, 'www.kaltura.com' caption, 4 sort_order
			UNION 
			SELECT 2 id, 'Limelight' caption, 2 sort_order
			UNION 
			SELECT 3 id, 'Level3' caption, 3 sort_order
			UNION 
			SELECT 4 id, 'Akamai' caption, 1 sort_order
		) filler
		ON (bandwidth.partner_sub_activity_id = filler.id)) Bandwidth) all_tables
		ON DUPLICATE KEY UPDATE 
			yesterday = VALUES(yesterday), 
			the_day_before = VALUES(the_day_before), 
			diff = VALUES(diff), 
			last_5_days_avg = VALUES(last_5_days_avg), 
			last_30_days_avg = VALUES(last_30_days_avg),
			outer_order = VALUES(outer_order),
			inner_order = VALUES(inner_order);
		
		SELECT 	measure AS Measure, 
		classification AS Classification, 
		DATE AS 'Report Date', 
		FORMAT(yesterday, 2) AS 'Yesterday', 
		FORMAT(the_day_before, 2) AS 'Day Before Yesterday', 
		CONCAT(diff, '%') AS 'Diff',
		FORMAT(last_5_days_avg, 2) AS 'Last 5 Days (AVG)', 
		FORMAT(last_30_days_avg, 2) AS 'Last 30 Days (AVG)'
		FROM kalturadw.dwh_qos_reports
		WHERE DATE = IF (measure = 'Bandwidth (MB)',DATE(p_date_val) - INTERVAL 3 DAY, DATE(p_date_val))
		ORDER BY outer_order, inner_order;
END$$

DELIMITER ;
