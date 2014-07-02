CREATE TABLE `dwh_dim_flavor_params_conversion_profile` (
  `conversion_profile_id` int(11) NOT NULL,
  `flavor_params_id` int(11) NOT NULL,
  `ready_behavior` tinyint(4) NOT NULL,
  `flavor_params_conversion_profile_id` int(11) NOT NULL ,
  `created_at` datetime default NULL,
  `created_date_id` int(11),
  `created_hour_id` tinyint(4),
  `updated_at` datetime default NULL,
  `updated_date_id` int(11),
  `updated_hour_id` tinyint(4),
  PRIMARY KEY  (`flavor_params_conversion_profile_id`),
  KEY `flavor_params_conversion_profile_FI_1` (`conversion_profile_id`),
  KEY `flavor_params_conversion_profile_FI_2` (`flavor_params_id`),
  KEY `updated_at_FI_3` (`updated_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8