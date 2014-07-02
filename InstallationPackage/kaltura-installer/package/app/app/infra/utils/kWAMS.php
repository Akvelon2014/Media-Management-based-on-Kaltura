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

require_once(realpath(dirName(__FILE__).'/../bootstrap_base.php'));

require_once(KALTURA_ROOT_PATH . '/api_v3/lib/KalturaErrors.php');
require_once(KALTURA_ROOT_PATH . '/vendor/autoload.php');

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\Internal\MediaServicesSettings;
use WindowsAzure\MediaServices\Models\Asset;
use WindowsAzure\MediaServices\Models\AccessPolicy;
use WindowsAzure\MediaServices\Models\Locator;
use WindowsAzure\MediaServices\Models\Task;
use WindowsAzure\MediaServices\Models\Job;
use WindowsAzure\Common\Internal\Http\Url;
use WindowsAzure\Common\Internal\Resources;

/**
 * Provides interface for access to Microsoft Azure Media Services
 * @package infra
 * @subpackage utils
 */
class kWAMS
{
	private static $_instance = null;
	private $_mediaServiceProxy = null;

	private $audioCodecs = array('aac' => 'AAC');
	private $videoProfiles = array('h264m' => 'MainH264VideoProfile', 'h264h' => 'HighH264VideoProfile');

	private $confPath = '';

	const SAMPLE_SIZE_KB = 256;
	const CHUNK_SIZE = 102400; // 1024 * 100

	const ACCESS_POLICY_READ_DURATION_M = 1051200;
	const ACCESS_POLICY_WRITE_DURATION_SEC = 36000; // 10 hours

	const BLOCK_ID_PREFIX = 'block-';
	const MAX_BLOCK_SIZE = 4194304; // 4 * 1024 * 1024
	const BLOCK_ID_PADDING = 6;

	const UPLOAD_TIME_LIMIT = 36000;

	const CONF_PARAM_AUDIO_CODEC = 'AUDIO_CODEC';
	const CONF_PARAM_AUDIO_CHANNELS = 'AUDIO_CHANNELS';
	const CONF_PARAM_AUDIO_SAMPLE_RATE = 'AUDIO_SAMPLE_RATE';
	const CONF_PARAM_AUDIO_BITRATE = 'AUDIO_BITRATE';
	const CONF_PARAM_VIDEO_PROFILE_NAME = 'VIDEO_PROFILE_NAME';
	const CONF_PARAM_VIDEO_WIDTH = 'VIDEO_WIDTH';
	const CONF_PARAM_VIDEO_HEIGHT = 'VIDEO_HEIGHT';
	const CONF_PARAM_VIDEO_BITRATE = 'VIDEO_BITRATE';
	const CONF_PARAM_ASSET_NAME = 'ASSET_NAME';
	const CONF_PARAM_THUMB_WIDTH = 'THUMB_WIDTH';
	const CONF_PARAM_THUMB_HEIGHT = 'THUMB_HEIGHT';
	const CONF_PARAM_THUMB_TYPE = 'THUMB_TYPE';
	const CONF_PARAM_THUMB_POSITION_TIME = 'THUMB_POSITION_TIME';
	const CONF_PARAM_COMMIT_BLOCKS = 'BLOCKS';

	const TEMPLATE_FILE_CONVERT = 'convert_template.xml';
	const TEMPLATE_FILE_TASK_BODY = 'task_body_template.xml';
	const TEMPLATE_FILE_THUMBNAIL = 'thumbnail_template.xml';
	const TEMPLATE_FILE_COMMIT = 'commit_template.xml';

	const CONVERT_PARAM_PRESET_NAME = 'presetName';
	const CONVERT_PARAM_AUDIO_CODEC = 'audioCodec';
	const CONVERT_PARAM_AUDIO_BITRATE = 'audioBitrate';
	const CONVERT_PARAM_AUDIO_CHANNELS = 'audioChannels';
	const CONVERT_PARAM_AUDIO_SAMPLE_RATE = 'audioSampleRate';
	const CONVERT_PARAM_VIDEO_CODEC = 'videoCodec';
	const CONVERT_PARAM_VIDEO_WIDTH = 'videoWidth';
	const CONVERT_PARAM_VIDEO_HEIGHT = 'videoHeight';
	const CONVERT_PARAM_VIDEO_BITRATE = 'videoBitrate';

