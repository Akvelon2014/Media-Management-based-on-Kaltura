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
class kConvertJobData extends kConvartableJobData
{
	const CONVERSION_MILTI_COMMAND_LINE_SEPERATOR = ';';
	const CONVERSION_FAST_START_SIGN = 'FS';


	/**
	 * @var string
	 */
	private $destFileSyncLocalPath;

	/**
	 * @var string
	 */
	private $destFileSyncRemoteUrl;

	/**
	 * @var string
	 */
	private $logFileSyncLocalPath;

	/**
	 * @var string
	 */
	private $logFileSyncRemoteUrl;

	/**
	 * @var string
	 */
	private $flavorAssetId;

	/**
	 * @var string
	 */
	private $remoteMediaId;

	/**
	 * @var string
	 */
	private $destFileSyncWamsAssetId;

	/**
	 * @return the $destFileSyncLocalPath
	 */
	public function getDestFileSyncLocalPath()
	{
		return $this->destFileSyncLocalPath;
	}

	/**
	 * @return the $logFileSyncLocalPath
	 */
	public function getLogFileSyncLocalPath()
	{
		return $this->logFileSyncLocalPath;
	}

	/**
	 * @param $remoteMediaId the $remoteMediaId to set
	 */
	public function setRemoteMediaId($remoteMediaId)
	{
		$this->remoteMediaId = $remoteMediaId;
	}

	/**
	 * @return the $remoteMediaId
	 */
	public function getRemoteMediaId()
	{
		return $this->remoteMediaId;
	}

	/**
	 * @param $destFileSyncRemoteUrl the $destFileSyncRemoteUrl to set
	 */
	public function setDestFileSyncRemoteUrl($destFileSyncRemoteUrl)
	{
		$this->destFileSyncRemoteUrl = $destFileSyncRemoteUrl;
	}

	/**
	 * @param $logFileSyncRemoteUrl the $logFileSyncRemoteUrl to set
	 */
	public function setLogFileSyncRemoteUrl($logFileSyncRemoteUrl)
	{
		$this->logFileSyncRemoteUrl = $logFileSyncRemoteUrl;
	}

	/**
	 * @return the $destFileSyncRemoteUrl
	 */
	public function getDestFileSyncRemoteUrl()
	{
		return $this->destFileSyncRemoteUrl;
	}

	/**
	 * @return the $logFileSyncRemoteUrl
	 */
	public function getLogFileSyncRemoteUrl()
	{
		return $this->logFileSyncRemoteUrl;
	}


	/**
	 * @return the $flavorAssetId
	 */
	public function getFlavorAssetId()
	{
		return $this->flavorAssetId;
	}

	/**
	 * @param $destFileSyncLocalPath the $destFileSyncLocalPath to set
	 */
	public function setDestFileSyncLocalPath($destFileSyncLocalPath)
	{
		$this->destFileSyncLocalPath = $destFileSyncLocalPath;
	}

	/**
	 * @param $logFileSyncLocalPath the $logFileSyncLocalPath to set
	 */
	public function setLogFileSyncLocalPath($logFileSyncLocalPath)
	{
		$this->logFileSyncLocalPath = $logFileSyncLocalPath;
	}

	/**
	 * @param $flavorAssetId the $flavorAssetId to set
	 */
	public function setFlavorAssetId($flavorAssetId)
	{
		$this->flavorAssetId = $flavorAssetId;
	}

	/**
	 * @param $destFileSyncWamsAssetId the $destFileSyncWamsAssetId to set
	 */
	public function setDestFileSyncWamsAssetId($destFileSyncWamsAssetId)
	{
		$this->destFileSyncWamsAssetId = $destFileSyncWamsAssetId;
	}

	/**
	 * @return the $destFileSyncWamsAssetId
	 */
	public function getDestFileSyncWamsAssetId()
	{
		return $this->destFileSyncWamsAssetId;
	}
}
