
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `kalturadw_ds`;

/*Table structure for table `aggr_name_resolver` */

CREATE TABLE `aggr_name_resolver` (
  `aggr_name` varchar(100) NOT NULL DEFAULT '',
  `aggr_table` varchar(100) DEFAULT NULL,
  `aggr_id_field` varchar(1024) NOT NULL DEFAULT '',
  `dim_id_field` varchar(1024) NOT NULL DEFAULT '',
  `aggr_type` VARCHAR(60) NOT NULL,
  `join_table` VARCHAR(60) NOT NULL DEFAULT '',
  `join_id_field` VARCHAR(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`aggr_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `aggr_name_resolver` */

insert  into `aggr_name_resolver`(`aggr_name`,`aggr_table`,`aggr_id_field`,`dim_id_field`,`aggr_type`,`join_table`,`join_id_field`) 
values  ('entry','dwh_hourly_events_entry','','entry_id','events','',''),
        ('domain','dwh_hourly_events_domain','domain_id','','events','',''),
        ('country','dwh_hourly_events_country','country_id,location_id','','events','',''),
        ('partner','dwh_hourly_partner','','','events','',''),
        ('widget','dwh_hourly_events_widget','widget_id','','events','',''),
        ('uid','dwh_hourly_events_uid','','kuser_id','events','',''),
		('domain_referrer', 'dwh_hourly_events_domain_referrer', 'domain_id, referrer_id', '','events','',''),
		('devices', 'dwh_hourly_events_devices', 'country_id,location_id,os_id,browser_id,ui_conf_id','entry_media_type_id','events','',''),
		('bandwidth_usage', 'dwh_hourly_partner_usage', 'bandwidth_source_id', '', 'bandwidth','',''),
		('devices_bandwidth_usage', 'dwh_hourly_events_devices', 'country_id, location_id', '', 'bandwidth','',''),
		('api_calls','dwh_hourly_api_calls','action_id', '', 'api','',''),
		('errors','dwh_hourly_errors','error_code_id','','errors','',''),
		('users', 'dwh_hourly_events_context_entry_user_app', 'user_id,context_id,application_id', 'entry_id', 'events', 'dwh_dim_user_reports_allowed_partners', 'partner_id'),
		('context', 'dwh_hourly_events_context_app', 'context_id,application_id', '', 'events', 'dwh_dim_user_reports_allowed_partners', 'partner_id');
		
