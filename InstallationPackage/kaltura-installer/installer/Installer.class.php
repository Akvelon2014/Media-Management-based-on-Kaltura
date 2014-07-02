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

define("FILE_INSTALL_CONFIG", "installer/installation.ini"); // this file contains the definitions of the installation itself
define("APP_SQL_DIR", "/app/deployment/final/sql/"); // this is the relative directory where the final sql files are
define("SYMLINK_SEPARATOR", "^"); // this is the separator between the two parts of the symbolic link definition

/*
* This class handles the installation itself. It has functions for installing and for cleaning up.
*/
class Installer {	
	private $install_config;

	// crteate a new installer, loads installation configurations from installation configuration file
	public function __construct() {
		$this->install_config = parse_ini_file(FILE_INSTALL_CONFIG, true);
	}
	
	// detects if there are leftovers of an installation
	// can be used both before installation to verify and when the installation failed for cleaning up
	// $report_only - if set to true only returns the leftovers found and does not removes them
	// $app - the AppConfig used for the installation
	// $db_params - the database parameters array used for the installation ('db_host', 'db_user', 'db_pass', 'db_port')
	// returns null if no leftovers are found or it is not report only or a text containing all the leftovers found
	public function detectLeftovers($report_only, $app, $db_params) {
		$leftovers = null;		
		
		// symbloic links leftovers
		foreach ($this->install_config['symlinks'] as $slink) {
			$link_items = explode(SYMLINK_SEPARATOR, $app->replaceTokensInString($slink));	
			if (is_file($link_items[1]) && (strpos($link_items[1], $app->get('BASE_DIR')) === false)) {
				if ($report_only) {
					$leftovers .= "   ".$link_items[1]." symbolic link exists".PHP_EOL;
				} else {
					logMessage(L_USER, "Removing symbolic link $link_items[1]");
					OsUtils::recursiveDelete($link_items[1]);
				}
			}
		}
		
		// database leftovers
		$verify = $this->detectDatabases($db_params);
		if (isset($verify)) {
			if(!$app->get('DB1_CREATE_NEW_DB'))
			{
				//do nothing
			}
			else if ($report_only) {
				$leftovers .= $verify;
			}  
			else {			
				$this->detectDatabases($db_params, true);
			}
		}
		
		// application leftovers
		if (is_dir($app->get('BASE_DIR')) && (($files = @scandir($dir)) && count($files) > 2)) {
			if ($report_only) {
				$leftovers .= "   Target directory ".$app->get('BASE_DIR')." already exists".PHP_EOL;
			} else {
				logMessage(L_USER, "killing sphinx daemon if running");
				$currentWorkingDir = getcwd();
				chdir($app->get('APP_DIR').'/app/plugins/sphinx_search/scripts/');
				@exec($app->get('BASE_DIR').'/app/plugins/sphinx_search/scripts/watch.stop.sh -u kaltura');
				logMessage(L_USER, "Stopping sphinx if running");
				@exec($app->get('BASE_DIR').'/app/plugins/sphinx_search/scripts/searchd.sh stop 2>&1', $output, $return_var);
				logMessage(L_USER, "Stopping the batch manager if running");
				chdir($app->get('APP_DIR').'/scripts/');
				@exec($app->get('BASE_DIR').'/app/scripts/serviceBatchMgr.sh stop 2>&1', $output, $return_var);
				chdir($currentWorkingDir);
				logMessage(L_USER, "Deleting ".$app->get('BASE_DIR'));
				OsUtils::recursiveDelete($app->get('BASE_DIR'));			
			}
		}
		
		return $leftovers;
	}	
	
