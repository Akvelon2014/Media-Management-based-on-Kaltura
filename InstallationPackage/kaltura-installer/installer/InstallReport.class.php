<?php

define('INSTALL_REPORT_URL', 'http://kalstats.kaltura.com/index.php/events/installation_event');

/*
* This class handles reporting to Kaltura during the installation if the user allows
*/
class InstallReport {
	private $report_parameters = array();
	private $report_post_parameters = array();
	
	// create a new install report class
	// $email - the user email
	// $package_version - the full version of the package
	// $install_seq_id - the id of the installation sequence
	// $install_id - the id of the current installation
	public function __construct($email, $package_version, $install_seq_id, $install_id){	
		$this->report_parameters['email'] = $email;
		$this->report_parameters['client_type'] = 'PHP CLI';
		$this->report_parameters['server_ip'] = null;
		$this->report_parameters['host_name'] = null;
		$this->report_parameters['operating_system'] = php_uname('s');
		$this->report_parameters['os_disribution'] = OsUtils::getOsLsb();
		$this->report_parameters['architecture'] = php_uname('m');
		$this->report_parameters['php_version'] = phpversion();
		$this->report_parameters['package_version'] = $package_version;
		$this->report_parameters['install_id'] = $install_id;
		$this->report_parameters['install_seq_id'] = $install_seq_id;
	}

	// send an installation start event report
	public function reportInstallationStart() {
		$this->report_parameters['step'] = "Install Started";
		$this->sendReport($this->report_parameters, $this->report_post_parameters);
	}
	
	// send an installation failed event report
	public function reportInstallationFailed($failure_message) {
		$this->report_parameters['step'] = "Install Failed";
		$this->report_post_parameters['description'] = $failure_message;
		$this->sendReport($this->report_parameters, $this->report_post_parameters);
	}
	
	// send an installation success event report
	public function reportInstallationSuccess() {
		$this->report_parameters['step'] = "Install Success";		
		$this->sendReport($this->report_parameters, $this->report_post_parameters);
	}	

	// send an event report with the given $get_parameters and $post_parameters
	private function sendReport($get_parameters, $post_parameters) {
		if (extension_loaded("curl")) {		
			// create a new cURL resource
			$ch = curl_init();		
			$url = INSTALL_REPORT_URL . '?' . http_build_query($get_parameters);
			
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			//curl_setopt($ch, CURLOPT_HTTPGET, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_parameters);
			
			// grab URL and pass it to the browser
			$result = curl_exec($ch);
			if (!$result) {
				logMessage(L_ERROR, 'Failed sending install report '.curl_error($ch));
			} else {
				logMessage(L_INFO, 'Sending install report');
			}
			
			// close cURL resource, and free up system resources
			curl_close($ch);
		}
	}
}