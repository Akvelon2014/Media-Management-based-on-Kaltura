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

include_once('installer/DatabaseUtils.class.php');
include_once('installer/OsUtils.class.php');
include_once('installer/UserInput.class.php');
include_once('installer/Log.php');
include_once('installer/InstallReport.class.php');
include_once('installer/AppConfig.class.php');
include_once('installer/Installer.class.php');
include_once('installer/InputValidator.class.php');
include_once('installer/phpmailer/class.phpmailer.php');
include_once('lib/utils.php');

// constants
define("K_TM_TYPE", "TM");
define("K_CE_TYPE", "CE");
define("FILE_INSTALL_SEQ_ID", "install_seq"); // this file is used to store a sequence of installations

// installation might take a few minutes
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
ini_set('max_input_time ', 0);

// start the log
startLog("update_config_files_log_".date("d.m.Y_H.i.s"));
logMessage(L_INFO, "Update started");

// variables
$silentRun = false;
if($argc > 1 && $argv[1] == '-s') $silentRun = true;
$cleanupIfFail = true;
if($argc > 1 && $argv[1] == '-c') {
	$cleanupIfFail = false;
	$silentRun = true;
} 
$app = new AppConfig();
$installer = new Installer();
$user = new UserInput();
$db_params = array();

// set the installation ids
$app->set('INSTALLATION_UID', uniqid("IID")); // unique id per installation

// load or create installation sequence id
if (is_file(FILE_INSTALL_SEQ_ID)) {
	$install_seq = @file_get_contents(FILE_INSTALL_SEQ_ID);
	$app->set('INSTALLATION_SEQUENCE_UID', $install_seq);
} else {
	$install_seq = uniqid("ISEQID"); // unique id per a set of installations
	$app->set('INSTALLATION_SEQUENCE_UID', $install_seq); 
	file_put_contents(FILE_INSTALL_SEQ_ID, $install_seq);
}


// read package version
$version = parse_ini_file('package/version.ini');
logMessage(L_INFO, "Installing Kaltura ".$version['type'].' '.$version['number']);
$app->set('KALTURA_VERSION', 'Kaltura '.$version['type'].' '.$version['number']);
$app->set('KALTURA_PREINSTALLED', $version['preinstalled']);
$app->set('KALTURA_VERSION_TYPE', $version['type']);
if (strcasecmp($app->get('KALTURA_VERSION_TYPE'), K_TM_TYPE) !== 0) {
	$hello_message = "Thank you for installing Media Management based on Kaltura";
	$report_message = "If you wish, please provide your email address so that we can offer you future assistance (leave empty to pass)";
	$report_error_message = "Email must be in a valid email format";
	$report_validator = InputValidator::createEmailValidator(true);		
	$fail_action = "For assistance, please contact us at https://github.com/Akvelon2014/Media-Management-based-on-Kaltura/issues";
} else {
	$hello_message = "Thank you for installing Media Management based on Kaltura";
	$report_message = "Please provide the name of your company or organization";
	$report_error_message = "Name cannot be empty";
	$report_validator = InputValidator::createNonEmptyValidator();	
	$fail_action = "For assistance, please contact us at https://github.com/Akvelon2014/Media-Management-based-on-Kaltura/issues";
}

// start user interaction
@system('clear');
logMessage(L_USER, $hello_message);
echo PHP_EOL;

// If previous installation found and the user wants to use it
if ($user->hasInput()){ 
	if(($silentRun) || ($user->getTrueFalse(null, "do you want to use the input you provided during you last update?", 'y'))) {
		$user->loadInput();
	}
}

// if user wants or have to report
if ($result = ((strcasecmp($app->get('KALTURA_VERSION_TYPE'), K_TM_TYPE) == 0) || 
	($user->getTrueFalse('ASK_TO_REPORT', "In order to improve Kaltura Community Edition, we would like your permission to send system data to Kaltura.\nThis information will be used exclusively for improving our software and our service quality. I agree", 'y')))) {	
	$email = $user->getInput('REPORT_MAIL', $report_message, $report_error_message, $report_validator, null);
	$app->set('REPORT_ADMIN_EMAIL', $email);
	$app->set('TRACK_KDPWRAPPER','true');
	$app->set('USAGE_TRACKING_OPTIN','true');	
	$report = new InstallReport($email, $app->get('KALTURA_VERSION'), $app->get('INSTALLATION_SEQUENCE_UID'), $app->get('INSTALLATION_UID'));
	$report->reportInstallationStart();
} else {
	$app->set('REPORT_ADMIN_EMAIL', "");
	$app->set('TRACK_KDPWRAPPER','false');
	$app->set('USAGE_TRACKING_OPTIN','false');
}


// set to replace passwords on first activiation if this installation is preinstalled
$app->set('REPLACE_PASSWORDS',$app->get('KALTURA_PREINSTALLED'));

// allow ui conf tab only for CE installation
if (strcasecmp($app->get('KALTURA_VERSION_TYPE'), K_TM_TYPE) !== 0) 
	$app->set('UICONF_TAB_ACCESS', 'SYSTEM_ADMIN_BATCH_CONTROL');

if (!OsUtils::verifyOS()) {
	die(1);
}

if (!extension_loaded('mysqli')) {
	die(1);
}

// get the user input if needed
if ($user->isInputLoaded()) {
	logMessage(L_USER, "Skipping user input, previous installation input will be used.");	
} else {
	$user->getApplicationInput();
}

// get from kConf.php the latest versions of kmc , clipapp and HTML5
$kconf = file_get_contents("/opt/kaltura/app/configurations/base.ini");
$latestVersions = array();
$latestVersions["KMC_VERSION"] = getVersionFromKconf($kconf,"kmc_version");
$latestVersions["CLIPAPP_VERSION"] = getVersionFromKconf($kconf,"clipapp_version");
$latestVersions["HTML5_VERSION"] = getVersionFromKconf($kconf,"html5_version");
logMessage(L_USER, 'HTML5_VERSION is: '. $latestVersions["HTML5_VERSION"]);

// init the application configuration
$app->initFromUserInput(array_merge((array)$user->getAll(), (array)$latestVersions));
$db_params['db_host'] = $app->get('DB1_HOST');
$db_params['db_port'] = $app->get('DB1_PORT');
$db_params['db_user'] = $app->get('DB1_USER');
$db_params['db_pass'] = $app->get('DB1_PASS');

// verify prerequisites
echo PHP_EOL;
logMessage(L_USER, "Verifing prerequisites");
@exec(sprintf("%s installer/Prerequisites.php '%s' '%s' '%s' '%s' '%s' 2>&1", $app->get("PHP_BIN"), $app->get("HTTPD_BIN"), $db_params['db_host'], $db_params['db_port'], $db_params['db_user'], $db_params['db_pass']), $output, $exit_value);
if ($exit_value !== 0) {
	$description = "   ".implode("\n   ", $output)."\n";
	echo PHP_EOL;
	logMessage(L_USER, "One or more prerequisites required to install Kaltura failed:".
					   $description.
					   "Please resolve the issues and run the installation again.");
	die(1);
}

// last chance to stop 
echo PHP_EOL;
if ((!$silentRun) && (!$user->getTrueFalse('', "Start update now?", 'y'))) {
	echo "Bye".PHP_EOL;
	die(1);	
}

// run the installation
$install_output = $installer->update($app, $db_params);
if ($install_output !== null) {
	logMessage(L_USER, "Update failed.". $install_output. $fail_action);
}

echo PHP_EOL;
logMessage(L_USER, "Update Completed Successfully.\n");

die(0);

