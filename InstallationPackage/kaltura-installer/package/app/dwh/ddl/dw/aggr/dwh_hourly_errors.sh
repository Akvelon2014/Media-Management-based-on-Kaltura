#---------------------------------------------------------------------------
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
# Modified by Akvelon Inc.
# 2014-06-30
# http://www.akvelon.com/contact-us
#---------------------------------------------------------------------------

USE kalturadw;

DROP TABLE IF EXISTS dwh_hourly_errors;

CREATE TABLE dwh_hourly_errors (
	partner_id INT(11) NOT NULL,
	date_id int NOT NULL,
	hour_id int NOT NULL,
	error_code_id INT(11) NOT NULL,
	count_errors INT(11) NOT NULL,
	PRIMARY KEY (`partner_id`,`date_id`,`hour_id`,`error_code_id`),
	KEY (`date_id`, `hour_id`)
	) ENGINE=INNODB DEFAULT CHARSET=latin1
	/*!50100 PARTITION BY RANGE (date_id)
	(PARTITION p_20131231 VALUES LESS THAN (20140101) ENGINE = INNODB)*/;
