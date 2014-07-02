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

define("FILE_USER_INPUT", "user_input.ini"); // this file contains the saved user input

/*
* This class handles all the user input
*/
class UserInput
{
	private $user_input;
	private $input_loaded = false;

	// return true if user input is already loaded
	public function hasInput() {
		return is_file(FILE_USER_INPUT);
	}
	
	// load input from user input file
	public function loadInput() {
		$this->user_input = parse_ini_file(FILE_USER_INPUT, true);
		$this->input_loaded = true;
	}
	
	// save the user input to input file
	public function saveInput() {
		OsUtils::writeConfigToFile($this->user_input, FILE_USER_INPUT);
	}		
	
	// get the input for the given $key
	public function get($key) {
		return $this->user_input[$key];
	}
	
	// set the input $value for the given $key
	public function set($key, $value) {
		return $this->user_input[$key] = $value;
	}

	// unset the input for the given $key
	public function unsetInput($key) {
		unset($this->user_input[$key]);
	}
	
	// returns the user input array
	public function getAll() {
		return $this->user_input;
	}
	
	// returns true if user input is loaded
	public function isInputLoaded() {
		return $this->input_loaded;
	}
	
	// gets input from the user and returns it
	// if $key was already loaded from config it will be taked from there and user will not have to insert
	// $request text - text to show the user
	// $not_valid_text - text to show the user if the input is invalid (according to the validator)
	// $validator - the InputValidator to user (default is null, no validation)
	// $default - the default value (default's default is '' :))
	public function getInput($key, $request_text, $not_valid_text, $validator = null, $default = '') {
		if (isset($key) && isset($this->user_input[$key])) {
			return $this->user_input[$key];
		}
		
		if (isset($validator) && !empty($default)) $validator->emptyIsValid = true;
		
		logMessage(L_USER, $request_text);
			
		$inputOk = false;
		while (!$inputOk) {
			echo '> ';
			$input = trim(fgets(STDIN));
			logMessage(L_INFO, "User input is $input");
			
			if (isset($validator) && !$validator->validateInput($input)) {
				logMessage(L_USER, $not_valid_text);
			} else {			
				$inputOk = true;
				echo PHP_EOL;				
				if (empty($input) && !empty($default)) {
					$input = $default;
					logMessage(L_INFO, "Using default value: $default");
				}	
			}				
		}
		
		if (isset($key)) $this->user_input[$key] = $input;
  		return $input;	
	}
	
	// Get a y/n input from the user
	// if $key was already loaded from config it will be taken from there and user will not have to insert
	// $request text - text to show the user	
	// $default - the default value (show be 'y'/'n')
	public function getTrueFalse($key, $request_text, $default) {	
		if (isset($key) && isset($this->user_input[$key])) {
			return $this->user_input[$key];
		}			
		
		if ((strcasecmp('y', $default) === 0) || (strcasecmp('yes', $default) === 0)) {
			$request_text .= ' (Y/n)';
		} else {
			$request_text .= ' (y/N)';
		}
		
		$validator = InputValidator::createYesNoValidator();
		$input = $this->getInput(null, $request_text, "Input is not valid", $validator, $default);
		$retrunVal = ((strcasecmp('y',$input) === 0) || (strcasecmp('yes',$input) === 0));
		
		if (isset($key)) $this->user_input[$key] = $retrunVal;
		return $retrunVal;		
	}
	
