DELIMITER $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`set_file_status`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE kalturadw_ds.`set_file_status`(
	pfile_id INT(20),
	new_file_status VARCHAR(20)
    )
BEGIN
	CALL set_file_status_full(pfile_id,new_file_status,1);
    END$$

DELIMITER ;