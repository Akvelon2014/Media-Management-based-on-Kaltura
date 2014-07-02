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
 * Uses for extracting information from Azure asset file (dimensions, bitrate, duration etc.)
 * @package infra
 * @subpackage Media
 */
class KWAMSMediaInfoParser extends KBaseMediaParser {
	const MAX_BLOCK_SIZE = 2097152; // 2 * 1024 * 1024

	private $wamsAssetId = null;
	private $mediaInfoParser = null;
	private $originalMediaInfo = null;
	private $mediaType = null;
	private $partnerId = null;

	/**
	 * Creates instance of class and initializes properties
	 * @param string $type
	 * @param string $filePath
	 * @param KSchedularTaskConfig $taskConfig
	 */
	public function __construct($type, $filePath, KSchedularTaskConfig $taskConfig, KalturaBatchJob $job, $wamsAssetId) {
		$this->wamsAssetId = $wamsAssetId;
		$this->filePath = $filePath;
		$this->mediaInfoParser = parent::getParser($type, $filePath, $taskConfig, $job);
		$this->partnerId = $job->partnerId;
		DbManager::setConfig(kConf::getDB());
		DbManager::initialize();
		$fileSync = FileSyncPeer::retrieveByWamsAssetId($this->wamsAssetId);
		if ($fileSync) {
			$flavorAsset = kFileSyncUtils::retrieveObjectForFileSync($fileSync);
			if ($flavorAsset instanceof asset) {
				$this->originalMediaInfo = mediaInfoPeer::retrieveOriginalByEntryId($flavorAsset->getEntryId());

				$entry = $flavorAsset->getentry();
				if ($entry) {
					$this->mediaType = $entry->getMediaType();
				}
			}
		}
	}

	private function validateMediaInfo(KalturaMediaInfo $mediaInfo) {
		if (empty($mediaInfo)) {
			return false;
		}

		if ($this->mediaType == entry::ENTRY_MEDIA_TYPE_VIDEO) {
			if (empty($mediaInfo->videoWidth) ||
				empty($mediaInfo->videoHeight)) {
				return false;
			}
		}

		if ($this->originalMediaInfo) {
			if ( ($mediaInfo->videoDuration < $this->originalMediaInfo->getVideoDuration()) ||
					($mediaInfo->audioDuration < $this->originalMediaInfo->getAudioDuration()) ||
					($mediaInfo->containerDuration < $this->originalMediaInfo->getContainerDuration()) ) {
				return false;
			}
		}

		return true;
	}

	private function appendBlock($blockContent) {
		$fp = fopen($this->filePath, 'ab');
		fwrite($fp, $blockContent);
		fclose($fp);
	}

	/**
	 * Extract media information from remote file
	 * @return KalturaMediaInfo
	 */
	public function getMediaInfo() {
		$mediaFileExt =  pathinfo($this->filePath, PATHINFO_EXTENSION);
		$kWAMS = kWAMS::getInstance($this->partnerId);

		$wamsFileSize = $kWAMS->getFileSizeForAssetId($this->wamsAssetId, $mediaFileExt);

		$mediaInfo = $this->mediaInfoParser->getMediaInfo();
		if ($this->validateMediaInfo($mediaInfo)) {
			$mediaInfo->fileSize = $wamsFileSize / 1024; // size in KB
			return $mediaInfo;
		}

		$localFileSize = filesize($this->filePath);
		$fp = fopen($kWAMS->getUrlForAssetId($this->wamsAssetId, $mediaFileExt), 'rb');
		if ($localFileSize > 0) {
			stream_get_contents($fp, $localFileSize);
		}
		while (!$this->validateMediaInfo($mediaInfo) && ($localFileSize < $wamsFileSize)) {
			$blockContent = stream_get_contents($fp, self::MAX_BLOCK_SIZE);
			$this->appendBlock($blockContent);
			clearstatcache();
			$localFileSize = filesize($this->filePath);
			$mediaInfo = $this->mediaInfoParser->getMediaInfo();
		}
		fclose($fp);

		if (!empty($mediaInfo)) {
			$mediaInfo->fileSize = $wamsFileSize / 1024; // size in KB
		}

		return $mediaInfo;
	}

	/**
	 * @return string
	 */
	protected function getCommand() {
		return '';
	}

	/**
	 *
	 * @param string $output
	 * @return KalturaMediaInfo
	 */
	protected function parseOutput($output) {
		return new KalturaMediaInfo();
	}
} 