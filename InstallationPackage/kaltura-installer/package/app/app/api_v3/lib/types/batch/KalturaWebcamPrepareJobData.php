<?php
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

/**
 * Data for webcam prepare batch job
 *
 * @package api
 * @subpackage objects
 */
class KalturaWebcamPrepareJobData extends KalturaJobData
{
	/**
	 * Webcam token ID
	 *
	 * @var string
	 */
	public $webcamTokenId = null;

	private static $map_between_objects = array
	(
		"webcamTokenId" ,
	);

	/**
	 * Mapping objects
	 *
	 * @return array
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	/**
	 * Transform data to object
	 *
	 * @param $dbData
	 * @param array $props_to_skip
	 * @return KalturaObject|null Object or error
	 */
	public function toObject($dbData = null, $props_to_skip = array())
	{
		if(is_null($dbData))
			$dbData = new kWebcamPrepareJobData();

		return parent::toObject($dbData, $props_to_skip);
	}
}