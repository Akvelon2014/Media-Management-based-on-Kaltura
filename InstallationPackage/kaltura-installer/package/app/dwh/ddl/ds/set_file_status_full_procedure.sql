DELIMITER $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`set_file_status_full`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE kalturadw_ds.`set_file_status_full`(
	pfile_id INT(20),
	new_file_status VARCHAR(20),
	override_safety_check INT
    )
BEGIN
	DECLARE cur_status VARCHAR(20);
	IF override_safety_check = 1 THEN
		SELECT f.file_status
		INTO cur_status
		FROM kalturadw_ds.files f
		WHERE f.file_id = pfile_id;
		IF  new_file_status NOT IN ('WAITING','RUNNING','PROCESSED','TRANSFERING','DONE','FAILED')
		 OR new_file_status = 'RUNNING' AND cur_status <> 'WAITING'
		 OR new_file_status = 'PROCESSED' AND cur_status <> 'RUNNING'
		 OR new_file_status = 'TRANSFERING' AND cur_status <> 'PROCESSED'
		 OR new_file_status = 'DONE' AND cur_status <> 'TRANSFERING'
		THEN
			SET @s = CONCAT('call Illegal_state_trying_to_set_',
					new_file_status,'_to_', cur_status,'_file_',pfile_id);
			PREPARE stmt FROM  @s;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;		
		END IF;
	END IF;
	
	UPDATE kalturadw_ds.files f
	SET f.prev_status = f.file_status
	    ,f.file_status = new_file_status
	WHERE f.file_id = pfile_id;
	IF new_file_status = 'RUNNING'
	THEN 
		UPDATE kalturadw_ds.files f
		SET f.run_time = NOW()
		WHERE f.file_id = pfile_id;
	ELSEIF new_file_status = 'TRANSFERING'
	THEN 
		UPDATE kalturadw_ds.files f
		SET f.transfer_time = NOW()
		WHERE f.file_id = pfile_id;
	END IF;
    END$$

DELIMITER ;