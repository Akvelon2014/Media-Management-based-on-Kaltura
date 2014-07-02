USE kalturadw;

DROP TABLE IF EXISTS `dwh_dim_browser`;
CREATE TABLE `dwh_dim_browser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `browser` varchar(50) NOT NULL,
  `group` varchar(50) NOT NULL,
  `manufacturer` varchar(50) NOT NULL,
  `render_engine` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `browser` (`browser`,`group`,`manufacturer`,`render_engine`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
