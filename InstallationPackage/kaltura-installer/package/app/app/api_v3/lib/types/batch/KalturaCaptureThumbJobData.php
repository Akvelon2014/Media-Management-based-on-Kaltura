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
class KalturaCaptureThumbJobData extends KalturaJobData
{
	/**
	 * @var string
	 */
	public $srcFileSyncLocalPath;
	
	/**
	 * The translated path as used by the scheduler
	 * @var string
	 */
	public $actualSrcFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	public $srcFileSyncRemoteUrl;
	
	/**
	 * @var int
	 */
	public $thumbParamsOutputId;
	
	/**
	 * @var string
	 */
	public $thumbAssetId;
	
	/**
	 * @var string
	 */
	public $srcAssetId;
	
	/**
	 * @var KalturaAssetType
	 */
	public $srcAssetType;
	
	/**
	 * @var string
	 */
	public $thumbPath;

	/**
	 * @var string
	 */
	public $srcWamsAssetId;

	private static $map_between_objects = array
	(
		"srcFileSyncLocalPath" ,
		"actualSrcFileSyncLocalPath" ,
		"srcFileSyncRemoteUrl" ,
		"thumbParamsOutputId" ,
		"thumbAssetId" ,
		"srcAssetId" ,
		"srcAssetType" ,
		"thumbPath" ,
		"srcWamsAssetId",
	);


	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	    
	public function toObject(  $dbCaptureThumbJobData = null, $props_to_skip = array()) 
	{
		if(is_null($dbCaptureThumbJobData))
			$dbCaptureThumbJobData = new kCaptureThumbJobData();
			
		return parent::toObject($dbCaptureThumbJobData, $props_to_skip);
	}
}
