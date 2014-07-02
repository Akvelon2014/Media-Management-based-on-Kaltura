CREATE TABLE `kalturadw`.`dwh_dim_categories` (
  `category_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `depth` tinyint(4) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL default '',
  `full_name` varchar(332) NOT NULL default '',
  `entries_count` int(11) NOT NULL default '0',
  `created_at` datetime default NULL,
  `created_date_id` int(11),
  `created_hour_id` tinyint(4),
  `updated_at` datetime default NULL,
  `updated_date_id` int(11),
  `updated_hour_id` tinyint(4),
  `deleted_at` datetime default NULL,
  `deleted_date_id` int(11),
  `deleted_hour_id` tinyint(4),
  PRIMARY KEY  (`category_id`),
  KEY `partner_id_full_name_index` (`partner_id`,`full_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8