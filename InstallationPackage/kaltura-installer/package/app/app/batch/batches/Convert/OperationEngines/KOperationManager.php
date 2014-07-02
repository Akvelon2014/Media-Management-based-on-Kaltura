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
 * @package Scheduler
 * @subpackage Conversion
 */
class KOperationManager
{
	/**
	 * @param int $type
	 * @param KSchedularTaskConfig $taskConfig
	 * @param KalturaConvartableJobData $data
	 * @param KalturaClient $client
	 * @return KOperationEngine
	 */
	public static function getEngine($type, KSchedularTaskConfig $taskConfig, KalturaConvartableJobData $data, KalturaClient $client, $jobId = null)
	{
		$engine = self::createNewEngine($type, $taskConfig, $data, $jobId);
		if(!$engine)
			return null;
			
		$engine->configure($taskConfig, $data, $client);
		return $engine;
	}
	
	/**
	 * @param int $type
	 * @param KSchedularTaskConfig $taskConfig
	 * @param KalturaConvartableJobData $data
	 * @return KOperationEngine
	 */
	protected static function createNewEngine($type, KSchedularTaskConfig $taskConfig, KalturaConvartableJobData $data, $jobId = null)
	{
		// TODO - remove after old version deprecated
		/*
		 * The 'flavorParamsOutput' is not set only for SL/ISM collections - that is definently old engine' flow
		 */
		if(!isset($data->flavorParamsOutput) || !$data->flavorParamsOutput->engineVersion)
		{
			return new KOperationEngineOldVersionWrapper($type, $taskConfig, $data, $jobId);
		}
		
		switch($type)
		{
			case KalturaConversionEngineType::MENCODER:
				return new KOperationEngineMencoder($taskConfig->params->mencderCmd, $data->destFileSyncLocalPath);
				
			case KalturaConversionEngineType::ON2:
				return new KOperationEngineFlix($taskConfig->params->on2Cmd, $data->destFileSyncLocalPath);
				
			case KalturaConversionEngineType::FFMPEG:
				return new KOperationEngineFfmpeg($taskConfig->params->ffmpegCmd, $data->destFileSyncLocalPath);
				
			case KalturaConversionEngineType::FFMPEG_AUX:
				return new KOperationEngineFfmpegAux($taskConfig->params->ffmpegAuxCmd, $data->destFileSyncLocalPath);
				
			case KalturaConversionEngineType::FFMPEG_VP8:
				return new KOperationEngineFfmpegVp8($taskConfig->params->ffmpegVp8Cmd, $data->destFileSyncLocalPath);
				
			case KalturaConversionEngineType::ENCODING_COM :
				return new KOperationEngineEncodingCom(
					$taskConfig->params->EncodingComUserId, 
					$taskConfig->params->EncodingComUserKey, 
					$taskConfig->params->EncodingComUrl);
		}
		
		if($data instanceof KalturaConvertCollectionJobData)
		{
			$engine = self::getCollectionEngine($type, $taskConfig, $data);
			if($engine)
				return $engine;
		}
		
		$engine = KalturaPluginManager::loadObject('KOperationEngine', $type, array('params' => $taskConfig->params, 'outFilePath' => $data->destFileSyncLocalPath));
		
		return $engine;
	}
	
	protected static function getCollectionEngine($type, KSchedularTaskConfig $taskConfig, KalturaConvertCollectionJobData $data)
	{
		switch($type)
		{
			case KalturaConversionEngineType::EXPRESSION_ENCODER3:
				return new KOperationEngineExpressionEncoder3($taskConfig->params->expEncoderCmd, $data->destFileName, $data->destDirLocalPath);
		}
		
		return  null;
	}
}


