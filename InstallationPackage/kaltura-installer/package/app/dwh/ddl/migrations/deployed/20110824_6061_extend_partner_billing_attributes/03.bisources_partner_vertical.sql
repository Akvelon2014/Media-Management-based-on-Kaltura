/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_partner_vertical`;

CREATE TABLE `bisources_partner_vertical` (
  `partner_vertical_id` SMALLINT(6) NOT NULL,
  `partner_vertical_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`partner_vertical_id`)
);

INSERT INTO `bisources_partner_vertical`
			(`partner_vertical_id`,`partner_vertical_name`) 
VALUES 		(0,'N/A'),(1,'Media'),(2,'Education'),(3,'Enterprise'),(4,'Service Provider'),(5,'Other');
