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
 * @package Core
 * @subpackage model.data
 */
class kCaptureThumbJobData extends kJobData
{
	/**
	 * @var string
	 */
	private $srcFileSyncLocalPath;
	
	/**
	 * The translated path as used by the scheduler
	 * @var string
	 */
	private $actualSrcFileSyncLocalPath;
	
	/**
	 * @var string
	 */
	private $srcFileSyncRemoteUrl;
	
	/**
	 * @var int
	 */
	private $thumbParamsOutputId;
	
	/**
	 * @var thumbParamsOutput
	 * @deprecated
	 */
	private $thumbParamsOutput;
	
	/**
	 * @var string
	 */
	private $thumbAssetId;
	
	/**
	 * @var assetType
	 */
	private $srcAssetType;
	
	/**
	 * @var string
	 */
	private $srcAssetId;
	
	/**
	 * @var string
	 */
	private $thumbPath;

	/**
	 * @var string
	 */
	private $srcWamsAssetId;
	
	/**
	 * @return the $thumbPath
	 */
	public function getThumbPath()
	{
		return $this->thumbPath;
	}

	/**
	 * @param $thumbPath the $thumbPath to set
	 */
	public function setThumbPath($thumbPath)
	{
		$this->thumbPath = $thumbPath;
	}

	/**
	 * @return the $srcAssetId
	 */
	public function getSrcAssetId()
	{
		return $this->srcAssetId;
	}

	/**
	 * @param $srcAssetId the $srcAssetId to set
	 */
	public function setSrcAssetId($srcAssetId)
	{
		$this->srcAssetId = $srcAssetId;
	}

	/**
	 * @return the $srcAssetType
	 */
	public function getSrcAssetType()
	{
		return $this->srcAssetType;
	}

	/**
	 * @param $srcAssetType the $srcAssetType to set
	 */
	public function setSrcAssetType($srcAssetType)
	{
		$this->srcAssetType = $srcAssetType;
	}

	/**
	 * @return the $thumbAssetId
	 */
	public function getThumbAssetId()
	{
		return $this->thumbAssetId;
	}

	/**
	 * @param $thumbAssetId the $thumbAssetId to set
	 */
	public function setThumbAssetId($thumbAssetId)
	{
		$this->thumbAssetId = $thumbAssetId;
	}

	/**
	 * @return the $srcFileSyncLocalPath
	 */
	public function getSrcFileSyncLocalPath()
	{
		return $this->srcFileSyncLocalPath;
	}
	
	/**
	 * @param $srcFileSyncRemoteUrl the $srcFileSyncRemoteUrl to set
	 */
	public function setSrcFileSyncRemoteUrl($srcFileSyncRemoteUrl)
	{
		$this->srcFileSyncRemoteUrl = $srcFileSyncRemoteUrl;
	}

	/**
	 * @return the $srcFileSyncRemoteUrl
	 */
	public function getSrcFileSyncRemoteUrl()
	{
		return $this->srcFileSyncRemoteUrl;
	}

	/**
	 * @param $thumbParamsOutput the $thumbParamsOutput to set
	 */
	public function setThumbParamsOutput($thumbParamsOutput)
	{
// 		$this->thumbParamsOutput = $thumbParamsOutput;
	}

	/**
	 * @param $srcFileSyncLocalPath the $srcFileSyncLocalPath to set
	 */
	public function setSrcFileSyncLocalPath($srcFileSyncLocalPath)
	{
		$this->srcFileSyncLocalPath = $srcFileSyncLocalPath;
	}

	/**
	 * @return the $actualSrcFileSyncLocalPath
	 */
	public function getActualSrcFileSyncLocalPath()
	{
		return $this->actualSrcFileSyncLocalPath;
	}

	/**
	 * @param $actualSrcFileSyncLocalPath the $actualSrcFileSyncLocalPath to set
	 */
	public function setActualSrcFileSyncLocalPath($actualSrcFileSyncLocalPath)
	{
		$this->actualSrcFileSyncLocalPath = $actualSrcFileSyncLocalPath;
	}
	
	/**
	 * @param $thumbParamsOutputId the $thumbParamsOutputId to set
	 */
	public function setThumbParamsOutputId($thumbParamsOutputId)
	{
		$this->thumbParamsOutputId = $thumbParamsOutputId;
	}

	/**
	 * @return thumbParamsOutput the $thumbParamsOutput
	 */
	public function getThumbParamsOutput()
	{
		return $this->thumbParamsOutput;
		
		if (is_null($this->thumbParamsOutputId))
			return null;
			
		return assetParamsOutputPeer::retrieveByPK($this->thumbParamsOutputId);
	}

	/**
	 * @return int the $thumbParamsOutputId
	 */
	public function getThumbParamsOutputId()
	{
		return $this->thumbParamsOutputId;
	}

	/**
	 * @return the $srcWamsAssetId
	 */
	public function getSrcWamsAssetId()
	{
		return $this->srcWamsAssetId;
	}

	/**
	 * @param $srcWamsAssetId
	 */
	public function setSrcWamsAssetId($srcWamsAssetId)
	{
		$this->srcWamsAssetId = $srcWamsAssetId;
	}

	/**
	 * @return the ready behavior
	 */
	public function getReadyBehavior()
	{
		$thumbParamsOutput = assetParamsOutputPeer::retrieveByPK($this->thumbParamsOutputId);
		if($thumbParamsOutput)
			return $thumbParamsOutput->getReadyBehavior();
			
		return null;
	}
}
