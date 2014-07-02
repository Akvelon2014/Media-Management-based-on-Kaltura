USE kalturadw;

DROP TABLE IF EXISTS `dwh_dim_os`; 
CREATE TABLE `dwh_dim_os` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` varchar(50) NOT NULL,
  `is_mobile` boolean NOT NULL,
  `manufacturer` varchar(50) NOT NULL,
  `group` varchar(50) NOT NULL,
  `os` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `os` (`os`,`device`,`is_mobile`,`manufacturer`,`group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