	// installs the application according to the given parameters
	// $app - the AppConfig used for the installation
	// $db_params - the database parameters array used for the installation ('db_host', 'db_user', 'db_pass', 'db_port')	
	// returns null if the installation succeeded or an error text if it failed
	public function install(AppConfig $app, $db_params) {
		logMessage(L_USER, sprintf("Copying application files to %s", $app->get('BASE_DIR')));
		logMessage(L_USER, sprintf("current working dir is %s", getcwd()));
		if (!OsUtils::rsync('package/app/', $app->get('BASE_DIR'), "--exclude web/content")) {
			return "Failed to copy application files to target directory";
		}
		if ($app->get('DB1_CREATE_NEW_DB'))
		{
			if (!OsUtils::rsync("package/app/web/content", $app->get('WEB_DIR'))) {
				return "Failed to copy default content into /opt/kaltura/web";
			}
		}		

		$os_name = 	OsUtils::getOsName();
		$architecture = OsUtils::getSystemArchitecture();	
		logMessage(L_USER, "Copying binaries for $os_name $architecture");
		if (!OsUtils::fullCopy("package/bin/$os_name/$architecture", $app->get('BIN_DIR'))) {
			return "Failed to copy binaries for $os_name $architecture";
		}
		
		logMessage(L_USER, "Creating the uninstaller");
		if (!OsUtils::fullCopy('installer/uninstall.php', $app->get('BASE_DIR')."/uninstaller/")) {
			return "Failed to create the uninstaller";
		}
		//create uninstaller.ini with minimal definitions
		$app->saveUninstallerConfig();
		
		//OsUtils::logDir definition
		OsUtils::$logDir = $app->get('LOG_DIR');
		
		// if vmware installation copy configurator folders
		if ($app->get('KALTURA_PREINSTALLED')) {
			mkdir($app->get('BASE_DIR').'/installer', 0777, true);
			if (!OsUtils::rsync('installer/', $app->get('BASE_DIR').'/installer')) {
				return "Failed to copy installer files to target directory";
			}
			
			if (!OsUtils::fullCopy('configurator/', $app->get('BASE_DIR').'/installer')) {
				return "Failed to copy configurator files to target directory";
			}
			
			if (!OsUtils::fullCopy('configure.php', $app->get('BASE_DIR')."/installer/")) {
				return "Failed to copy configure.php file to targer directory";
			}		
		}
		
		logMessage(L_USER, "Replacing configuration tokens in files");
		foreach ($this->install_config['token_files'] as $file) {
			$replace_file = $app->replaceTokensInString($file);
			if (!$app->replaceTokensInFile($replace_file)) {
				return "Failed to replace tokens in $replace_file";
			}
		}		

		$this->changeDirsAndFilesPermissions($app);
		
		if((!$app->get('DB1_CREATE_NEW_DB')) && (DatabaseUtils::dbExists($db_params, $app->get('DB1_NAME')) === true))
		{		
			logMessage(L_USER, sprintf("Skipping '%s' database creation", $app->get('DB1_NAME')));
		}
		else 
		{
			$sql_files = parse_ini_file($app->get('BASE_DIR').APP_SQL_DIR.'create_kaltura_db.ini', true);
			logMessage(L_USER, sprintf("Creating and initializing '%s' database", $app->get('DB1_NAME')));
			if (!DatabaseUtils::createDb($db_params, $app->get('DB1_NAME'))) {
				return "Failed to create '".$app->get('DB1_NAME')."' database";
			}
			foreach ($sql_files['kaltura']['sql'] as $sql) {
				$sql_file = $app->get('BASE_DIR').APP_SQL_DIR.$sql;
				if (!DatabaseUtils::runScript($sql_file, $db_params, $app->get('DB1_NAME'))) {
					return "Failed running database script $sql_file";
				}
			}
		}
		if((!$app->get('DB1_CREATE_NEW_DB')) && (DatabaseUtils::dbExists($db_params, $app->get('SPHINX_DB_NAME')) === true))
		{		
			logMessage(L_USER, sprintf("Skipping '%s' database creation", $app->get('SPHINX_DB_NAME')));
		}
		else 
		{		
			logMessage(L_USER, sprintf("Creating and initializing '%s' database", $app->get('SPHINX_DB_NAME')));
			if (!DatabaseUtils::createDb($db_params, $app->get('SPHINX_DB_NAME'))) {
				return "Failed to create '".$app->get('SPHINX_DB_NAME')."' database";
			}
			foreach ($sql_files[$app->get('SPHINX_DB_NAME')]['sql'] as $sql) {
				$sql_file = $app->get('BASE_DIR').APP_SQL_DIR.$sql;
				if (!DatabaseUtils::runScript($sql_file, $db_params, $app->get('SPHINX_DB_NAME'))) {
					return "Failed running database script $sql_file";
				}
			}
		}
		if((!$app->get('DB1_CREATE_NEW_DB')) && (DatabaseUtils::dbExists($db_params, $app->get('DWH_DATABASE_NAME')) === true))
		{		
			logMessage(L_USER, sprintf("Skipping '%s' database creation", $app->get('DWH_DATABASE_NAME')));
		}
		else 
		{
			logMessage(L_USER, "Creating data warehouse");
			if (!OsUtils::execute(sprintf("%s/setup/dwh_setup.sh -h %s -P %s -u %s -p %s -d %s ", $app->get('DWH_DIR'), $app->get('DB1_HOST'), $app->get('DB1_PORT'), $app->get('DWH_USER'), $app->get('DWH_PASS'), $app->get('DWH_DIR')))) {		
				return "Failed running data warehouse initialization script";
			}
		}
		
		logMessage(L_USER, "Creating Dynamic Enums");
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/installPlugins.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
				logMessage(L_INFO, "Dynamic Enums created");
		} else {
			return "Failed to create dynamic enums";
		}
			
