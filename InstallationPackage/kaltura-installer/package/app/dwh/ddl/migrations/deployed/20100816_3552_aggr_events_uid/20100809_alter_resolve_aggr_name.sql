DELIMITER $$

USE `kalturadw`$$

DROP FUNCTION IF EXISTS `resolve_aggr_name`$$

CREATE DEFINER=`etl`@`localhost` FUNCTION `resolve_aggr_name`(aggr_name VARCHAR(100),field_name VARCHAR(100)) RETURNS VARCHAR(100) CHARSET latin1
    DETERMINISTIC
BEGIN
	DECLARE aggr_table VARCHAR(100);
	DECLARE aggr_id_field VARCHAR(100);
	IF aggr_name = 'entry' THEN
		SET aggr_table = 'dwh_aggr_events_entry';
		SET aggr_id_field = 'entry_id';
	ELSEIF aggr_name = 'domain' THEN
		SET aggr_table = 'dwh_aggr_events_domain';
		SET aggr_id_field = 'domain_id';
	ELSEIF aggr_name = 'country' THEN
		SET aggr_table = 'dwh_aggr_events_country';
		SET aggr_id_field = 'country_id,location_id';
	ELSEIF aggr_name = 'partner' THEN
		SET aggr_table = 'dwh_aggr_partner';
		SET aggr_id_field = '';
	ELSEIF aggr_name = 'widget' THEN
		SET aggr_table = 'dwh_aggr_events_widget';
		SET aggr_id_field = 'widget_id';
	ELSEIF aggr_name = 'uid' THEN
		SET aggr_table = 'dwh_aggr_events_uid';
		SET aggr_id_field = 'uid';
	ELSE
		CALL ERROR_UNKNOWN_AGGR_NAME();
	END IF;
	
	IF field_name = 'aggr_table' THEN RETURN aggr_table;
	ELSEIF field_name = 'aggr_id_field' THEN RETURN aggr_id_field;
	END IF;
	RETURN '';
    END$$

DELIMITER ;