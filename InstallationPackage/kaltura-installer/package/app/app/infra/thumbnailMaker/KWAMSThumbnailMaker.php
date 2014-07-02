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
 * @package infra
 * @subpackage Media
 */
class KWAMSThumbnailMaker
{
	protected $srcWAMSAssetId;
	protected $targetPath;
	protected $partnerId = null;

	/**
	 * @param string $srcWAMSAssetId
	 * @param string $targetPath
	 */
	public function __construct($srcWAMSAssetId, $targetPath)
	{
		KalturaLog::debug("Creation instance of KWAMSThumbnailMaker srcWAMSAssetId = [$srcWAMSAssetId] targetPath = [$targetPath]");
		$this->srcWAMSAssetId = $srcWAMSAssetId;
		$this->targetPath = $targetPath;
		DbManager::setConfig(kConf::getDB());
		DbManager::initialize();
		$fileSync = FileSyncPeer::retrieveByWamsAssetId($srcWAMSAssetId);
		if (!empty($fileSync)) {
			$this->partnerId = $fileSync->getPartnerId();
		}
	}

	public function createThumbnail($position, $width, $height, $dar = null)
	{
		KalturaLog::debug("Creation thumbnail via WAMS position = [$position], width = [$width], height = [$height], dar = [$dar]");

		if (isset($dar) && $dar > 0 && isset($height)) {
			$width = floor(round($height * $dar) / 2) * 2;
		}

		$thumbFormat = 'jpg';
		$kWAMS = kWAMS::getInstance($this->partnerId);
		$thumbAssetId = $kWAMS->createThumbnail($this->srcWAMSAssetId, $position, $width, $height, $thumbFormat);
		if (!$thumbAssetId) {
			return false;
		}
		else {
			$thumbURL = $kWAMS->getUrlForAssetId($thumbAssetId, $thumbFormat);
			// download
			KalturaLog::debug("Downloading thumbnail url = [$thumbURL]");
			file_put_contents($this->targetPath, fopen($thumbURL, 'rb'));
			$kWAMS->deleteAssetById($thumbAssetId);
			return true;
		}
	}
}