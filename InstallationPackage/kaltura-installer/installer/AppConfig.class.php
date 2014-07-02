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

define('TOKEN_CHAR', '@'); // this character is user to surround parameters that should be replaced with configurations in config files
define('TEMPLATE_FILE', '.template'); // how to recognize a template file, template files are copyed to non-template and then the tokens are replaced
define('KCONF_LOCAL_LOCATION', '/configurations/local.ini'); // the location of kConf
define('UNINSTALLER_LOCATION', '/uninstaller/uninstall.ini'); // the location where to save configuration for the uninstaller

/* 
* This class handles all the configuration of the application:
* Defining application configuration values according to user input, 
* replaceing configuration tokens in needed files and other application configuration actions 
*/
class AppConfig {
	private $app_config = array();
	
	// gets the application value set for the given key
	public function get($key) {
		return $this->app_config[$key];
	}
	
	// sets the application value for the given key
	public function set($key, $value) {
		$this->app_config[$key] = $value;
	}
	
	// init the application configuration values according to the user input
	public function initFromUserInput($user_input) {
		foreach ($user_input as $key => $value) {
			$this->app_config[$key] = $value;
		}
		$this->defineInstallationTokens();
	}		
	
	// replaces all tokens in the given string with the configuration values and returns the new string
	public function replaceTokensInString($string) {
		foreach ($this->app_config as $key => $var) {
			$key = TOKEN_CHAR.$key.TOKEN_CHAR;
			$string = str_replace($key, $var, $string);		
		}
		return $string;
	}
		
	// replaces all the tokens in the given file with the configuration values and returns true/false upon success/failure
	// will override the file if it is not a template file
	// if it is a template file it will save it to a non template file and then override it
	public function replaceTokensInFile($file) {		
		$newfile = $this->copyTemplateFileIfNeeded($file);
		$data = @file_get_contents($newfile);
		if (!$data) {
			logMessage(L_ERROR, "Cannot replace token in file $newfile");
			return false;			
		} else {
			$data = $this->replaceTokensInString($data);
			if (!file_put_contents($newfile, $data)) {
				logMessage(L_ERROR, "Cannot replace token in file, cannot write to file $newfile");
				return false;							
			} else {
				logMessage(L_INFO, "Replaced tokens in file $newfile");			
			}
		}
		return true;
	}	
	
	// saves the uninstaller config file, the values saved are the minimal values subset needed for the uninstaller to run
	public function saveUninstallerConfig() {
		$file = $this->app_config['BASE_DIR'].UNINSTALLER_LOCATION;
		$data = "BASE_DIR = ".$this->app_config["BASE_DIR"].PHP_EOL;	
		$data = $data."DB_HOST = ".$this->app_config["DB1_HOST"].PHP_EOL;
		$data = $data."DB_USER = ".$this->app_config["DB1_USER"].PHP_EOL;
		$data = $data."DB_PASS = ".$this->app_config["DB1_PASS"].PHP_EOL;
		$data = $data."DB_PORT = ".$this->app_config["DB1_PORT"].PHP_EOL;
		return OsUtils::writeFile($file, $data);
	}	
	
	// update uninstaller config with symlinks definitions
	public function updateUninstallerConfig($symlinks) {
		$file = $this->app_config['BASE_DIR'].UNINSTALLER_LOCATION;
		$data ='';
		foreach ($symlinks as $slink) {
			$link_items = explode(SYMLINK_SEPARATOR, $this->replaceTokensInString($slink));	
			if (is_file($link_items[1]) && (strpos($link_items[1], $this->app_config["BASE_DIR"]) === false)) {
				$data = $data."symlinks[] = ".$link_items[1].PHP_EOL;
			}
		} 
		return OsUtils::appendFile($file, $data);
	}	
	
	// private functions
	
