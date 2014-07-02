/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Copyright 2014 Akvelon Inc.
* http://www.akvelon.com/contact-us
*/

ALTER TABLE `file_sync`
	ADD COLUMN `wams_asset_id` VARCHAR(255) NULL DEFAULT NULL AFTER `custom_data`,
	ADD COLUMN `wams_url` TEXT NULL DEFAULT NULL AFTER `wams_asset_id`;

commit;