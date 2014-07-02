ALTER TABLE `kalturadw`.`aggr_managment` 
   ADD COLUMN aggr_day_int INT(11) UNSIGNED DEFAULT 0 NOT NULL AFTER `aggr_name`;

UPDATE `kalturadw`.`aggr_managment`
SET aggr_day_int = DATE_FORMAT(aggr_day, '%Y%m%d');

ALTER TABLE `kalturadw`.`aggr_managment`
   CHANGE `aggr_day` `aggr_day` DATE DEFAULT '0000-00-00' NOT NULL,
   DROP PRIMARY KEY, 
   ADD PRIMARY KEY(`aggr_name`, `aggr_day_int`);
   