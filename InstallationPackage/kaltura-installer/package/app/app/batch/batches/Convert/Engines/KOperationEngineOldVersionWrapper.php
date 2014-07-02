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
 * base class for the real ConversionEngines in the system - ffmpeg,menconder and flix. 
 * 
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class KOperationEngineOldVersionWrapper extends KOperationEngine
{
	/**
	 * @var KalturaConvartableJobData
	 */
	protected $data;
	 
	/**
	 * @var KConversionEngine
	 */
	protected $convertor;
	
	public function __construct($type, KSchedularTaskConfig $taskConfig, KalturaConvartableJobData $data, $jobId = null)
	{
		$this->data = $data;
		$this->convertor = KConversionEngine::getInstance($type, $taskConfig, $jobId);
	}

	protected function doOperation()
	{
		list($ok, $errorMessage) = $this->convertor->convert($this->data);
		if(!$ok)
			throw new KOperationEngineException($errorMessage);
	}
	
	/**
	 * @param bool $enabled
	 */
	public function setMediaInfoEnabled($enabled)
	{
		$this->convertor->setMediaInfoEnabled($enabled);
	}
	
	/* (non-PHPdoc)
	 * @see KOperationEngine::getLogFilePath()
	 */
	public function getLogFilePath()
	{
		return $this->convertor->getLogFilePath();
	}
	
	/* (non-PHPdoc)
	 * @see KOperationEngine::getLogData()
	 */
	public function getLogData()
	{
		return $this->convertor->getLogData();
	}
	
	protected function getCmdLine(){}
}