		logMessage(L_USER, "Create query cache triggers");
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/createQueryCacheTriggers.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
			logMessage(L_INFO, "sphinx Query Cache Triggers created");
		} else {
			return "Failed to create QueryCacheTriggers";
		}
		
		logMessage(L_USER, "Populate sphinx tables");
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/populateSphinxEntries.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
				logMessage(L_INFO, "sphinx entries log created");
		} else {
			return "Failed to populate sphinx log from entries";
		}
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/populateSphinxEntryDistributions.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
				logMessage(L_INFO, "sphinx content distribution log created");
		} else {
			return "Failed to populate sphinx log from content distribution";
		}
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/populateSphinxCuePoints.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
				logMessage(L_INFO, "sphinx cue points log created");
		} else {
			return "Failed to populate sphinx log from cue points";
		}
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/populateSphinxKusers.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
			logMessage(L_INFO, "sphinx Kusers log created");
		} else {
			return "Failed to populate sphinx log from Kusers";
		}
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/populateSphinxTags.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
			logMessage(L_INFO, "sphinx tags log created");
		} else {
			return "Failed to populate sphinx log from tags";
		}
		if (OsUtils::execute(sprintf("%s %s/deployment/base/scripts/populateSphinxCategories.php", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
			logMessage(L_INFO, "sphinx Categories log created");
		} else {
			return "Failed to populate sphinx log from categories";
		}

		$this->changeDirsAndFilesPermissions($app);
		
		$this->changeDirsAndFilesOwnerships($app);
		
		logMessage(L_USER, "Creating system symbolic links");
		foreach ($this->install_config['symlinks'] as $slink) {
			$link_items = explode(SYMLINK_SEPARATOR, $app->replaceTokensInString($slink));	
			if (symlink($link_items[0], $link_items[1])) {
				logMessage(L_INFO, "Created symbolic link $link_items[0] -> $link_items[1]");
			} else {
				logMessage(L_INFO, "Failed to create symbolic link from ". $link_items[0]." to ".$link_items[1].", retyring..");
				unlink($link_items[1]);
				symlink($link_items[0], $link_items[1]);
			}
		}
		
		//update uninstaller config
		$app->updateUninstallerConfig($this->install_config['symlinks']);
		
		if (strcasecmp($app->get('KALTURA_VERSION_TYPE'), K_CE_TYPE) == 0) {
			$app->simMafteach();
		}
	
		logMessage(L_USER, "Deploying uiconfs in order to configure the application");
		foreach ($this->install_config['uiconfs_2'] as $uiconfapp) {
			$to_deploy = $app->replaceTokensInString($uiconfapp);
			if (OsUtils::execute(sprintf("%s %s/deployment/uiconf/deploy_v2.php --ini=%s", $app->get('PHP_BIN'), $app->get('APP_DIR'), $to_deploy))) {
				logMessage(L_INFO, "Deployed uiconf $to_deploy");
			} else {
				return "Failed to deploy uiconf $to_deploy";
			}
		}
				
		logMessage(L_USER, "clear cache");
		if (!OsUtils::execute(sprintf("%s %s/scripts/clear_cache.php -y", $app->get('PHP_BIN'), $app->get('APP_DIR')))) {
			return "Failed clear cache";
		}
		
		logMessage(L_USER, "Running the generate script");
		$currentWorkingDir = getcwd();
		chdir($app->get('APP_DIR').'/generator');
		if (!OsUtils::execute($app->get('APP_DIR').'/generator/generate.sh')) {
			return "Failed running the generate script";
		}
		
		logMessage(L_USER, "Running the batch manager");
		chdir($app->get('APP_DIR').'/scripts/');
		if (!OsUtils::execute($app->get('APP_DIR').'/scripts/serviceBatchMgr.sh start')) {
			return "Failed running the batch manager";
		}
		chdir($currentWorkingDir);
		
		logMessage(L_USER, "Running the sphinx search daemon");
		print("Executing sphinx daemon \n");
		OsUtils::executeInBackground('nohup '.$app->get('APP_DIR').'/plugins/sphinx_search/scripts/watch.daemon.sh');
		OsUtils::executeInBackground('chkconfig sphinx_watch.sh on');
		
		//make sure crond is running.
		OsUtils::executeInBackground('chkconfig crond on');
		
		$this->changeDirsAndFilesPermissions($app);
		
		OsUtils::execute('cp /package/version.ini ' . $app->get('APP_DIR') . '/configurations/');
		
		return null;
	}
	
	// detects if there are databases leftovers
	// can be used both for verification and for dropping the databases
	// $db_params - the database parameters array used for the installation ('db_host', 'db_user', 'db_pass', 'db_port')
	// $should_drop - whether to drop the databases that are found or not (default - false) 
	// returns null if no leftovers are found or a text containing all the leftovers found
	private function detectDatabases($db_params, $should_drop=false) {
		$verify = null;
		foreach ($this->install_config['databases'] as $db) {
			$result = DatabaseUtils::dbExists($db_params, $db);
			
			if ($result === -1) {
				$verify .= "   Cannot verify if '$db' database exists".PHP_EOL;
			} else if ($result === true) {
				if (!$should_drop) {
					$verify .= "   '$db' database already exists ".PHP_EOL;
				} else {
					logMessage(L_USER, "Dropping '$db' database");
					DatabaseUtils::dropDb($db_params, $db);
				}
			}
		}
		return $verify;
	}	
	
	private function changeDirsAndFilesPermissions($app){
		logMessage(L_USER, "Changing permissions of directories and files");
		foreach ($this->install_config['chmod_items'] as $item) {
			$chmod_item = $app->replaceTokensInString($item);
			if (!OsUtils::chmod($chmod_item)) {
				return "Failed to change permissions for $chmod_item";
			}
		}
	}	
	
	private function changeDirsAndFilesOwnerships ($app) {
		logMessage(L_USER, "Changing ownerships of directories and files");
		foreach ($this->install_config['chown_items'] as $item) {
			$chmown_item = $app->replaceTokensInString($item);
			if (!OsUtils::chown($chmown_item)) {
				return "Failed to change ownership for $chmown_item";
			}
		}
	}
	
	public function finalizeInstallation ($app)
	{
		$this->configureSSL ($app);
	}
	
	private function configureSSL ($app)
	{
		if (strcasecmp($app->get('WORK_MODE'), 'https') === 0 )
		{
			echo ('***********************************************');
			@exec($app->get('HTTPD_BIN') . ' -M 2>&1', $loadedModules, $exitCode);
			if ($exitCode !== 0) {
				logMessage(L_USER, "Unable to get list of loaded apache modules. Cannot enable SSL configuration for this installation. Please investigate the issue.");
				return;
			}
			array_walk($loadedModules, create_function('&$str', '$str = trim($str);'));
			$found = false;
			foreach ($loadedModules as $loadedModule)
			{
				if (strpos($loadedModule,'ssl_module') === 0) {
					$found = true;
					break;
				}		
			}
			if (!$found)
			{
				logMessage(L_USER, "Required SSL module is missing. Cannot enable SSL configuration for this installation. Please investigate the issue.");
				return;
			}
		
			symlink($app->get('APP_DIR'). "/configurations/apache/my_kaltura.ssl.conf", "/etc/httpd/conf.d/my_kaltura.ssl.conf");
		}		
	}
	
	private function extractKCWUiconfIds ($app)
	{
		$uiconfIds = array();
		$log = file_get_contents($app->get('LOG_DIR') . "/instlBkgrndRun.log");
		preg_match_all("/creating uiconf \[\d+\] for widget \w+ with default values \( \/flash\/kcw/", $log, $matches);
		foreach ($matches[0] as $match)
		{
			preg_match("/\[\d+\]/", $match, $bracketedId);
			$id = str_replace(array ('[' , ']'), array ('', ''), $bracketedId[0]);
			$uiconfIds[] = $id;
		}
		
		return $uiconfIds;
	}
}