	const MEDIA_PROCESSOR_NAME = 'Windows Azure Media Encoder';

	const VALID_HTTP_CODE = 200;
	const MAX_CHECK_URL_TIMEOUT = 40;

	private function __construct($partnerId)
	{
		DbManager::setConfig(kConf::getDB());
		DbManager::initialize();

		$this->confPath = KALTURA_ROOT_PATH . '/configurations/wams/';

		$partner = PartnerPeer::retrieveByPK($partnerId);

		$wams_account_name = $partner->getWamsAccountName();
		$wams_access_key = $partner->getWamsAccountKey();

		self::testConnection($wams_account_name, $wams_access_key);

		$this->_mediaServiceProxy = ServicesBuilder::getInstance()->createMediaServicesService(
			new MediaServicesSettings(
				$wams_account_name,
				$wams_access_key
			));
	}

	/**
	 * Uses for getting instance of singleton
	 * @return kWAMS
	 */
	public static function getInstance($partnerId)
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new kWAMS($partnerId);
		}

		return self::$_instance;
	}

	private static function handleException(Exception $exception) {
		$errorString = KalturaErrors::INTERNAL_SERVERL_ERROR;
		if ($exception instanceof WindowsAzure\Common\ServiceException) {
			$reasons =  json_decode($exception->getErrorReason(), true);
			switch ($reasons['error']) {
				case 'invalid_client' :
					if (strpos($reasons['error_description'], 'Authentication failed')) {
						$errorString = KalturaErrors::WAMS_CREDENTIALS_ERROR;
					}
					break;
			}
		}
		throw new KalturaAPIException($errorString);
	}

	/**
	 * Testing connection with Microsoft Azure Media Services
	 * @param string $wams_account_name Media Service Account Name
	 * @param string $wams_access_key Primary Media Service access key
	 * @throws KalturaAPIException If Microsoft Azure credentials not defined
	 */
	public static function testConnection($wams_account_name, $wams_access_key) {
		if (empty($wams_account_name) || empty($wams_access_key)) {
			throw new KalturaAPIException(KalturaErrors::WAMS_CREDENTIALS_REQUIRED);
		}

		$mediaServiceProxy = ServicesBuilder::getInstance()->createMediaServicesService(
			new MediaServicesSettings(
				$wams_account_name,
				$wams_access_key
			));

		try {
			$mediaServiceProxy->getAssetList();
		}
		catch (Exception $e) {
			self::handleException($e);
		}
	}

	private function renderConfiguration($templatePath, $params) {
		$config = file_get_contents($templatePath);
		foreach ($params as $paramName => $paramValue) {
			$config = str_replace('{' . $paramName . '}', $paramValue, $config);
		}

		return $config;
	}

	private function getAssetById($assetId)
	{
		$assetList = $this->_mediaServiceProxy->getAssetList();
		foreach ($assetList as $asset) {
			if ($asset->getId() === $assetId) {
				return $asset;
			}
		}
		return null;
	}

	private function addAsset($assetName)
	{
		$assetOptions = Asset::OPTIONS_NONE;

		$asset = new Asset($assetOptions);
		$asset->setName($assetName);
		$asset = $this->_mediaServiceProxy->createAsset($asset);

		return $asset;
	}

	private function uploadBlock($baseUrl, $blockId, $blockSize, $blockContent) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $baseUrl . '&comp=block&blockid=' . $blockId);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $blockContent);

		$headers = array(Resources::CONTENT_TYPE . ': ' . Resources::BINARY_FILE_TYPE,
			Resources::CONTENT_LENGTH . ': ' . $blockSize,
			Resources::X_MS_VERSION . ': ' . Resources::STORAGE_API_LATEST_VERSION,
			Resources::X_MS_BLOB_TYPE . ': BlockBlob',
		);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_exec($ch);
	}

	private function commitBlocks($baseUrl, $blockIds) {

		$blocks = '';
		foreach ($blockIds as $blockId) {
			$blocks .= '<Latest>' . $blockId . '</Latest>';
		}

		$params = array();
		$params[self::CONF_PARAM_COMMIT_BLOCKS] = $blocks;
		$requestBody = $this->renderConfiguration($this->confPath . self::TEMPLATE_FILE_COMMIT, $params);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $baseUrl . '&comp=blocklist');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);

		$headers = array(Resources::CONTENT_LENGTH . ': ' . strlen($requestBody),
			Resources::X_MS_VERSION . ': ' . Resources::STORAGE_API_LATEST_VERSION,
			Resources::X_MS_BLOB_CONTENT_TYPE . ': ' . Resources::BINARY_FILE_TYPE,
		);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_exec($ch);
	}

	private function uploadFileToLocator ($fileName, $filePath, $locator) {
		set_time_limit(self::UPLOAD_TIME_LIMIT);

		$blockSize = self::MAX_BLOCK_SIZE;
		$blockIds = array();

		$urlFile = $locator->getBaseUri() . '/' . $fileName;
		$baseUrl = new Url($urlFile . $locator->getContentAccessComponent());

		$fileSize = filesize($filePath);
		if ($fileSize < $blockSize) {
			$blockSize = $fileSize;
		}
		$totalBytesRemaining = $fileSize;

		$fh = fopen($filePath, 'rb');
		while ($totalBytesRemaining > 0) {

			$blockContent = fread($fh, $blockSize);

			$blockId = base64_encode (self::BLOCK_ID_PREFIX . str_pad(count($blockIds), self::BLOCK_ID_PADDING, '0', STR_PAD_LEFT));
			$blockIds[] = $blockId;
			$this->uploadBlock($baseUrl, $blockId, $blockSize, $blockContent);

			$totalBytesRemaining -= $blockSize;
			if ($totalBytesRemaining < $blockSize) {
				$blockSize = $totalBytesRemaining;
			}
		}

		fclose($fh);

		$this->commitBlocks($baseUrl, $blockIds);
	}

	private function uploadFileToAsset($asset, $fileName, $filePath)
	{
		if (is_null($asset)) {
			return false;
		}

		$accessPolicyName = 'upload policy';
		$accessPolicyDuration = self::ACCESS_POLICY_WRITE_DURATION_SEC;
		$accessPolicyPermission = AccessPolicy::PERMISSIONS_WRITE;

		$locatorStartTime = new \DateTime('now -5 minutes');
		$locatorType = Locator::TYPE_SAS;

		$accessPolicy = new AccessPolicy($accessPolicyName);
		$accessPolicy->setDurationInMinutes($accessPolicyDuration);
		$accessPolicy->setPermissions($accessPolicyPermission);
		$accessPolicy = $this->_mediaServiceProxy->createAccessPolicy($accessPolicy);

		$locator = new Locator($asset, $accessPolicy, $locatorType);
		$locator->setStartTime($locatorStartTime);
		$locator = $this->_mediaServiceProxy->createLocator($locator);

		$this->uploadFileToLocator($fileName, $filePath, $locator);

		$this->_mediaServiceProxy->createFileInfos($asset);

		$this->_mediaServiceProxy->deleteLocator($locator);
		$this->_mediaServiceProxy->deleteAccessPolicy($accessPolicy);

		return true;
	}

	private function publishAsset($asset)
	{
		$accessPolicy = new AccessPolicy("read access policy");
		$accessPolicy->setDurationInMinutes(self::ACCESS_POLICY_READ_DURATION_M);
		$accessPolicy->setPermissions(AccessPolicy::PERMISSIONS_READ);
		$accessPolicy = $this->_mediaServiceProxy->createAccessPolicy($accessPolicy);

		$locator = new Locator($asset, $accessPolicy, Locator::TYPE_SAS);
		$locator->setStartTime(new \DateTime('now -5 minutes'));
		$locator = $this->_mediaServiceProxy->createLocator($locator);

		return $locator;
	}

	private function getAssetFiles($asset, $fileExt = null)
	{
		$assetFileList = $this->_mediaServiceProxy->getAssetAssetFileList($asset);
		if (is_null($fileExt)) {
			return $assetFileList;
		}
		else {
			$result = array();
			$regExp = '/.' . $fileExt . '$/';
			foreach ($assetFileList as $assetFile) {
				$fileName = $assetFile->getName();
				if (preg_match($regExp, $fileName)) {
					$result[] = $assetFile;
				}
			}
			return $result;
		}
	}

	private function clearAssetList ($assetList)
	{
		foreach ($assetList as $asset) {
			$this->_mediaServiceProxy->deleteAsset($asset);
		}
	}

	private function getTimeBySeconds($seconds)
	{
		return sprintf("%02d:%02d:%02d", floor($seconds/3600), ($seconds/60)%60, $seconds%60);
	}

	private function getEntryName($assetId) {
		$fileSync = FileSyncPeer::retrieveByWamsAssetId($assetId);
		if (!$fileSync) {
			return null;
		}

		$asset = kFileSyncUtils::retrieveObjectForFileSync($fileSync);
		if (!$asset) {
			return null;
		}

		$entry = $asset->getentry();
		if (!$entry) {
			return null;
		}

		$replacedId = $entry->getReplacedEntryId();
		if (!empty($replacedId)) {
			$entry = entryPeer::retrieveByPK($replacedId);
		}

		return $entry->getName();
	}

	private function getHttpCode ($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $httpCode;
	}

	private function checkURL ($url) {
		$start = microtime(true);
		$timeout = false;

		while (($this->getHttpCode($url) !== self::VALID_HTTP_CODE) && (!$timeout)) {
			sleep(5);
			$timeout = ((microtime(true) - $start) > self::MAX_CHECK_URL_TIMEOUT);
		}

		return !$timeout;
	}

	/**
	 * Publishing local file to Microsoft Azure Media Services
	 * @param string $assetName Name for new asset
	 * @param string $filePath Path for local file
	 * @return int|false Created asset id or error
	 */
	public function publishFileToWAMS($assetName, $filePath)
	{
		$asset = $this->addAsset($assetName);

		$fileName = pathinfo($filePath, PATHINFO_BASENAME);

		if ($this->uploadFileToAsset($asset, $fileName, $filePath)) {
			$locator = $this->publishAsset($asset);
			if ($locator instanceof Locator) {
				return $asset->getId();
			}
			else {
				KalturaLog::err("Error creation locator for [$assetName]");
				return false;
			}
		}
		else {
			KalturaLog::err("Error uploading file [$filePath] to asset [$assetName]");
			return false;
		}
	}

	/**
	 * Getting path for temporary file by asset id
	 * @param int $assetId Asset Id
	 * @param string $fileExt File extension
	 * @return string Path for temporary file
	 */
	public static function getTempFilePathForAssetId($assetId, $fileExt = null)
	{
		$fileName = md5($assetId);

		$tempFolder = kConf::get('temp_folder').DIRECTORY_SEPARATOR.'WAMS'.DIRECTORY_SEPARATOR;
		if (!file_exists($tempFolder.DIRECTORY_SEPARATOR)) {
			mkdir($tempFolder);
		}

		if (empty($fileExt)) {
			$filePath = $tempFolder.$fileName;
		}
		else {
			$filePath = $tempFolder.$fileName.'.'.$fileExt;
		}

		return $filePath;
	}

	/**
	 * Creates temporary file for asset id and download part of file
	 * @param int $assetId Asset Id
	 * @param string $fileExt File extension
	 * @return string|null Path for temporary file or error
	 */
	public function createTempFileForAssetId($assetId, $fileExt = null) {

		if (empty($assetId)) {
			return null;
		}

		$mediaUrl = $this->getUrlForAssetId($assetId, $fileExt);

		if (empty($mediaUrl)) {
			return null;
		}

		$filePath = self::getTempFilePathForAssetId($assetId, $fileExt);

		if (!file_exists($filePath)) {
			// download sample
			$cmd = 'curl --silent "' . $mediaUrl . '" | head --bytes ' . self::SAMPLE_SIZE_KB . 'K > ' . $filePath;
			shell_exec($cmd);
		}

		return $filePath;
	}

	/**
	 * Deleting temporary file for asset id
	 * @param int $assetId Asset Id
	 * @param string $fileExt File extension
	 */
	public function deleteTempFileForAssetId($assetId, $fileExt = null) {
		if (empty($assetId)) {
			return;
		}

		$filePath = self::getTempFilePathForAssetId($assetId, $fileExt);

		if (file_exists($filePath)) {
			unlink($filePath);
		}

		return;
	}

	/**
	 * Getting size of file in Microsoft Azure Media Services by asset id and extension
	 * @param int $assetId Asset Id
	 * @param string $fileExt File extension
	 * @return int Size of file
	 */
	public function getFileSizeForAssetId($assetId, $fileExt = null) {
		if (!empty($assetId)) {
			$asset = $this->getAssetById($assetId);
			$assetFiles = $this->getAssetFiles($asset, $fileExt);
			if (!empty($assetFiles)) {
				return $assetFiles[0]->getContentFileSize();
			}
		}
		return -1;
	}

	/**
	 * Creates thumbnail for file in Microsoft Azure Media Services
	 * @param int $sourceAssetId Asset Id of source file
	 * @param int $position Position (in seconds) for thumbnail
	 * @param int $width Width of thumbnail
	 * @param int $height Height of thumbnail
	 * @param string $thumbFormat Format of thumbnail
	 * @return int|false Asset Id of thumbnail or error
	 */
	public function createThumbnail($sourceAssetId, $position, $width, $height, $thumbFormat = 'jpg') {
		switch ($thumbFormat){
			case 'jpg':
				$thumbType = 'Jpeg';
				break;
			default:
				$thumbType = '';
		}

		$mediaProcessor = $this->_mediaServiceProxy->getLatestMediaProcessor(self::MEDIA_PROCESSOR_NAME);
		$sourceAsset = $this->getAssetById($sourceAssetId);

		if (!$sourceAsset) {
			return false;
		}

		$positionTime = $this->getTimeBySeconds($position);

		if (($width <= 0) || ($height <= 0)) {
			$width = 1920;
			$height = '*';
		}

		$confParams = array();
		$confParams[self::CONF_PARAM_THUMB_WIDTH] = $width;
		$confParams[self::CONF_PARAM_THUMB_HEIGHT] = $height;
		$confParams[self::CONF_PARAM_THUMB_POSITION_TIME] = $positionTime;
		$confParams[self::CONF_PARAM_THUMB_TYPE] = $thumbType;

		$configuration = $this->renderConfiguration($this->confPath . self::TEMPLATE_FILE_THUMBNAIL, $confParams);

		$taskParams = array();
		$taskParams[self::CONF_PARAM_ASSET_NAME] = 'Thumbnail for ' . $sourceAsset->getName();
		$taskBody = $this->renderConfiguration($this->confPath . self::TEMPLATE_FILE_TASK_BODY, $taskParams);

		$task = new Task($taskBody, $mediaProcessor->getId(), 0);
		$task->setConfiguration($configuration);

		$job = new Job();
		$job->setName('Creation thumb for ' . $sourceAsset->getName());

		$job = $this->_mediaServiceProxy->createJob($job, array($sourceAsset), array($task));

		$jobStatus = $this->_mediaServiceProxy->getJobStatus($job);

		$processingStatuses = array(Job::STATE_QUEUED, Job::STATE_SCHEDULED, Job::STATE_PROCESSING, Job::STATE_CANCELING);
		while (in_array($jobStatus, $processingStatuses)) {
			sleep(5);
			$jobStatus = $this->_mediaServiceProxy->getJobStatus($job);
		}

		$outputAssetList = $this->_mediaServiceProxy->getJobOutputMediaAssets($job);

		if ($jobStatus != Job::STATE_FINISHED) {
			$this->clearAssetList($outputAssetList);
			KalturaLog::err("Error during creation thumbnail, job status = [$jobStatus]. Source asset ID = [$sourceAssetId], position = [$position], width = [$width], height [$height]");
			return false;
		}
		else {
			if (!count($outputAssetList)) {
				return false;
			}

			$this->publishAsset($outputAssetList[0]);

			return $outputAssetList[0]->getId();
		}
	}

	/**
	 * Add convert job to Microsoft Azure Media Services
	 * @param int $sourceAssetId Asset Id of source file
	 * @param array $preset Preset for encoding
	 * @return int|false Job Id or error
	 */
	public function addConvertJob($sourceAssetId, $preset) {
		if (!is_array($preset)) {
			return false;
		}

		$mediaProcessor = $this->_mediaServiceProxy->getLatestMediaProcessor(self::MEDIA_PROCESSOR_NAME);
		$sourceAsset = $this->getAssetById($sourceAssetId);

		if (!$sourceAsset) {
			return false;
		}

		$entryName = $this->getEntryName($sourceAssetId);
		if ($entryName) {
			$assetName = $entryName . ' - ' . $preset[self::CONVERT_PARAM_PRESET_NAME];
		}
		else {
			$assetName = $sourceAsset->getName() . ' - ' . $preset[self::CONVERT_PARAM_PRESET_NAME];
		}

		$confParams = array();
		$confParams[self::CONF_PARAM_AUDIO_BITRATE] = $preset[self::CONVERT_PARAM_AUDIO_BITRATE];
		$confParams[self::CONF_PARAM_AUDIO_CHANNELS] = $preset[self::CONVERT_PARAM_AUDIO_CHANNELS];
		$confParams[self::CONF_PARAM_AUDIO_CODEC] = $this->audioCodecs[$preset[self::CONVERT_PARAM_AUDIO_CODEC]];
		$confParams[self::CONF_PARAM_AUDIO_SAMPLE_RATE] = $preset[self::CONVERT_PARAM_AUDIO_SAMPLE_RATE];
		$confParams[self::CONF_PARAM_VIDEO_PROFILE_NAME] = $this->videoProfiles[$preset[self::CONVERT_PARAM_VIDEO_CODEC]];
		$confParams[self::CONF_PARAM_VIDEO_WIDTH] = $preset[self::CONVERT_PARAM_VIDEO_WIDTH];
		$confParams[self::CONF_PARAM_VIDEO_HEIGHT] = $preset[self::CONVERT_PARAM_VIDEO_HEIGHT];
		$confParams[self::CONF_PARAM_VIDEO_BITRATE] = $preset[self::CONVERT_PARAM_VIDEO_BITRATE];

		$configuration = $this->renderConfiguration($this->confPath . self::TEMPLATE_FILE_CONVERT, $confParams);

		$taskParams = array();
		$taskParams[self::CONF_PARAM_ASSET_NAME] = $assetName;
		$taskBody = $this->renderConfiguration($this->confPath . self::TEMPLATE_FILE_TASK_BODY, $taskParams);

		$task = new Task($taskBody, $mediaProcessor->getId(), 0);
		$task->setConfiguration($configuration);

		$job = new Job();
		$job->setName('Converting '.$sourceAsset->getName().' to '.$preset[self::CONVERT_PARAM_PRESET_NAME]);

		return $this->_mediaServiceProxy->createJob($job, array($sourceAsset), array($task))->getId();
	}

	/**
	 * Canceling job in Microsoft Azure Media Services
	 * @param int $jobId Job Id
	 */
	public function cancelJob ($jobId) {
		$this->_mediaServiceProxy->cancelJob($jobId);
		while ($this->isJobProcessing($jobId)) {
			sleep(5);
		}
	}

	/**
	 * Check job for processing in Microsoft Azure Media Services
	 * @param int $jobId Job Id
	 * @return bool
	 */
	public function isJobProcessing ($jobId) {
		$jobStatus = $this->_mediaServiceProxy->getJobStatus($jobId);
		$processingStatuses = array(Job::STATE_QUEUED, Job::STATE_SCHEDULED, Job::STATE_PROCESSING, Job::STATE_CANCELING);
		return in_array($jobStatus, $processingStatuses);
	}

	/**
	 * Check job for finished in Microsoft Azure Media Services
	 * @param int $jobId Job Id
	 * @return bool
	 */
	public function isJobFinished ($jobId) {
		return ($this->_mediaServiceProxy->getJobStatus($jobId) == Job::STATE_FINISHED);
	}

	/**
	 * Get result Output Asset Id of job
	 * @param int $jobId Job Id
	 * @return string|false Asset Id or error
	 */
	public function getOutputAssetId ($jobId) {
		$outputAssetList = $this->_mediaServiceProxy->getJobOutputMediaAssets($jobId);

		if (!$this->isJobFinished($jobId)) {
			$this->clearAssetList($outputAssetList);
			return false;
		}

		if (!count($outputAssetList)) {
			return false;
		}

		return $outputAssetList[0]->getId();
	}

	/**
	 * Getting url for file in Microsoft Azure Media Services
	 * @param int $assetId Asset Id
	 * @param string $fileExt File extension
	 * @return string|null Url of file or error
	 */
	public function getUrlForAssetId($assetId, $fileExt = null) {
		$asset = $this->getAssetById($assetId);
		$assetFiles = $this->getAssetFiles($asset, $fileExt);
		if (empty($assetFiles)) {
			KalturaLog::err("Asset files not found for asset [$assetId]");
			return null;
		}

		$fileName = $assetFiles[0]->getName();
		$locators = $this->_mediaServiceProxy->getAssetLocators($asset);
		$locatorsCount = count($locators);
		$i = 0;
		$locator = null;

		while (is_null($locator) && ($i < $locatorsCount)) {
			$accessPolicy = $this->_mediaServiceProxy->getLocatorAccessPolicy($locators[$i]);
			if ($accessPolicy->getPermissions() & AccessPolicy::PERMISSIONS_READ) {
				$locator = $locators[$i];
			}
			$i++;
		}

		if (is_null($locator)) {
			$locator = $this->publishAsset($asset);
		}

		if (is_null($locator)) {
			return null;
		}

		$url = $locator->getBaseUri().'/'.$fileName.$locator->getContentAccessComponent();

		if (!$this->checkURL($url)) {
			return null;
		}

		return $url;
	}

	/**
	 * Deleting file from Microsoft Azure Media Services
	 * @param int $assetId Asset Id
	 */
	public function deleteAssetById ($assetId)
	{
		try {
			$this->_mediaServiceProxy->deleteAsset($assetId);
		}
		catch (Exception $e) {
			KalturaLog::err("Error during deletion asset");
		}
	}

	/**
	 * Output file content
	 * @param int $assetId Asset Id
	 * @param string $fileExt File extension
	 */
	public function dumpFile($assetId, $fileExt)
	{
		$fileSize = $this->getFileSizeForAssetId($assetId, $fileExt);

		$range_length = $fileSize * 1024;
		$wamsURL = $this->getUrlForAssetId($assetId, $fileExt);
		$fh = fopen($wamsURL, 'rb');
		if ($fh) {
			infraRequestUtils::sendCdnHeaders($fileExt, $fileSize);

			while ($range_length > 0) {
				$content = fread($fh, min(self::CHUNK_SIZE, $range_length));
				echo $content;
				$range_length -= self::CHUNK_SIZE;
			}
			fclose($fh);
		}
		die(); // no view
	}

	/**
	 * Getting Microsoft Azure Media Services supported formats
	 * @return array Supported formats
	 */
	public static function getSupportedFormats()
	{
		return explode(',', kConf::get('wams_supported_formats'));
	}

	/**
	 * Identification file extension by url
	 * @param string $url Url for file
	 * @return string|null File extension or error
	 */
	public static function getFileExtFromURL ($url) {
		$content_type = null;
		$filename = null;
		$headers = get_headers($url);
		foreach ($headers as $header)
		{
			if (preg_match('/^Content-Disposition:.*filename=[",\'](.*)[\',"]/', $header, $result)) {
				if (isset($result[1])) {
					$filename = $result[1];
				}
			}
			elseif (preg_match('/^Content-Type:/', $header))
			{
				$content_type = trim(substr($header, strlen('Content-Type:')));
			}
		}
		if (!empty($filename))
		{
			return pathinfo($filename, PATHINFO_EXTENSION);
		}
		elseif (!empty($content_type))
		{
			switch ($content_type) {
				case 'video/mp4' :
					return 'mp4';
				case 'video/x-ms-wmv' :
					return 'wmv';
				case 'video/mpeg' :
					return 'mpg';
				case 'video/x-flv' :
					return 'flv';
				case 'video/webm' :
					return 'webm';
			}
		}
		return null;
	}
}