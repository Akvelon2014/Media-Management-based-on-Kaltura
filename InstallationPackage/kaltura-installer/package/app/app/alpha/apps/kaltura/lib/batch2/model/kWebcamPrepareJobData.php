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
 * Data for webcam prepare batch job
 * @package Core
 * @subpackage model.data
 */
class kWebcamPrepareJobData extends kJobData {
	/**
	 * Webcam token ID
	 *
	 * @var string
	 */
	private $webcamTokenId;

	/**
	 * Getting webcam token ID
	 * @return string webcam token ID
	 */
	public function getWebcamTokenId(){
		return $this->webcamTokenId;
	}

	/**
	 * Setting webcam token ID
	 * @param string $webcamTokenId webcam token ID
	 */
	public function setWebcamTokenId($webcamTokenId){
		$this->webcamTokenId = $webcamTokenId;
	}
} 