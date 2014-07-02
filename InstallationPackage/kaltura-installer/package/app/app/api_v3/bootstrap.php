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

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."infra".DIRECTORY_SEPARATOR."bootstrap_base.php");

define("KALTURA_API_V3", true); // used for different logic in alpha libs

define("KALTURA_API_PATH", KALTURA_ROOT_PATH.DIRECTORY_SEPARATOR."api_v3");
require_once(KALTURA_API_PATH.DIRECTORY_SEPARATOR.'VERSION.php'); //defines KALTURA_API_VERSION
require_once (KALTURA_ROOT_PATH.DIRECTORY_SEPARATOR.'infra'.DIRECTORY_SEPARATOR.'kConf.php');
require_once (KALTURA_ROOT_PATH.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

// Autoloader
require_once(KALTURA_INFRA_PATH.DIRECTORY_SEPARATOR."KAutoloader.php");
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_API_PATH, "lib", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_API_PATH, "services", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "alpha", "plugins", "*")); // needed for testmeDoc
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "generator")); // needed for testmeDoc
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "infra", "utils", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "infra", "thumbnailMaker", "*"));
KAutoloader::setClassMapFilePath(kConf::get("cache_root_path") . '/api_v3/classMap.cache');
//KAutoloader::dumpExtra();
KAutoloader::register();


// Timezone
date_default_timezone_set(kConf::get("date_default_timezone")); // America/New_York


// Logger
$loggerConfigPath = realpath(KALTURA_ROOT_PATH . DIRECTORY_SEPARATOR . "configurations" . DIRECTORY_SEPARATOR . "logger.ini");

try // we don't want to fail when logger is not configured right
{
	$config = new Zend_Config_Ini($loggerConfigPath);
	$api_v3 = $config->api_v3;
	
	KalturaLog::initLog($api_v3);
}
catch(Zend_Config_Exception $ex)
{
	$config = null;
}

