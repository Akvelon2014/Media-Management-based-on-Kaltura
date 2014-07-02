CREATE TABLE `monitor_status` (
  `source` varchar(50) NOT NULL COMMENT 'The monitoring file used for monitoring',
  `date` datetime NOT NULL COMMENT 'date in which the monitoring test was run',
  `had_errors` tinyint(1) NOT NULL COMMENT 'boolean indicator for presence of errors in the monitoring script',
  PRIMARY KEY (`source`,`date`)
)
