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
class kConvertProfileJobData extends kJobData
{
	/**
	 * @var string
	 */
	private $inputFileSyncLocalPath;

	/**
	 * @var string
	 */
	private $flavorAssetId;
	
	/**
	 * @var bool
	 */
	private $extractMedia = true;

	/**
	 * @var string
	 */
	private $inputFileSyncWamsAssetId;

	/**
	 * @return the $extractMedia
	 */
	public function getExtractMedia() {
		return $this->extractMedia;
	}

	/**
	 * @param $extractMedia the $extractMedia to set
	 */
	public function setExtractMedia($extractMedia) {
		$this->extractMedia = $extractMedia;
	}

	/**
	 * @return the $flavorAssetId
	 */
	public function getFlavorAssetId()
	{
		return $this->flavorAssetId;
	}

	/**
	 * @param $flavorAssetId the $flavorAssetId to set
	 */
	public function setFlavorAssetId($flavorAssetId)
	{
		$this->flavorAssetId = $flavorAssetId;
	}

	/**
	 * @param $inputFileSyncWamsAssetId the $inputFileSyncWamsAssetId to set
	 */
	public function setInputFileSyncWamsAssetId($inputFileSyncWamsAssetId)
	{
		$this->inputFileSyncWamsAssetId = $inputFileSyncWamsAssetId;
	}
	
	/**
	 * @return the $inputFileSyncLocalPath
	 */
	public function getInputFileSyncLocalPath()
	{
		return $this->inputFileSyncLocalPath;
	}

	/**
	 * @return the $inputFileSyncWamsAssetId
	*/
	public function getInputFileSyncWamsAssetId()
	{
		return $this->inputFileSyncWamsAssetId;
	}
    
	/**
	 * @param $inputFileSyncLocalPath the $inputFileSyncLocalPath to set
	 */
	public function setInputFileSyncLocalPath($inputFileSyncLocalPath)
	{
		$this->inputFileSyncLocalPath = $inputFileSyncLocalPath;
	}
}
