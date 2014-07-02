DELIMITER $$

USE `kalturadw_ds`$$

DROP PROCEDURE IF EXISTS `transfer_ods_partition`$$

CREATE DEFINER=`etl`@`localhost` PROCEDURE `transfer_ods_partition`(
	staging_area_id INTEGER, partition_number VARCHAR(10)
)
BEGIN
DECLARE src_table VARCHAR(45);
DECLARE tgt_table VARCHAR(45);
DECLARE dup_clause VARCHAR(4000);
DECLARE partition_field VARCHAR(45);
DECLARE select_fields VARCHAR(4000);
DECLARE post_transfer_sp VARCHAR(4000);
DECLARE aggr_date VARCHAR(4000);
DECLARE s VARCHAR(4000);
SELECT source_table,target_table,IFNULL(on_duplicate_clause,''),staging_partition_field,post_transfer_sp,aggr_date_field
INTO src_table,tgt_table,dup_clause,partition_field,post_transfer_sp,aggr_date
FROM staging_areas
WHERE id=staging_area_id;

IF LENGTH(AGGR_DATE) > 0 THEN
SELECT CONCAT('update kalturadw.aggr_managment set is_calculated=0 where aggr_day_int in ',
	      '(select distinct ',aggr_date,
	        ' from ',src_table,
	        ' where ',partition_field,' = ',partition_number,')') INTO s;
SET @s = s;
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END IF;

SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position)
INTO select_fields
FROM information_schema.columns
WHERE CONCAT(table_schema,'.',table_name) = tgt_table;

	SELECT CONCAT('insert into ',tgt_table,
 ' select ',select_fields,
			 ' from ',src_table,
			 ' where ',partition_field,'  = ',partition_number,
			 ' ',dup_clause ) INTO s;
SET @s = s;
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

IF LENGTH(POST_TRANSFER_SP)>0 THEN
SET @s = CONCAT('call ',post_transfer_sp,'(',partition_number,')');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END IF;

END$$

DELIMITER ;