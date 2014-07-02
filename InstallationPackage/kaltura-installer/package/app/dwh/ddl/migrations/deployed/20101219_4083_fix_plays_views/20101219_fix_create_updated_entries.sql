DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `create_updated_entries`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `create_updated_entries`(max_date DATE)
BEGIN
	TRUNCATE TABLE kalturadw_ds.updated_entries;
	
	UPDATE kalturadw.aggr_managment SET start_time = NOW() WHERE is_calculated = 0 AND aggr_day < max_date AND aggr_name = 'plays_views';
	
	INSERT INTO kalturadw_ds.updated_entries SELECT entries.entry_id, SUM(count_loads)+IFNULL(old_entries.views,0) views, SUM(count_plays)+IFNULL(old_entries.plays,0) plays FROM 
	(SELECT distinct entry_id 
		FROM kalturadw.dwh_aggr_events_entry e
		INNER JOIN kalturadw.aggr_managment m ON (e.date_id = m.aggr_day_int)
		WHERE is_calculated = 0 
		  AND m.aggr_day < max_date
		  AND m.aggr_name = 'plays_views') entries
	INNER JOIN
	kalturadw.dwh_aggr_events_entry
	ON (dwh_aggr_events_entry.entry_id = entries.entry_id)
	LEFT OUTER JOIN
	kalturadw.entry_plays_views_before_08_2009 AS old_entries
	ON (entries.entry_id = old_entries.entry_id)
	GROUP BY entries.entry_id;
    END$$

DELIMITER ;