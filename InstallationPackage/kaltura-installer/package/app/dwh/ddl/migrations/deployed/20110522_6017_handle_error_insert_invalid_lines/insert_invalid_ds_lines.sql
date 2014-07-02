DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `insert_invalid_ds_line`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `insert_invalid_ds_line`(line_number_param INT(11), 
									file_id_param INT(11), 
									error_reason_param VARCHAR(255), 
									ds_line_param VARCHAR(4096), 
									date_id_param INT(11),
									partner_id_param INT(11), 
									cycle_id_param INT(11), 
									process_id_param INT(11))
BEGIN
	INSERT IGNORE INTO invalid_ds_lines (line_number, file_id, error_reason_code, ds_line, date_id, partner_id, cycle_id, process_id)
	VALUES (line_number_param, file_id_param, get_error_code(error_reason_param), ds_line_param, date_id_param, partner_id_param, cycle_id_param, process_id_param);
END$$

DELIMITER ;
