USE kalturadw;

CREATE TABLE `dwh_dim_time` (
  `day_id` int(11) NOT NULL DEFAULT '0',
  `date_field` date NOT NULL,
  `datetime_field` datetime NOT NULL,
  `day_eng_name` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `month_str` varchar(50) DEFAULT NULL,
  `month_id` int(11) DEFAULT NULL,
  `month_eng_name` varchar(50) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `day_of_year` int(11) DEFAULT NULL,
  `day_of_month` int(11) DEFAULT NULL,
  `day_of_week` int(11) DEFAULT NULL,
  `week_id` int(11) DEFAULT NULL,
  `week_of_year` int(11) DEFAULT NULL,
  `week_eng_name` varchar(50) DEFAULT NULL,
  `day_of_week_desc` varchar(30) DEFAULT NULL,
  `day_of_week_short_desc` varchar(3) DEFAULT NULL,
  `month_desc` varchar(30) DEFAULT NULL,
  `month_short_desc` varchar(3) DEFAULT NULL,
  `quarter` char(1) DEFAULT NULL,
  `quarter_id` int(11) DEFAULT NULL,
  `quarter_eng_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`day_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
