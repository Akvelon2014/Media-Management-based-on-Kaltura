/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Modified by Akvelon Inc.
* 2014-06-30
* http://www.akvelon.com/contact-us
*/

/*
SQLyog Community v8.3 
MySQL - 5.1.41-3ubuntu12.6 : Database - kalturadw
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

USE `kalturadw`;

/*Table structure for table `dwh_fact_storage_usage` */

DROP TABLE IF EXISTS `dwh_fact_entries_sizes`;

CREATE TABLE `dwh_fact_entries_sizes` (
`partner_id` INT(11) NOT NULL,
`entry_id` VARCHAR(20) NOT NULL,
`entry_size_date` DATETIME NOT NULL,
`entry_size_date_id` INT(11) NOT NULL,
`entry_additional_size_kb` DECIMAL(15,3) NOT NULL,
PRIMARY KEY (`partner_id`, `entry_id`, `entry_size_date_id`),
KEY entry_id (`entry_id`))
ENGINE=MYISAM DEFAULT CHARSET=utf8
PARTITION BY RANGE (entry_size_date_id)
(PARTITION p_20131231 VALUES LESS THAN (20140101));


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

