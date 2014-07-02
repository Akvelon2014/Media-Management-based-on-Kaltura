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

/* 
* This class is a static class with database utility functions
*/
class DatabaseUtils
{	
	/**
	 * Connect to mySQL database
	 * @param mysqli $link mysqli link
	 * @param array $db_params db parameters array 'db_host', 'db_user', 'db_pass', 'db_port'
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function connect(&$link, $db_params, $db_name)
	{
		// set mysqli to connect via tcp
		if ($db_params['db_host'] == 'localhost') {
			$db_params['db_host'] = '127.0.0.1';
		}
		if (trim($db_params['db_pass']) == '') {
			$db_params['db_pass'] = null;
		}
		$link = @mysqli_init();
		$result = @mysqli_real_connect($link, $db_params['db_host'], $db_params['db_user'], $db_params['db_pass'], $db_name, $db_params['db_port']);
		if (!$result) {
			logMessage(L_ERROR, sprintf("Cannot connect to db: %s, %s, %s", $db_params['db_host'], $db_params['db_user'], $link->error));
			return false;
		}
		return true;
	}
		
	/**
	 * Execute a mySQL query or multi queries
	 * @param string $query mySQL query, or multiple queries seperated by a ';'
	 * @param array $db_params db parameters array 'db_host', 'db_user', 'db_pass', 'db_port'
	 * @param string $db_name database name
	 * @param mysqli $link mysqli link
	 * @return true on success, false otherwise
	 */
	public static function executeQuery($query, $db_params, $db_name, $link = null)
	{
		// connect if not yet connected
		if (!$link && !self::connect($link, $db_params, $db_name)) {
			return false;
		}
		
		// use desired database
		else if (isset($db_name) && !mysqli_select_db($link, $db_name)) {
			logMessage(L_ERROR, "Cannot execute query: could not find the db: $db");
			return false;
		}
		
		// execute all queries
		if (!mysqli_multi_query($link, $query) || $link->error != '') {
			logMessage(L_ERROR, "Cannot execute query: error with query: $query, error: ".$link->error);
			return false;		
		}
		  
		// flush
		while (mysqli_more_results($link) && mysqli_next_result($link)) {
			$discard = mysqli_store_result($link);			
		}
		$link->commit();
		
		return true;
	}
		
	/**
	 * Create a new mySQL database
	 * @param array $db_params db parameters array 'db_host', 'db_user', 'db_pass', 'db_port'
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function createDb($db_params, $db_name)
	{
		logMessage(L_INFO, "Creating database $db_name");	
		$create_db_query = "CREATE DATABASE $db_name;";
		return self::executeQuery($create_db_query, $db_params, null);
	}
		
	/**
	 * Drop a mySQL database
	 * @param array $db_params db parameters array 'db_host', 'db_user', 'db_pass', 'db_port'
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function dropDb($db_params, $db_name)
	{
		logMessage(L_INFO, "Dropping database $db_name");	
		$drop_db_query = "DROP DATABASE $db_name;";
		return self::executeQuery($drop_db_query, $db_params, null);
	}
		
	/**
	 * Check if a mySQL database exists
	 * @param array $db_params db parameters array 'db_host', 'db_user', 'db_pass', 'db_port'
	 * @param string $db_name database name
	 * @return true/false according to existence
	 */
	public static function dbExists($db_params, $db_name)
	{
		if (!self::connect($link, $db_params, null)) {
			logMessage(L_ERROR, "Could not database $db_name: could not connect to host");	
			return -1;
		}
		return mysqli_select_db($link, $db_name);
	}
			
	/**
	 * Execute mySQL queries from a given sql file
	 * @param string $file sql file
	 * @param array $db_params db parameters array 'db_host', 'db_user', 'db_pass', 'db_port'
	 * @param string $db_name database name
	 * @return true on success, false otherwise
	 */
	public static function runScript($file, $db_params, $db_name) {		
		if (!is_file($file)) {
			logMessage(L_ERROR, "Could not run script: script not found $file");	
			return false;
		}
		
		if (empty($db_params['db_pass'])) {		
			$cmd = sprintf("mysql -h%s -u'%s' -P'%s' '%s' < %s", $db_params['db_host'], $db_params['db_user'], $db_params['db_port'], $db_name, $file);
		} else {
			$cmd = sprintf("mysql -h'%s' -u'%s' -p'%s' -P'%s' '%s' < %s", $db_params['db_host'], $db_params['db_user'], $db_params['db_pass'], $db_params['db_port'], $db_name, $file);
		}
		logMessage(L_INFO, "Executing $cmd");
		@exec($cmd . ' 2>&1', $output, $return_var);
		if ($return_var === 0) {
			return true;
		} else {
			logMessage(L_ERROR, "Executing command failed: ".implode("\n",$output));	
			return false;
		}
	}
}
