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

DROP TABLE IF EXISTS kalturadw.`dwh_hourly_user_usage`;

CREATE TABLE kalturadw.`dwh_hourly_user_usage` (
  `partner_id` INT(11) NOT NULL,
  `kuser_id` INT(11) NOT NULL,
  `date_id` INT(11) NOT NULL,
  `hour_id` INT(11) NOT NULL,
  `added_storage_kb`  DECIMAL(19,4) DEFAULT 0.0000,
  `deleted_storage_kb`  DECIMAL(19,4) DEFAULT 0.0000,
  `total_storage_kb` DECIMAL(19,4) ,
  `added_entries`  INT(11) DEFAULT 0,
  `deleted_entries`  INT(11) DEFAULT 0,
  `total_entries` INT(11) ,
  `added_msecs`  INT(11) DEFAULT 0,
  `deleted_msecs`  INT(11) DEFAULT 0,
  `total_msecs` INT(11) ,
  PRIMARY KEY (`partner_id`, `kuser_id`,`date_id`, `hour_id`),
  KEY (`date_id`, `hour_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
PARTITION BY RANGE (date_id)
(PARTITION p_20131231 VALUES LESS THAN (20140101) ENGINE = INNODB);
 
 CALL kalturadw.add_monthly_partition_for_table('dwh_hourly_user_usage');
