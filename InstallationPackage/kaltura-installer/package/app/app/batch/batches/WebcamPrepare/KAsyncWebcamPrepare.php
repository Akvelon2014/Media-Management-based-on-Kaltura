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
 * KAsyncWebcamPrepare batch job
 *
 * @package Scheduler
 * @subpackage WebcamPrepare
 */
class KAsyncWebcamPrepare extends KJobHandlerWorker {

	const MSG_ERROR_FILE_NOT_EXISTS = "Source file not exists";
	const MSG_ERROR_WAMS_FAIL = "Fail prepare to WAMS";

	private $entryFullPath;
	private $webcam_basePath;

	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::WEBCAM_PREPARE;
	}

	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}

	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		return $this->prepare($job, $job->data);
	}

	private function fixFlv(){
		// for webcams that might have problmes with the metada - run the clipping even if $entry_from_time and $entry_to_time are null

		// clip the webcam to some new file

		$entry_fixedFullPath = $this->webcam_basePath.'_fixed.flv';
		myFlvStaticHandler::fixRed5WebcamFlv($this->entryFullPath, $entry_fixedFullPath);

		$entry_newFullPath = $this->webcam_basePath.'_clipped.flv';
		myFlvStaticHandler::clipToNewFile( $entry_fixedFullPath, $entry_newFullPath, 0, null);

		$this->entryFullPath = $entry_newFullPath;
	}

	protected function prepare(KalturaBatchJob $job, KalturaWebcamPrepareJobData $data){

		DbManager::setConfig(kConf::getDB());
		DbManager::initialize();

		$dbEntry = entryPeer::retrieveByPK($job->entryId);
		$webcamTokenId = $data->webcamTokenId;

		$dbEntry->setStatus(entryStatus::PRECONVERT);
		$dbEntry->save();

		// check that the webcam file exists
		$content = myContentStorage::getFSContentRootPath();
		$this->webcam_basePath = $content."/content/webcam/".$webcamTokenId;
		$this->entryFullPath = $this->webcam_basePath.'.flv';
		if (! file_exists ( $this->entryFullPath )) {
			$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
			$dbEntry->save();
			return $this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, null, "Error: " . self::MSG_ERROR_FILE_NOT_EXISTS, KalturaBatchJobStatus::FAILED);
		}

		$duration = myFlvStaticHandler::getLastTimestamp($this->entryFullPath);

		$this->fixFlv();

		$kWAMSWebcam = new kWAMSWebcam($this->webcam_basePath);
		if ($kWAMSWebcam->prepare())
		{
			$this->entryFullPath = $kWAMSWebcam->getOutputFilePath();
		}
		else
		{
			$dbEntry->setStatus(entryStatus::ERROR_CONVERTING);
			$dbEntry->save();
			return $this->closeJob($job, KalturaBatchJobErrorTypes::RUNTIME, null, "Error: " . self::MSG_ERROR_WAMS_FAIL, KalturaBatchJobStatus::FAILED);
		}

		$kshowId = $dbEntry->getKshowId();

		// setup the needed params for my insert entry helper
		$paramsArray = array (
			"entry_media_source" => KalturaSourceType::WEBCAM,
			"entry_media_type" => $dbEntry->getMediaType(),
			"webcam_suffix" => $webcamTokenId,
			"entry_license" => $dbEntry->getLicenseType(),
			"entry_credit" => $dbEntry->getCredit(),
			"entry_source_link" => $dbEntry->getSourceLink(),
			"entry_tags" => $dbEntry->getTags(),
			"duration" => $duration,
		);

		$token = $this->getClient()->getKs();
		$insert_entry_helper = new myInsertEntryHelper(null , $dbEntry->getKuserId(), $kshowId, $paramsArray);
		$insert_entry_helper->setPartnerId($job->partnerId, $job->partnerId * 100);
		$insert_entry_helper->insertEntry($token, $dbEntry->getType(), $dbEntry->getId(), $dbEntry->getName(), $dbEntry->getTags(), $dbEntry);

		myNotificationMgr::createNotification( kNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry);

		return $this->closeJob($job, null, null, '', KalturaBatchJobStatus::FINISHED, $data);
	}
}