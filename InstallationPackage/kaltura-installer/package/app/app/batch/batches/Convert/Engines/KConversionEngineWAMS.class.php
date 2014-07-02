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
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class KConversionEngineWAMS extends KJobConversionEngine
{
	const WAMS = "wams";
	protected $batchJobId;

	protected function __construct( KSchedularTaskConfig $engine_config, $jobId ) {
		parent::__construct($engine_config);
		$this->batchJobId = $jobId;
	}

	/**
	 * Getting name of current conversion engine
	 * @return string Conversion engine name
	 */
	public function getName()
	{
		return self::WAMS;
	}

	/**
	 * Getting type of current conversion engine
	 * @return string Conversion engine type
	 */
	public function getType()
	{
		return KalturaConversionEngineType::WAMS;
	}

	/**
	 * @deprecated deprecated for this conversion engine
	 */
	public function getCmd()
	{
		return null;
	}

	/**
	 * Running converting
	 * @param KalturaConvartableJobData $data Job data
	 * @return array Result of converting
	 */
	public function convert(KalturaConvartableJobData &$data)
	{
		return $this->convertJob($data);
	}

	/**
	 * Do converting
	 * @param KalturaConvertJobData $data Job data
	 * @return array Result of converting
	 */
	public function convertJob(KalturaConvertJobData &$data)
	{
		$outputParams = $data->flavorParamsOutput;
		$videoCodec = $outputParams->videoCodec;
		$videoBitrate = $outputParams->videoBitrate;
		$width = $outputParams->width;
		$height = $outputParams->height;
		$presetName = $outputParams->name;

		$preset = array();
		$preset[kWAMS::CONVERT_PARAM_PRESET_NAME] = $outputParams->name;
		$preset[kWAMS::CONVERT_PARAM_AUDIO_CODEC] = $outputParams->audioCodec;
		$preset[kWAMS::CONVERT_PARAM_AUDIO_BITRATE] = $outputParams->audioBitrate;
		$preset[kWAMS::CONVERT_PARAM_AUDIO_CHANNELS] = $outputParams->audioChannels;
		$preset[kWAMS::CONVERT_PARAM_AUDIO_SAMPLE_RATE] = $outputParams->audioSampleRate;
		$preset[kWAMS::CONVERT_PARAM_VIDEO_CODEC] = $outputParams->videoCodec;
		$preset[kWAMS::CONVERT_PARAM_VIDEO_WIDTH] = $outputParams->width;
		$preset[kWAMS::CONVERT_PARAM_VIDEO_HEIGHT] = $outputParams->height;
		$preset[kWAMS::CONVERT_PARAM_VIDEO_BITRATE] = $outputParams->videoBitrate;

		KalturaLog::debug("Video params output: preset = [$presetName], videoCodec = [$videoCodec], videoBitrate = [$videoBitrate], width = [$width], height = [$height]");
		$start = microtime(true);

		$kWAMS = kWAMS::getInstance($data->flavorParamsOutput->partnerId);
		$wamsJobId = $kWAMS->addConvertJob($data->srcFileSyncWamsAssetId, $preset);

		Propel::disableInstancePooling();
		while ($kWAMS->isJobProcessing($wamsJobId)) {
			// check Kaltura Batch Job Status
			if (BatchJobPeer::retrieveByPK($this->batchJobId)->getAbort()) {
				$kWAMS->cancelJob($wamsJobId);
			}
			sleep(5);
		}
		Propel::enableInstancePooling();

		$data->destFileSyncWamsAssetId = $kWAMS->getOutputAssetId($wamsJobId);

		$end = microtime(true);

		$duration = ($end - $start);
		KalturaLog::info($this->getName() . ":  took [$duration] seconds");

		return array(true, null);
	}
}

