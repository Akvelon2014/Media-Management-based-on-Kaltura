#  DECLARE MAX_ACTIVITY_ID INTEGER;
#  DECLARE RETURN_VAL      INTEGER; #used as a generic return variable

####################
# START ETL TABLES #
####################

# create new tables
DROP TABLE IF EXISTS `kalturadw_ds`.`processes`;
CREATE TABLE  `kalturadw_ds`.`processes` (
`id` int(10) unsigned NOT NULL,
`process_name` varchar(45) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

insert into kalturadw_ds.processes (id,process_name) values
(1,'events'),
(2,'fms_streaming'),
(3,'partner_activity');

DROP TABLE IF EXISTS `kalturadw_ds`.`staging_areas`;
CREATE TABLE  `kalturadw_ds`.`staging_areas` (
`id` int(10) unsigned NOT NULL,
`process_id` int(10) unsigned NOT NULL,
`source_table` varchar(45) NOT NULL,
`target_table` varchar(45) NOT NULL,
`on_duplicate_clause` varchar(4000) DEFAULT NULL,
`staging_partition_field` varchar(45) DEFAULT NULL,
`post_transfer_sp` varchar(500) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# *Note that the staging area ods_fms_session_events DOES NOT have a post_transfer_sp value.
# When the aggregate from fms to partner activity will move to production, staging_areas will be updated
insert into kalturadw_ds.staging_areas (id,process_id,source_table,target_table,on_duplicate_clause,staging_partition_field)
values
(1,1,'ds_events','kalturadw.dwh_fact_events','ON DUPLICATE KEY UPDATE kalturadw.dwh_fact_events.file_id = kalturadw.dwh_fact_events.file_id','file_id'),
(2,2,'ods_fms_session_events','kalturadw.dwh_fact_session_events','ON DUPLICATE KEY UPDATE kalturadw.dwh_fact_fms_session_events.file_id = kalturadw.dwh_fact_fms_session_events.file_id','file_id');

# partner activity parameter insert is in the partner activity section
DROP TABLE IF EXISTS `kalturadw_ds`.`parameters`;
CREATE TABLE  `kalturadw_ds`.`parameters` (
`id` int(11) unsigned NOT NULL,
`process_id` int(11) unsigned NOT NULL,
`parameter_name` varchar(100) NOT NULL,
`int_value` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


# note default 1 - all existing files are process_id 1
alter table kalturadw_ds.files add (process_id integer default 1);

###################
# STOP ETL TABLES #
###################

DELIMITER $$

########################
# START ETL MANAGEMENT #
########################

# new management procedures
# add_ods_partition
DROP PROCEDURE IF EXISTS `kalturadw_ds`.`add_ods_partition` $$
CREATE PROCEDURE  `kalturadw_ds`.`add_ods_partition`(
	partition_number VARCHAR(10),
table_name VARCHAR(32)
)
BEGIN
	SET @s = CONCAT('alter table kalturadw_ds.',table_name,' ADD PARTITION (partition p_' ,
			partition_number ,' values in (', partition_number ,'))');
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END $$

# drop_ods_partition
DROP PROCEDURE IF EXISTS `kalturadw_ds`.`drop_ods_partition` $$
CREATE  PROCEDURE  `kalturadw_ds`.`drop_ods_partition`(
	partition_number VARCHAR(10),
table_name VARCHAR(32)
	)
BEGIN
	SET @s = CONCAT('alter table kalturadw_ds.',table_name,' drop PARTITION  p_' ,
			partition_number );
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END $$

# empty_ods_partition
DROP PROCEDURE IF EXISTS `kalturadw_ds`.`empty_ods_partition` $$
CREATE PROCEDURE  `kalturadw_ds`.`empty_ods_partition`(
	partition_number VARCHAR(10),
table_name VARCHAR(32)
)
BEGIN
	CALL drop_ods_partition(partition_number,table_name);
	CALL add_ods_partition(partition_number,table_name);
END $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`transfer_ods_partition` $$
CREATE DEFINER=`etl`@`localhost` PROCEDURE  `kalturadw_ds`.`transfer_ods_partition`(
	staging_area_id INTEGER, partition_number VARCHAR(10)
)
BEGIN
DECLARE src_table VARCHAR(45);
DECLARE tgt_table VARCHAR(45);
DECLARE dup_clause VARCHAR(4000);
DECLARE partition_field VARCHAR(45);
DECLARE select_fields VARCHAR(4000);
DECLARE post_transfer_sp VARCHAR(4000);
DECLARE s VARCHAR(4000);
SELECT source_table,target_table,IFNULL(on_duplicate_clause,''),staging_partition_field,post_transfer_sp
INTO src_table,tgt_table,dup_clause,partition_field,post_transfer_sp
FROM staging_areas
WHERE id=staging_area_id;

SELECT GROUP_CONCAT(column_name ORDER BY ordinal_position)
INTO select_fields
FROM information_schema.columns
WHERE CONCAT(table_schema,'.',table_name) = tgt_table;

	select CONCAT('insert into ',tgt_table,
 ' select ',select_fields,
			 ' from ',src_table,
			 ' where ',partition_field,'  = ',partition_number,
			 ' ',dup_clause ) into s;
SET @s = s;
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

IF LENGTH(POST_TRANSFER_SP)>0 THEN
	select CONCAT('call ',post_transfer_sp,'(',partition_number,')') into s;
	SET @s = s;
	PREPARE stmt FROM  @s;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END IF;

END $$

# change existing management procs

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`add_file_partition` $$
CREATE PROCEDURE  `kalturadw_ds`.`add_file_partition`(
	partition_number VARCHAR(10)
)
BEGIN
CALL kalturadw_ds.add_ods_partition(partition_number,'ds_events');
END $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`drop_file_partition` $$
CREATE PROCEDURE  `kalturadw_ds`.`drop_file_partition`(
	partition_number VARCHAR(10)
	)
BEGIN
CALL kalturadw_ds.drop_ods_partition(partition_number,'ds_events');
END $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`empty_file_partition` $$
CREATE PROCEDURE  `kalturadw_ds`.`empty_file_partition`(
	partition_number VARCHAR(10)
)
BEGIN
	CALL drop_file_partition(partition_number);
	CALL add_file_partition(partition_number);
END $$

DROP PROCEDURE IF EXISTS `kalturadw_ds`.`transfer_file_partition` $$
CREATE PROCEDURE  `kalturadw_ds`.`transfer_file_partition`(
	partition_number VARCHAR(10)
)
BEGIN
	CALL transfer_ods_partition(1,partition_number);
END $$

#######################
# STOP ETL MANAGEMENT #
#######################

DELIMITER ;

##########################
# START PARTNER ACTIVITY #
##########################

insert into kalturadw_ds.parameters (id,process_id,parameter_name,int_value)
select 1,3,'max_operational_partner_activity',max(activity_id)
from kalturadw.dwh_fact_partner_activities;

alter table kalturadw.dwh_fact_partner_activities drop primary key;

########################
# END PARTNER ACTIVITY #
########################