	// defines all the installation configuration values according to the user input and the default values
	private function defineInstallationTokens() {
		logMessage(L_INFO, "Defining installation tokens for config");
		// directories
		$this->app_config['APP_DIR'] = $this->app_config['BASE_DIR'].'/app';	
		$this->app_config['WEB_DIR'] = $this->app_config['BASE_DIR'].'/web';	
		$this->app_config['LOG_DIR'] = $this->app_config['BASE_DIR'].'/log';	
		$this->app_config['BIN_DIR'] = $this->app_config['BASE_DIR'].'/bin';	
		$this->app_config['TMP_DIR'] = $this->app_config['BASE_DIR'].'/tmp/';
		$this->app_config['DWH_DIR'] = $this->app_config['BASE_DIR'].'/dwh';
		$this->app_config['ETL_HOME_DIR'] = $this->app_config['BASE_DIR'].'/dwh'; // For backward compatibility
		$this->app_config['SPHINX_BIN_DIR'] = $this->app_config['BIN_DIR'].'/sphinx';
		
		$this->app_config['IMAGE_MAGICK_BIN_DIR'] = "/usr/bin";
		$this->app_config['CURL_BIN_DIR'] = "/usr/bin";
		
		
		// site settings
		if (strpos($this->app_config['KALTURA_FULL_VIRTUAL_HOST_NAME'], ":") !== false)
		{
			$this->app_config['KALTURA_VIRTUAL_HOST_PORT'] = parse_url($this->app_config['KALTURA_FULL_VIRTUAL_HOST_NAME'], PHP_URL_PORT);
		}
		else
		{
			$this->app_config['KALTURA_VIRTUAL_HOST_PORT'] = 80;
		}
		
		$this->app_config['KALTURA_VIRTUAL_HOST_NAME'] = $this->removeHttp($this->app_config['KALTURA_FULL_VIRTUAL_HOST_NAME']);
		$this->app_config['CORP_REDIRECT'] = '';	
		$this->app_config['CDN_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['IIS_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['RTMP_URL'] = self::stripProtocol($this->app_config['KALTURA_VIRTUAL_HOST_NAME']);
		$this->app_config['MEMCACHE_HOST'] = self::stripProtocol($this->app_config['KALTURA_VIRTUAL_HOST_NAME']);
		$this->app_config['GLOBAL_MEMCACHE_HOST'] = self::stripProtocol($this->app_config['KALTURA_VIRTUAL_HOST_NAME']);
		$this->app_config['WWW_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['BASE_HOST_NO_PORT'] = self::stripProtocol($this->app_config['KALTURA_VIRTUAL_HOST_NAME']);
		$this->app_config['SERVICE_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['ENVIRONMENT_NAME'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		
		// databases (copy information collected during prerequisites
		if ($this->app_config['DB1_HOST'] == 'localhost') {
			$this->app_config['DB1_HOST'] = '127.0.0.1';
		}
		$this->collectDatabaseCopier('DB1', 'DB2');
		$this->collectDatabaseCopier('DB1', 'DB3');

		//sphinx
		$this->app_config['SPHINX_SERVER'] = $this->app_config['DB1_HOST'];
		$this->app_config['SPHINX_DB_NAME'] = 'kaltura_sphinx_log';
		$this->app_config['SPHINX_DB_PORT'] = $this->app_config['DB1_PORT'];
		$this->app_config['SPHINX_DB_USER'] = $this->app_config['DB1_USER'];
		$this->app_config['SPHINX_DB_PASS'] = $this->app_config['DB1_PASS'];

		// admin console defaults
		$this->app_config['SYSTEM_USER_ADMIN_EMAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['ADMIN_CONSOLE_PARTNER_ALIAS'] = md5('-2kaltura partner');
		$this->app_config['ADMIN_CONSOLE_KUSER_MAIL'] = 'admin_console@'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->generateSha1Salt($this->app_config['ADMIN_CONSOLE_PASSWORD'], $salt, $sha1);
		$this->app_config['SYSTEM_USER_ADMIN_SALT'] = $salt;
		$this->app_config['ADMIN_CONSOLE_KUSER_SHA1'] = $salt;
		$this->app_config['SYSTEM_USER_ADMIN_SHA1'] = $sha1;
		$this->app_config['ADMIN_CONSOLE_KUSER_SALT'] = $sha1;
		$this->app_config['UICONF_TAB_ACCESS'] = 'SYSTEM_ADMIN_BATCH_CONTROL';

		// stats DB
		$this->collectDatabaseCopier('DB1', 'DB_STATS');
		$this->app_config['DB_STATS_NAME'] = 'kaltura_stats';
		
		// data warehouse
		$this->app_config['DWH_HOST'] = $this->app_config['DB1_HOST'];
		$this->app_config['DWH_PORT'] = $this->app_config['DB1_PORT'];
		$this->app_config['DWH_DATABASE_NAME'] = 'kalturadw';
		$this->app_config['DWH_USER'] = 'etl';
		$this->app_config['DWH_PASS'] = 'etl';
		$this->app_config['DWH_SEND_REPORT_MAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['EVENTS_LOGS_DIR'] = $this->app_config['LOG_DIR'];
		$this->app_config['EVENTS_WILDCARD'] = '.*kaltura.*_apache_access.log-.*';
		$this->app_config['EVENTS_FETCH_METHOD'] = 'local';
		
				
		// default partners and kusers
		$this->app_config['TEMPLATE_PARTNER_MAIL'] = 'template@'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['TEMPLATE_KUSER_MAIL'] = $this->app_config['TEMPLATE_PARTNER_MAIL'];		
		$this->app_config['TEMPLATE_ADMIN_KUSER_SALT'] = $this->app_config['SYSTEM_USER_ADMIN_SALT'];
		$this->app_config['TEMPLATE_ADMIN_KUSER_SHA1'] = $this->app_config['SYSTEM_USER_ADMIN_SHA1'];		
		
		$this->app_config['PARTNER_ZERO_PARTNER_ALIAS'] = md5('-1kaltura partner zero');		
		
		// batch
		$this->app_config['BATCH_ADMIN_MAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['BATCH_KUSER_MAIL'] = 'batch@'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['BATCH_HOST_NAME'] = OsUtils::getComputerName();
		$this->app_config['BATCH_PARTNER_PARTNER_ALIAS'] = md5('-1kaltura partner');		
				
		// other configurations
		$this->app_config['APACHE_RESTART_COMMAND'] = $this->app_config['HTTPD_BIN'].' -k restart';
		date_default_timezone_set($this->app_config['TIME_ZONE']);
		$this->app_config['GOOGLE_ANALYTICS_ACCOUNT'] = 'UA-7714780-1';
		$this->app_config['INSTALLATION_TYPE'] = '';
		$this->app_config['PARTNERS_USAGE_REPORT_SEND_FROM'] = ''; 
		$this->app_config['PARTNERS_USAGE_REPORT_SEND_TO'] = '';
		$this->app_config['SYSTEM_PAGES_LOGIN_USER'] = '';
		$this->app_config['SYSTEM_PAGES_LOGIN_PASS'] = '';
		$this->app_config['KMC_BACKDOR_SHA1_PASS'] = '';
		$this->app_config['DC0_SECRET'] = '';
		$this->app_config['APACHE_CONF'] = '';
		
		// storage profile related
		$this->app_config['DC_NAME'] = 'local';
		$this->app_config['DC_DESCRIPTION'] = 'local';
		$this->app_config['STORAGE_BASE_DIR'] = $this->app_config['WEB_DIR'];
		$this->app_config['DELIVERY_HTTP_BASE_URL'] = $this->app_config['SERVICE_URL'];
		$this->app_config['DELIVERY_RTMP_BASE_URL'] = $this->app_config['RTMP_URL'];
		$this->app_config['DELIVERY_ISS_BASE_URL'] = $this->app_config['SERVICE_URL'];	
		$this->app_config['ENVIRONMENT_NAME'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		
		// set the usage tracking for Kaltura TM
		if (strcasecmp($this->app_config['KALTURA_VERSION_TYPE'], K_TM_TYPE) === 0) {
			$this->app_config['PARTNERS_USAGE_REPORT_SEND_FROM'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
			$this->app_config['PARTNERS_USAGE_REPORT_SEND_TO'] = "on-prem-monthly@kaltura.com";
		}
		
		// mails configurations
		$this->app_config['FORUMS_URLS'] = '';
		$this->app_config['CONTACT_URL'] = 'https://github.com/Akvelon2014/Media-Management-based-on-Kaltura/issues';
		$this->app_config['CONTACT_PHONE_NUMBER'] = '';
		$this->app_config['BEGINNERS_TUTORIAL_URL'] = '';
		$this->app_config['QUICK_START_GUIDE_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'].'/content/docs/wams/kmc.html';
		$this->app_config['UNSUBSCRIBE_EMAIL_URL'] = '';

		//Set parameters default value if they are not included in a previous user_input.ini
		if(!isset($this->app_config['DB1_CREATE_NEW_DB']))
			$this->app_config['DB1_CREATE_NEW_DB'] = true;
		else
			$this->app_config['DB1_CREATE_NEW_DB'] = ((strcasecmp('y',$this->app_config['DB1_CREATE_NEW_DB']) === 0) || (strcasecmp('yes',$this->app_config['DB1_CREATE_NEW_DB']) === 0));
	
		if (!isset($this->app_config['RED5_INSTALL']))
			$this->app_config['RED5_INSTALL'] = false;
		else
			$this->app_config['RED5_INSTALL'] = ((strcasecmp('y',$this->app_config['RED5_INSTALL']) === 0) || (strcasecmp('yes',$this->app_config['RED5_INSTALL']) === 0));

		if ($this->app_config['DB1_CREATE_NEW_DB'])
		{
			$this->app_config['PARTNER_ZERO_SECRET'] = $this->generateSecret();
			$this->app_config['PARTNER_ZERO_ADMIN_SECRET'] = $this->generateSecret();
			$this->app_config['BATCH_PARTNER_SECRET'] = $this->generateSecret();
			$this->app_config['BATCH_PARTNER_ADMIN_SECRET'] = $this->generateSecret();
			$this->app_config['ADMIN_CONSOLE_PARTNER_SECRET'] = $this->generateSecret();
			$this->app_config['ADMIN_CONSOLE_PARTNER_ADMIN_SECRET'] =  $this->generateSecret();
		}
		else 
		{
			if (!empty($this->app_config['DB1_PASS'])) {
				$mysql_str = 'mysql -h'.$this->app_config['DB1_HOST']. ' -P'.$this->app_config['DB1_PORT'] . ' -u'.$this->app_config['DB1_USER'] . ' -p'. $this->app_config['DB1_PASS'] . ' '. $this->app_config['DB1_NAME'] . ' --skip-column-names';
			}
			else {
				$mysql_str = 'mysql -h'.$this->app_config['DB1_HOST']. ' -P'.$this->app_config['DB1_PORT'] . ' -u'.$this->app_config['DB1_USER'] . ' '. $this->app_config['DB1_NAME'] . ' --skip-column-names';
			}

			$output = OsUtils::executeReturnOutput('echo "select secret from partner where id=0" | ' . $mysql_str );
			$this->app_config['PARTNER_ZERO_SECRET'] = $output[0];
			$output = OsUtils::executeReturnOutput('echo "select admin_secret from partner where id=0" | ' . $mysql_str );
			$this->app_config['PARTNER_ZERO_ADMIN_SECRET'] = $output[0];
			$output = OsUtils::executeReturnOutput('echo "select secret from partner where id=-1" | ' . $mysql_str );
			$this->app_config['BATCH_PARTNER_SECRET'] = $output[0];
			$output = OsUtils::executeReturnOutput('echo "select admin_secret from partner where id=-1" | ' . $mysql_str );
			$this->app_config['BATCH_PARTNER_ADMIN_SECRET'] = $output[0];
			$output = OsUtils::executeReturnOutput('echo "select secret from partner where id=-2" | ' . $mysql_str );
			$this->app_config['ADMIN_CONSOLE_PARTNER_SECRET'] = $output[0];
			$output = OsUtils::executeReturnOutput('echo "select admin_secret from partner where id=-2" | ' . $mysql_str );
			$this->app_config['ADMIN_CONSOLE_PARTNER_ADMIN_SECRET'] =  $output[0];
		}
	}
	
	public function defineConfigurationTokens() {

		$this->app_config['KALTURA_VIRTUAL_HOST_NAME'] = $this->removeHttp($this->app_config['KALTURA_FULL_VIRTUAL_HOST_NAME']);
		$this->app_config['CDN_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['IIS_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['RTMP_URL'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['MEMCACHE_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['GLOBAL_MEMCACHE_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['WWW_HOST'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['SERVICE_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['ENVIRONMENT_NAME'] = $this->app_config['KALTURA_VIRTUAL_HOST_NAME'];		
		$this->app_config['BATCH_KUSER_MAIL'] = 'batch@'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['TEMPLATE_PARTNER_MAIL'] = 'template@'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['TEMPLATE_KUSER_MAIL'] = $this->app_config['TEMPLATE_PARTNER_MAIL'];
		$this->app_config['ADMIN_CONSOLE_KUSER_MAIL'] = 'admin_console@'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'];
		$this->app_config['DELIVERY_HTTP_BASE_URL'] = $this->app_config['SERVICE_URL'];
		$this->app_config['DELIVERY_ISS_BASE_URL'] = $this->app_config['SERVICE_URL'];	
		$this->app_config['DELIVERY_RTMP_BASE_URL'] = $this->app_config['RTMP_URL'];
		$this->app_config['SYSTEM_USER_ADMIN_EMAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['DWH_SEND_REPORT_MAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['BATCH_ADMIN_MAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['PARTNERS_USAGE_REPORT_SEND_FROM'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];

		$this->generateSha1Salt($this->app_config['ADMIN_CONSOLE_PASSWORD'], $salt, $sha1);
		$this->app_config['SYSTEM_USER_ADMIN_SALT'] = $salt;
		$this->app_config['ADMIN_CONSOLE_KUSER_SHA1'] = $salt;
		$this->app_config['SYSTEM_USER_ADMIN_SHA1'] = $sha1;
		$this->app_config['ADMIN_CONSOLE_KUSER_SALT'] = $sha1;
		$this->app_config['TEMPLATE_ADMIN_KUSER_SALT'] = $this->app_config['SYSTEM_USER_ADMIN_SALT'];
		$this->app_config['TEMPLATE_ADMIN_KUSER_SHA1'] = $this->app_config['SYSTEM_USER_ADMIN_SHA1'];
		
		$this->app_config['XYMON_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'].'/xymon/';
		$this->app_config['QUICK_START_GUIDE_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'].'/content/docs/KMC_Quick_Start_Guide.pdf';
		$this->app_config['UNSUBSCRIBE_EMAIL_URL'] = '"'.$this->app_config['WORK_MODE'].'://'.$this->app_config['KALTURA_VIRTUAL_HOST_NAME'].'/index.php/extwidget/blockMail?e="';
	}
	
	public function definePostInstallationConfigurationTokens()
	{
		$this->app_config['POST_INST_VIRTUAL_HOST_NAME'] = $this->removeHttp($this->app_config['KALTURA_FULL_VIRTUAL_HOST_NAME']);
		$this->app_config['KALTURA_VIRTUAL_HOST_NAME'] = $this->app_config['POST_INST_VIRTUAL_HOST_NAME'];
		$this->app_config['DELIVERY_HTTP_BASE_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['POST_INST_VIRTUAL_HOST_NAME'];
		$this->app_config['DELIVERY_ISS_BASE_URL'] = $this->app_config['WORK_MODE'].'://'.$this->app_config['POST_INST_VIRTUAL_HOST_NAME'];
		$this->app_config['DELIVERY_RTMP_BASE_URL'] = $this->app_config['POST_INST_VIRTUAL_HOST_NAME'];

		$this->app_config['POST_INST_ADMIN_CONSOLE_ADMIN_MAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];

		$this->app_config['BATCH_KUSER_MAIL'] = 'batch@'.$this->app_config['POST_INST_VIRTUAL_HOST_NAME'];
		$this->app_config['TEMPLATE_PARTNER_MAIL'] = 'template@'.$this->app_config['POST_INST_VIRTUAL_HOST_NAME'];
		$this->app_config['TEMPLATE_KUSER_MAIL'] = $this->app_config['TEMPLATE_PARTNER_MAIL'];
		$this->app_config['SYSTEM_USER_ADMIN_EMAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$this->app_config['ADMIN_CONSOLE_KUSER_MAIL'] = 'admin_console@'.$this->app_config['POST_INST_VIRTUAL_HOST_NAME'];
		$this->app_config['BATCH_ADMIN_MAIL'] = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];

		$this->generateSha1Salt($this->app_config['ADMIN_CONSOLE_PASSWORD'], $salt, $sha1);

		$this->app_config['SYSTEM_USER_ADMIN_SALT'] = $salt;
		$this->app_config['ADMIN_CONSOLE_KUSER_SHA1'] = $salt;
		$this->app_config['SYSTEM_USER_ADMIN_SHA1'] = $sha1;
		$this->app_config['ADMIN_CONSOLE_KUSER_SALT'] = $sha1;
		$this->app_config['ADMIN_CONSOLE_KUSER_SHA1'] = $sha1;
		$this->app_config['TEMPLATE_ADMIN_KUSER_SALT'] = $this->app_config['SYSTEM_USER_ADMIN_SALT'];
		$this->app_config['TEMPLATE_ADMIN_KUSER_SHA1'] = $this->app_config['SYSTEM_USER_ADMIN_SHA1'];		
	}

	// copies DB parametes from one DB configuration to another
	private function collectDatabaseCopier($from_db, $to_db) {
		$this->app_config[$to_db.'_HOST'] = $this->app_config[$from_db.'_HOST'];
		$this->app_config[$to_db.'_PORT'] = $this->app_config[$from_db.'_PORT'];
		$this->app_config[$to_db.'_NAME'] = $this->app_config[$from_db.'_NAME'];
		$this->app_config[$to_db.'_USER'] = $this->app_config[$from_db.'_USER'];
		$this->app_config[$to_db.'_PASS'] = $this->app_config[$from_db.'_PASS'];
	}
		
	// generates a secret for Kaltura and returns it
	private function generateSecret() {
		logMessage(L_INFO, "Generating secret");
		$secret = md5(self::str_makerand(5,10,true, false, true));
		return $secret;
	}
	
	/**
	 * Generates sha1 and salt from a password
	 * @param string $password chosen password
	 * @param string $salt salt will be generated
	 * @param string $sha1 sha1 will be generated
	 * @return $sha1 & $salt by reference
	 */
	public static function generateSha1Salt($password, &$salt, &$sha1) {
		$salt = md5(rand(100000, 999999).$password); 
		$sha1 = sha1($salt.$password);  
	}
	
	// puts a Kaltura CE activation key
	public function simMafteach() {
		$admin_email = $this->app_config['ADMIN_CONSOLE_ADMIN_MAIL'];
		$kConfLocalFile = $this->app_config['APP_DIR'].KCONF_LOCAL_LOCATION;
		logMessage(L_INFO, "Setting application key");
		$token = md5(uniqid(rand(), true));
		$str = implode("|", array(md5($admin_email), '1', 'never', $token));
		$key = base64_encode($str);
		$data = @file_get_contents($kConfLocalFile);
		$key_line = '/kaltura_activation_key(\s)*=(\s)*(.+)/';
		$replacement = 'kaltura_activation_key = "'.$key.'"';
		$data = preg_replace($key_line, $replacement ,$data);
		@file_put_contents($kConfLocalFile, $data);
	}
	
	// removes http:// or https:// prefix from the string and returns it
	private function removeHttp($url = '') {
		$list = array('http://', 'https://');
		foreach ($list as $item) {
			if (strncasecmp($url, $item, strlen($item)) == 0)
				return substr($url, strlen($item));
		}
		return $url;
	}
	
	// checks if the given file is a template file and if so copies it to a non template file
	// returns the non template file if it was copied or the original file if it was not copied
	private function copyTemplateFileIfNeeded($file) {
		$return_file = $file;
		// Replacement in a template file, first copy to a non .template file
		if (strpos($file, TEMPLATE_FILE) !== false) {
			$return_file = str_replace(TEMPLATE_FILE, "", $file);
			logMessage(L_INFO, "$file token file contains ".TEMPLATE_FILE);
			OsUtils::fullCopy($file, $return_file);
		}
		return $return_file;
	}
	
	// creates a random key used to generate a secret
	private static function str_makerand($minlength, $maxlength, $useupper, $usespecial, $usenumbers) {
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		$key = "";
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}	
	
	private static function stripProtocol($url)
	{
		if (strpos($url, ":"))
		{
			return parse_url($url, PHP_URL_HOST);
		}
		
		return $url;
	}
}