	// get all the user input for the installation
	public function getApplicationInput() {				
		$httpd_bin_found = OsUtils::findBinary(array('apachectl', 'apache2ctl'));					
		if (!empty($httpd_bin_found)) {
			$httpd_bin_message = "The following apachectl script has been detected: $httpd_bin_found. Do you want to use this script to run your Kaltura application? Leave empty to use or provide a pathname to an alternative apachectl script on your server.";
			$httpd_error_message = "Invalid pathname for apachectl script, leave empty to use $httpd_bin_found or enter an alternative apachectl path";
		} else {
			$httpd_bin_message = "Installation could not automatically detect any apachectl script. Please provide a pathname to the apachectl script on your server.";
			$httpd_error_message = "Invalid pathname for apachectl script, please enter the apachectl pathname again";
		}
		
		$php_bin_found = OsUtils::findBinary('php');
		if (!empty($php_bin_found)) {
			$php_bin_message = "The following PHP binary has been detected: $php_bin_found. Do you want to use this script to run your Kaltura application? Leave empty to use or provide a pathname to an alternative PHP binary on your server.";
			$php_error_message = "Invalid pathname for PHP binary, leave empty to use $php_bin_found or enter an alternative PHP path";
		} else {
			$php_bin_message = "Installation could not automatically detect any PHP binary. Please provide a pathname to the PHP binary on your server.";
			$php_error_message = "Invalid pathname for PHP binary, please enter the PHP pathname again";
		}

		logMessage(L_USER, "Please provide the following information:");
		echo PHP_EOL;
		
		$this->set('HTTPD_BIN', $httpd_bin_found);
		$this->set('PHP_BIN', $php_bin_found);
		
		$this->getInput('TIME_ZONE', 
						"Default time zone for Kaltura application (leave empty to use system timezone: ". date_default_timezone_get()." )",
						"Timezone must be a valid timezone, please enter again", 
						InputValidator::createTimezoneValidator(), 
						date_default_timezone_get());
		$this->getInput('BASE_DIR', 
						"Full target directory path for Kaltura application (leave empty for /opt/kaltura)",
						"Target directory must be a valid directory path, please enter again", 
						InputValidator::createDirectoryValidator(), 
						'/opt/kaltura');
		$this->getInput('KALTURA_FULL_VIRTUAL_HOST_NAME', 
						"Please enter the domain name/virtual hostname that will be used for the Kaltura server (without http://)", 
						'Must be a valid hostname or ip, please enter again', 
						InputValidator::createHostValidator(), 
						null);
		$this->getInput('DB1_HOST', 
						"Database host (leave empty for 'localhost')", 
						"Must be a valid hostname or ip, please enter again (leave empty for 'localhost')", 
						InputValidator::createHostValidator(), 
						'localhost');
		$this->getInput('DB1_PORT', 
						"Database port (leave empty for '3306')", 
						"Must be a valid port (1-65535), please enter again (leave empty for '3306')", 
						InputValidator::createRangeValidator(1, 65535), 
						'3306');
		$this->set('DB1_NAME','kaltura'); // currently we do not support getting the DB name from the user because of the DWH implementation
		$this->getInput('DB1_USER', 
						"Database username (with create & write privileges)", 
						"Database username cannot be empty, please enter again", 
						InputValidator::createNonEmptyValidator(), 
						null);
		$this->getInput('DB1_PASS', 
						"Database password (leave empty for no password)", 
						null, 
						null, 
						null);

		if (((strcasecmp($this->get('DB1_HOST'), 'localhost') === 0) || (strcasecmp($this->get('DB1_HOST'), '127.0.0.1') === 0)) && empty($this->user_input['DB1_PASS'])) {
			$this->getInput('DB1_SET_PASSWORD',
				"Empty password is not secure. Would you like to set password now? (Y/n)",
				"Input is not valid",
				InputValidator::createYesNoValidator(),
				"yes");

			if ((strcasecmp($this->get('DB1_SET_PASSWORD'), 'y') === 0) || (strcasecmp($this->get('DB1_SET_PASSWORD'), 'yes') === 0)) {

				$user = $this->get('DB1_USER');
				$newPassword = '';
				$continue = true;
				while ($continue) {
					$this->getInput('DB1_NEW_PASSWORD',
						"Enter new password",
						"Password cannot be empty, please enter again",
						InputValidator::createNonEmptyValidator(),
						null);

					$this->getInput('DB1_NEW_PASSWORD_CONFIRM',
						"Confirm new password",
						"Password cannot be empty, please enter again",
						InputValidator::createNonEmptyValidator(),
						null);

					if ($this->get('DB1_NEW_PASSWORD') === $this->get('DB1_NEW_PASSWORD_CONFIRM')) {
						$newPassword = $this->get('DB1_NEW_PASSWORD');
						$continue = false;
					}
					else {
						echo "The passwords that you entered do not match.";
						echo PHP_EOL;
					}
					$this->unsetInput('DB1_NEW_PASSWORD');
					$this->unsetInput('DB1_NEW_PASSWORD_CONFIRM');
				}

				shell_exec("sudo mysqladmin -u $user password '$newPassword'");

				$this->set('DB1_PASS', $newPassword);
			}
			$this->unsetInput('DB1_SET_PASSWORD');
		}

		$this->getInput('DB1_CREATE_NEW_DB', 
						"Would you like to create a new kaltura database or use an existing one? (Y/n)",
						"Input is not valid", 
						InputValidator::createYesNoValidator(), 
						"yes");

		$this->set('XYMON_URL', null);

		$this->getInput('SPHINX_DB_HOST', 
						"Sphinx host (leave empty if Sphinx is running on this machine).", 
						null, 
						InputValidator::createHostValidator(), 
						'127.0.0.1');
						
		$this->getInput('WORK_MODE', 
						"Work mode - enter http/https (http)",
						"Input is not valid",
						InputValidator::createWorkModeValidator(),
						'http');

		$this->getInput('ADMIN_CONSOLE_ADMIN_MAIL',
									"Your primary system administrator email address",
									"Email must be in a valid email format, please enter again",
									InputValidator::createEmailValidator(false),
									null);

		$this->getInput('ADMIN_CONSOLE_PASSWORD',
							"The password you want to set for your primary administrator",
							"Password should not be empty and should not contain whitespaces, please enter again",
							InputValidator::createNoWhitespaceValidator(),
							null);

		$this->saveInput();
	}
}
