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

USE kalturadw;

DROP TABLE IF EXISTS `dwh_hourly_api_calls`;
CREATE TABLE `dwh_hourly_api_calls` (
  `date_id` int(11) NOT NULL DEFAULT '0',
  `hour_id` tinyint(4) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `action_id` int(11) NOT NULL DEFAULT '0',
  `count_calls` decimal(22,0) DEFAULT NULL,
  `count_success` decimal(23,0) DEFAULT NULL,
  `count_is_in_multi_request` decimal(23,0) DEFAULT NULL,
  `count_is_admin` decimal(14,4) DEFAULT NULL,
  `sum_duration_msecs` decimal(32,0) DEFAULT NULL,
  PRIMARY KEY (`partner_id`,`date_id`,`hour_id`, `action_id`),
  KEY (`date_id`,`hour_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
/*!50100 PARTITION BY RANGE (date_id)
(PARTITION p_20131231 VALUES LESS THAN (20140101) ENGINE = INNODB) */
