DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `resolve_aggr_name`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION `resolve_aggr_name`(p_aggr_name VARCHAR(100),p_field_name VARCHAR(100)) RETURNS VARCHAR(100) CHARSET latin1
    DETERMINISTIC
BEGIN
	DECLARE v_aggr_table VARCHAR(100);
	DECLARE v_aggr_id_field VARCHAR(100);
	SELECT aggr_table, aggr_id_field
	INTO  v_aggr_table, v_aggr_id_field
	FROM kalturadw_ds.aggr_name_resolver
	WHERE aggr_name = p_aggr_name;
	
	IF p_field_name = 'aggr_table' THEN RETURN v_aggr_table;
	ELSEIF p_field_name = 'aggr_id_field' THEN RETURN v_aggr_id_field;
	END IF;
	RETURN '';
    END$$

DELIMITER ;