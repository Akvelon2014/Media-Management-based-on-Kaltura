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

USE `kalturadw`;

DROP TABLE IF EXISTS `dwh_fact_fms_sessions`;

CREATE TABLE `dwh_fact_fms_sessions` (
  `session_id` VARCHAR(20) NOT NULL,
  `session_time` DATETIME NOT NULL,
  `session_date_id` INT(11) UNSIGNED DEFAULT NULL,
  `bandwidth_source_id` INT(11) NOT NULL,
  `session_client_ip` VARCHAR(15),
  `session_client_ip_number` INT(10) UNSIGNED,
  `country_id` INT(10) UNSIGNED,
  `location_id` INT(10) UNSIGNED,
  `session_partner_id` INT(10) UNSIGNED DEFAULT NULL,
  `total_bytes` BIGINT(20) UNSIGNED DEFAULT NULL,
  `entry_id` varchar(20) DEFAULT NULL,
  KEY `session_partner_id` (`session_partner_id`),
  UNIQUE KEY `session_id` (`session_id`,`session_date_id`)
) ENGINE=INNODB DEFAULT CHARSET=latin1
/*!50100 PARTITION BY RANGE (session_date_id)
(PARTITION p_20131231 VALUES LESS THAN (20140101) ENGINE = INNODB) */;
