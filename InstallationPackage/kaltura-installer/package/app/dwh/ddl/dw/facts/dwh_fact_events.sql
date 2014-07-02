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

CREATE TABLE kalturadw.dwh_fact_events
     (
	  file_id INT NOT NULL
	, event_id INT  NOT NULL
	, event_type_id SMALLINT  NOT NULL
	, client_version VARCHAR(31)
	, event_time DATETIME
	, event_date_id INT
	, event_hour_id TINYINT
	, session_id VARCHAR(50)
	, partner_id INT
	, entry_id VARCHAR(20)
	, unique_viewer VARCHAR(40)
	, widget_id VARCHAR(31)
	, ui_conf_id INT
	, uid VARCHAR(64)
	, current_point INT
	, duration INT
	, user_ip VARCHAR(15)
	, user_ip_number INT UNSIGNED
	, country_id INT
	, location_id INT
	, process_duration INT
	, control_id VARCHAR(15)
	, seek INT
	, new_point INT
	, domain_id INT
	, entry_media_type_id INT
	, entry_partner_id INT
	, referrer_id INT(11)
	, os_id INT(11)
	, browser_id INT(11)
	, context_id INT(11) DEFAULT NULL
	, user_id INT(11) DEFAULT NULL
	,application_id INT(11) DEFAULT NULL
	,PRIMARY KEY (file_id,event_id,event_date_id)
	,KEY Entry_id (Entry_id)
	,KEY `event_hour_id_event_date_id_partner_id` (event_hour_id, event_date_id, partner_id)
     ) ENGINE=INNODB  DEFAULT CHARSET=utf8  
/*!50100 PARTITION BY RANGE (event_date_id)
(PARTITION p_20131231 VALUES LESS THAN (20140101) ENGINE = INNODB) */;