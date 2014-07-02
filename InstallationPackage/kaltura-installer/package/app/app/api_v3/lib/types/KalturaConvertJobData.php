<?php
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

/**
 * @package api
 * @subpackage objects
 */
class KalturaConvertJobData extends KalturaConvartableJobData
{
	/**
	 * @var string
	 */
	public $destFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	public $destFileSyncRemoteUrl;
	
	/**
	 * @var string
	 */
	public $logFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	 public $logFileSyncRemoteUrl;
	
	/**
	 * @var string
	 */
	public $flavorAssetId;
	
	
	/**
	 * @var string
	 */
	public $remoteMediaId;
    
	/**
	 * @var string
	 */
	public $customData;

	/**
	 * @var string
	 */
	public $destFileSyncWamsAssetId;

	private static $map_between_objects = array
	(
		"destFileSyncLocalPath" ,
		"destFileSyncRemoteUrl" ,
		"logFileSyncLocalPath" ,
		"logFileSyncRemoteUrl" ,
		"flavorAssetId" ,
		"remoteMediaId" ,
		"customData" ,
		"destFileSyncWamsAssetId",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new kConvertJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
	
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		return kPluginableEnumsManager::apiToCore('conversionEngineType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		return kPluginableEnumsManager::coreToApi('conversionEngineType', $subType);
	}
}
