DELIMITER $$

USE `kaltura`$$

DROP PROCEDURE IF EXISTS `update_entries`$$

CREATE DEFINER=`etl`@`%` PROCEDURE `update_entries`()
BEGIN
    
    DECLARE done INT DEFAULT 0;
    DECLARE entry_id CHAR(50);
    DECLARE new_views, new_plays INT;
    DECLARE updated_entries CURSOR FOR SELECT id, plays, views FROM temp_entry_update;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN updated_entries;
    
    SET @counter = 0;
    SET @sleep_duration=1;
    SET @iterations=100;
 
    SET SESSION sql_log_bin = 1;
    REPEAT
    FETCH updated_entries INTO entry_id, new_plays, new_views;
    SELECT SLEEP(IF((@counter:=@counter+1)%@iterations=0,@sleep_duration,0));
    UPDATE entry SET entry.plays = new_plays, entry.views = new_views WHERE entry.id = entry_id;
    UNTIL done END REPEAT;
    SET SESSION sql_log_bin = 0;
    CLOSE updated_entries;
    END$$

DELIMITER ;
