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
 * 
 * @package Scheduler
 */

require_once("../infra/bootstrap_base.php");
require_once(KALTURA_ROOT_PATH . '/infra/kConf.php');
require_once (KALTURA_ROOT_PATH.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

define("KALTURA_BATCH_PATH", KALTURA_ROOT_PATH . "/batch");

// Autoloader - override the autoloader defaults
require_once(KALTURA_INFRA_PATH . "/KAutoloader.php");
KAutoloader::setClassPath(array(
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "infra", "*"),
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "PHPMailer", "*"),
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "phpseclib", "*"),
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "*"),
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"),
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "alpha", "*"),
	KAutoloader::buildPath(KALTURA_BATCH_PATH, "*"),
));

KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "*", "batch", "*"));

KAutoloader::setIncludePath(array(
	KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "ZendFramework", "library"),
));
KAutoloader::setClassMapFilePath(kConf::get("cache_root_path") . '/batch/classMap.cache');
KAutoloader::register();

set_include_path(get_include_path() . PATH_SEPARATOR . KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "phpseclib"));
set_include_path(get_include_path() . PATH_SEPARATOR . KAutoloader::buildPath(KALTURA_ROOT_PATH, "alpha", "apps", "kaltura", "lib"));

// Logger
$loggerConfigPath = KALTURA_ROOT_PATH . "/configurations/logger.ini";

try // we don't want to fail when logger is not configured right
{
	$config = new Zend_Config_Ini($loggerConfigPath);
	KalturaLog::initLog($config->batch);
	KalturaLog::setContext("BATCH");
}
catch(Zend_Config_Exception $ex)
{
}

