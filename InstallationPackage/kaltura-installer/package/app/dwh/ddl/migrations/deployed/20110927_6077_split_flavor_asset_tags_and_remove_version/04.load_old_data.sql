USE kalturadw;

DROP PROCEDURE IF EXISTS load_tags;

DELIMITER $$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `load_tags`()
BEGIN
    DECLARE v_flavor_asset_id VARCHAR(60);
    DECLARE v_tags VARCHAR(256);
    DECLARE v_updated_at TIMESTAMP;
    DECLARE v_tag_name VARCHAR(256);
    DECLARE v_tag_id INT;
    DECLARE v_tags_done INT;
    DECLARE v_tags_idx INT;
    DECLARE done INT DEFAULT 0;
    DECLARE assets CURSOR FOR
    SELECT id, tags, updated_at
    FROM dwh_dim_flavor_asset;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN assets;

    read_loop: LOOP
        FETCH assets INTO v_flavor_asset_id, v_tags, v_updated_at;
        IF done THEN
             LEAVE read_loop;
	END IF;

        SET v_tags_done = 0;
        SET v_tags_idx = 1;

        WHILE NOT v_tags_done DO
            SET v_tag_name = SUBSTRING(v_tags, v_tags_idx,
					IF(LOCATE(',', v_tags, v_tags_idx) > 0,
					LOCATE(',', v_tags, v_tags_idx) - v_tags_idx,
					LENGTH(v_tags)));

            SET v_tag_name = TRIM(v_tag_name);
            IF LENGTH(v_tag_name) > 0 THEN
                SET v_tags_idx = v_tags_idx + LENGTH(v_tag_name) + 1;
                -- add the tag if it doesnt already exist
                INSERT IGNORE INTO dwh_dim_tags (tag_name) VALUES (v_tag_name);
                
		SELECT tag_id INTO v_tag_id FROM dwh_dim_tags WHERE tag_name = v_tag_name;

                -- add the flavor_asset tag
                INSERT IGNORE INTO dwh_dim_flavor_asset_tags (flavor_asset_id, tag_id, updated_at) VALUES (v_flavor_asset_id, v_tag_id, v_updated_at);
            ELSE
                SET v_tags_done = 1;
            END IF;
        END WHILE;
    END LOOP;
END$$

DELIMITER ;

CALL load_tags();

DROP PROCEDURE load_tags;
