DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `post_aggregation_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `post_aggregation_partner`(date_val DATE, p_hour_id INT(11))
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	
	SELECT aggr_table INTO v_aggr_table
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = 'partner';
	
	SET @s = CONCAT('INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		date_id, 
		hour_id,
		new_videos,
		new_images,
		new_audios,
		new_livestreams,
		new_playlists,
		new_documents,
		new_other_entries)
    		SELECT partner_id,DATE(''',date_val,''')*1 date_id, ', p_hour_id, ' hour_id,
    			SUM(IF(entry_type_id = 1 AND entry_media_type_id = 1, 1,0)) new_videos,
    			SUM(IF(entry_type_id = 1 AND entry_media_type_id = 2, 1,0)) new_images,
    			SUM(IF(entry_type_id = 1 AND entry_media_type_id = 5, 1,0)) new_audios,
			SUM(IF(entry_type_id = 7, 1,0)) new_livestreams,
			SUM(IF(entry_type_id = 5, 1,0)) new_playlists,
			SUM(IF(entry_type_id = 10, 1,0)) new_documents,
			SUM(IF(entry_type_id NOT IN (1,5,7,10) or (entry_type_id = 1 and entry_media_type_id NOT IN (1,2,5)), 1, 0)) new_other_entries
    		FROM dwh_dim_entries  en 
    		WHERE en.created_at between DATE(''',date_val,''') + INTERVAL ', p_hour_id, ' HOUR ',' 
					   AND DATE(''',date_val,''') + INTERVAL ', p_hour_id, ' + 1 HOUR - INTERVAL 1 SECOND ','
    	
		GROUP BY partner_id
    	ON DUPLICATE KEY UPDATE
    		new_videos=VALUES(new_videos),
		new_images=VALUES(new_images),
		new_audios=VALUES(new_audios),
		new_livestreams=VALUES(new_livestreams),
		new_playlists=VALUES(new_playlists),
		new_documents=VALUES(new_documents),
		new_other_entries=VALUES(new_other_entries);
    	');
	
 
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET @s = CONCAT('INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		 date_id, 
		 hour_id,
		 deleted_audios,
		 deleted_images,
		 deleted_videos,
		 deleted_documents,
		 deleted_livestreams,
		 deleted_playlists,
		 deleted_other_entries)
		SELECT  partner_id,DATE(''',date_val,''')*1 date_id, ', p_hour_id, ' hour_id,
			SUM(IF(entry_type_id = 1 AND entry_media_type_id = 1, 1,0)) deleted_videos,
			SUM(IF(entry_type_id = 1 AND entry_media_type_id = 2, 1,0)) deleted_images,
			SUM(IF(entry_type_id = 1 AND entry_media_type_id = 5, 1,0)) deleted_audios,
			SUM(IF(entry_type_id = 7, 1,0)) deleted_livestreams,
			SUM(IF(entry_type_id = 5, 1,0)) deleted_playlists,
			SUM(IF(entry_type_id = 10, 1,0)) deleted_documents,
			SUM(IF(entry_type_id NOT IN (1,5,7,10) or (entry_type_id = 1 and entry_media_type_id NOT IN (1,2,5)), 1, 0)) deleted_other_entries
		FROM 	dwh_dim_entries  en 
    		WHERE 	entry_status_id = 3
    			AND en.modified_at between DATE(''',date_val,''') + INTERVAL ', p_hour_id, ' HOUR ',' 
					   AND DATE(''',date_val,''') + INTERVAL ', p_hour_id, ' + 1 HOUR - INTERVAL 1 SECOND ','
    		GROUP BY partner_id
		ON DUPLICATE KEY UPDATE
			deleted_videos=VALUES(deleted_videos),
			deleted_images=VALUES(deleted_images),
			deleted_audios=VALUES(deleted_audios),
			deleted_livestreams=VALUES(deleted_livestreams),
			deleted_playlists=VALUES(deleted_playlists),
			deleted_documents=VALUES(deleted_documents),
			deleted_other_entries=VALUES(deleted_other_entries);
    	');

	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET @s = CONCAT('
    	INSERT INTO ',v_aggr_table,'
    		(partner_id, 
    		 date_id, 
		 hour_id,
    		 new_admins)
    	SELECT  partner_id, DATE(''',date_val,''')*1 date_id, ', p_hour_id, ' hour_id, count(*) new_admins
    	FROM dwh_dim_kusers  ku
    	WHERE ku.created_at between DATE(''',date_val,''') + INTERVAL ', p_hour_id, ' HOUR ',' 
					   AND DATE(''',date_val,''') + INTERVAL ', p_hour_id, ' + 1 HOUR - INTERVAL 1 SECOND ','
		and is_admin = 1
   		GROUP BY partner_id
    	ON DUPLICATE KEY UPDATE
		new_admins=VALUES(new_admins) ;
        ');
	
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

DELIMITER $$

USE `kalturadw`$$

DROP PROCEDURE IF EXISTS `reaggregate_post_data_partner`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `reaggregate_post_data_partner`()
BEGIN
    DECLARE v_date_id INT;
    DECLARE v_hour_id INT;

    DECLARE done INT DEFAULT 0;
    DECLARE aggrs CURSOR FOR SELECT aggr_day_int, hour_id FROM aggr_managment WHERE aggr_name = 'partner' AND is_calculated = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN aggrs;
    read_loop: LOOP
        FETCH aggrs INTO v_date_id, v_hour_id;
        IF done THEN
             LEAVE read_loop;
        END IF;
	
        CALL post_aggregation_partner(DATE(v_date_id), v_hour_id);

    END LOOP;
END$$

DELIMITER ;


CALL reaggregate_post_data_partner();

DROP PROCEDURE reaggregate_post_data_partner;
