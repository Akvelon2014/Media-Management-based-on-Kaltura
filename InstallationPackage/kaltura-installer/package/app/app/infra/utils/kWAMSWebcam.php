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
 * Converts flv to mp4, removes flv and related files
 *
 * @package infra
 * @subpackage utils
 */
class kWAMSWebcam {

	const OUTPUT_FILE_EXT = 'mp4';
	const INPUT_FILE_END = '_clipped.flv';
	const CONVERT_COMMAND = ' -sameq -y -i %s -c:v libx264 -c:a libfaac  -async 1 %s';
	const FFMPEG_PARAM = 'bin_path_ffmpeg';

	private $webcam_basePath;

	private function convert()
	{
		shell_exec(kConf::get(self::FFMPEG_PARAM).sprintf(self::CONVERT_COMMAND, $this->getInputFilePath(), $this->getOutputFilePath()));
		return file_exists($this->getOutputFilePath());
	}

	private function getInputFilePath()
	{
		return $this->webcam_basePath.self::INPUT_FILE_END;
	}

	private function clean(){
		$path = pathinfo($this->webcam_basePath, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;
		$regex = '/^'.pathinfo($this->webcam_basePath, PATHINFO_BASENAME).'.*/';
		$files = preg_grep($regex, scandir($path));

		$outputFile = pathinfo($this->getOutputFilePath(), PATHINFO_BASENAME);
		foreach ($files as $file) {
			if ($file != $outputFile){
				unlink($path.$file);
			}
		}
	}

	/**
	 * Getting an instance of kWAMSWebcam
	 *
	 * @param string $webcam_basePath base path of flv-files
	 * @return kWAMSWebcam
	 */
	public function __construct($webcam_basePath)
	{
		$this->webcam_basePath = $webcam_basePath;
	}

	/**
	 * Getting output file path
	 *
	 * @return string file path
	 */
	public function getOutputFilePath()
	{
		return $this->webcam_basePath.'.'.self::OUTPUT_FILE_EXT;
	}

	/**
	 * Converting flv to mp4 and clean webcam directory
	 *
	 * @return bool result of process
	 */
	public function prepare()
	{
		if (!$this->convert())
		{
			return false;
		}
		$this->clean();

		return true;
	}
}