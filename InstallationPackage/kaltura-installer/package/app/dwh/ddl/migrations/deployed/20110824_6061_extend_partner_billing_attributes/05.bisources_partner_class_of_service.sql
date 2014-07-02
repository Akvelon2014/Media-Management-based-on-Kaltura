/*
SQLyog Community v8.7 
MySQL - 5.1.47 : Database - kalturadw_bisources
*********************************************************************
*/

USE `kalturadw_bisources`;

DROP TABLE IF EXISTS `bisources_partner_class_of_service`;

CREATE TABLE `bisources_partner_class_of_service` (
  `partner_class_of_service_id` SMALLINT(6) NOT NULL,
  `partner_class_of_service_name` VARCHAR(50) DEFAULT 'missing value',
  PRIMARY KEY (`partner_class_of_service_id`)
);

INSERT INTO `bisources_partner_class_of_service`
                        (`partner_class_of_service_id`,`partner_class_of_service_name`)
VALUES          (0,'N/A'),(1,'Silver'),(2,'Gold'),(3,'Platinum');

