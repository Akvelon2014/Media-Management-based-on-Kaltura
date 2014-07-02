use `kalturadw_ds`;

DROP PROCEDURE IF EXISTS  `mark_as_aggregated`;

DELIMITER $$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `mark_as_aggregated`( max_date VARCHAR(4000), aggr_name VARCHAR(50))
BEGIN
	SET @s = CONCAT('update kalturadw.aggr_managment set is_calculated=1, end_time=now() ',
			'where aggr_day < ''',max_date,''' ',
            'and is_calculated = 0 ',
			'and (aggr_name = ''',aggr_name,''' or ''all''=''',aggr_name,''');');
	PREPARE stmt FROM @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
    END $$
DELIMITER ;
