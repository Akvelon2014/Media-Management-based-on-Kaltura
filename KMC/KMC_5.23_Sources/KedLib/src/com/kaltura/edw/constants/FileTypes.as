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

package com.kaltura.edw.constants
{
	import mx.resources.ResourceManager;

	public class FileTypes
	{
		public static var VIDEO_TYPES:String = "*.3gp;*.3g2;*.3gp2;*.asf;*.avi;*.dv;*.ismv;*.m2ts;*.m2v;*.mod;*.mp4;*.mpeg;*.mpg;*.mts;*.ts;*.vob;*.wmv";
		public static var AUDIO_TYPES:String = "*.m4a";
		
		public static function setFileTypes(filters:XMLList):void {
			var filter:XML = filters.(@name=="video_files")[0];
			FileTypes.VIDEO_TYPES = filter.@ext;
		}
	}
}