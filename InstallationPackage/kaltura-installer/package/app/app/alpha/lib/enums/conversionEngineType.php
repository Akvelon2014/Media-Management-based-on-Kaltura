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
 * @subpackage model.enum
 */ 
interface conversionEngineType extends BaseEnum
{
	const KALTURA_COM = 0;
	const ON2 = 1;
	const FFMPEG = 2;
	const MENCODER = 3;
	const ENCODING_COM = 4;
	const EXPRESSION_ENCODER3 = 5;
	
	const FFMPEG_VP8 = 98;
	const FFMPEG_AUX = 99;
	
	// document conversion engines
	const PDF2SWF = 201;
	const PDF_CREATOR = 202;

	const WAMS = 8;
}
