DELIMITER $$

USE `kalturadw_ds`$$

DROP FUNCTION IF EXISTS `get_error_code`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION `get_error_code`(error_code_reason_param varchar(255)) RETURNS SMALLINT(6)
    NO SQL
BEGIN
	DECLARE error_code smallint(6);
	INSERT IGNORE kalturadw_ds.invalid_ds_lines_error_codes (error_code_reason) VALUES(error_code_reason_param);
	SELECT error_code_id 
		INTO error_code
		FROM kalturadw_ds.invalid_ds_lines_error_codes
		WHERE error_code_reason = error_code_reason_param;
	return error_code;
END$$

DELIMITER ;


