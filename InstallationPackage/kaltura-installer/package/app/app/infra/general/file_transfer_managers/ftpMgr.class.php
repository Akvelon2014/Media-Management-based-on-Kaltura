<?php
/**
 * Extends the 'kFileTransferMgr' class & implements a file transfer manager using the FTP protocol.
 * For additional comments please look at the 'kFileTransferMgr' class.
 * 
 * @package infra
 * @subpackage Storage
 */
class ftpMgr extends kFileTransferMgr
{

	// instances of this class should be created usign the 'getInstance' of the 'kFileTransferMgr' class
	protected function __construct()
	{
		// do nothing
	}


	/**********************************************************************/
	/* Implementation of abstract functions from class 'kFileTransferMgr' */
	/**********************************************************************/

	// ftp connect to server:port
	protected function doConnect($ftp_server, &$ftp_port)
	{
		// try connecting to server
		if (!$ftp_port || $ftp_port == 0) {
			$ftp_port = 21;
		}
		return ftp_connect($ftp_server, $ftp_port);
	}


	// login to an existing connection with given user/pass
	protected function doLogin($ftp_user, $ftp_pass, $ftp_passive_mode = TRUE)
	{
		// try to login
		$res = ftp_login($this->getConnection(), $ftp_user, $ftp_pass);
		if ($res) {
			// set FTP passive mode
			ftp_pasv($this->getConnection() , $ftp_passive_mode);
		}
		return $res;
	}


	// login using a public key - not supported in FTP
	protected function doLoginPubKey($user, $pubKeyFile, $privKeyFile, $passphrase = null)
	{
		return false; // NOT SUPPORTED
	}


	// upload a file to the server (ftp_mode is irrelevant
	protected function doPutFile ($remote_file,  $local_file, $ftp_mode, $http_field_name = null, $http_file_name = null)
	{
		$remote_file = ltrim($remote_file,'/');
		// try to upload file
		return ftp_put( $this->connection_id ,  $remote_file ,  $local_file ,  $ftp_mode);
	}


	// download a file from the server (ftp_mode is irrelevant)
	protected function doGetFile ($remote_file, $local_file, $ftp_mode)
	{
		// try to download file
		$remote_file = ltrim($remote_file,'/');
		return ftp_get($this->getConnection(), $local_file, $remote_file, $ftp_mode);
	}


	// create a new directory on the server
	protected function doMkDir ($remote_path)
	{
		$remote_path = ltrim($remote_path,'/');
		// try to make the new directory
		return ftp_mkdir($this->getConnection(), $remote_path);
	}


	// chmod to the given remote file
	protected function doChmod ($remote_file, $chmod_code)
	{
		// try to chmod
		$remote_file = ltrim($remote_file,'/');
		$chmod_code = octdec ( str_pad ( $chmod_code, 4, '0', STR_PAD_LEFT ) );
		$chmod_code = (int) $chmod_code;
		return ftp_chmod($this->getConnection(), $chmod_code, $remote_file);
	}


	// check if the given file/dir exists on the server
	protected function doFileExists($remote_file)
	{
		$remote_file = ltrim($remote_file,'/');
		// check if exists as file
		if (ftp_size($this->getConnection(), $remote_file) != -1) {
			return true; // file exists
		}

		// check if exists as dir
		$pwd = ftp_pwd($this->getConnection());
		if (ftp_chdir($this->getConnection(), $remote_file)) {
			ftp_chdir($this->getConnection(), $pwd);
			return true; // dir exists
		}

		// does not exist
		return false;
	}

	// return the current working directory
	protected function doPwd()
	{
		return ftp_pwd($this->getConnection());
	}

	// delete a file and return true/false according to success
	protected function doDelFile ($remote_file)
	{
	    $remote_file = ltrim($remote_file,'/');
		return ftp_delete($this->getConnection(), $remote_file);
	}

	// delete a directory and return true/false according to success
	protected function doDelDir ($remote_path)
	{
	    $remote_path = ltrim($remote_path,'/');
		$handle = $this->getConnection();
		if (!(ftp_rmdir($handle, $remote_path) || ftp_delete($handle, $remote_path))) {
			$list = ftp_nlist($handle, $remote_path);
			if (!empty($list)) {
				foreach($list as $value) {
					$this->doDelDir($value);
				}
			}
			return ftp_rmdir($handle, $remote_path);
		}
		return true;
	}

	protected function doList ($remoteDir)
	{
	    $remoteDir = ltrim($remoteDir,'/');
		$tempList = ftp_nlist($this->getConnection(), $remoteDir);
		$fileList = array();
		$remoteDir = trim($remoteDir, '/');
		$remoteDir = str_replace('/', '\/', $remoteDir);
		foreach ($tempList as $tempFile)
		{
		    $tempFile = trim($tempFile, '/');
		    $fileName = preg_replace('/^'.$remoteDir.'/', '', $tempFile);
		    $fileList[] = ltrim($fileName, '/');
		}
		return $fileList;		
	}
	
	protected function doFileSize($remote_file)
	{
	    $remote_file = ltrim($remote_file,'/');
//	    $size = ftp_size($this->getConnection(), $remote_file);
//		KalturaLog::debug("size [" . print_r($size, true) . "]");
//	    if($size > 0)
//	    	return $size;
	    	
	    $filesInfo = ftp_rawlist($this->getConnection(), dirname($remote_file));
		KalturaLog::debug("ftp rawlist [" . print_r($filesInfo, true) . "]");
	    
	    // -rw-r--r-- 1 kaltura kaltura 1876084736 Oct 31 14:31 1615.mpeg
	    $regexUnix = '^(?P<permissions>[-drwx]{10})\s+(?P<number>\d{1})\s+(?P<owner>[\d\w\-]+)\s+(?P<group>[\d\w\-]+)\s+(?P<fileSize>\d*)\s+(?P<month>\w{3})\s+(?P<day>\d{1,2})\s+((?P<hour>\d{2}):(?P<minute>\d{2})|(?P<year>\d{4}))\s+(?P<file>.+)\s*$';
	    
		// 08/08/2011  08:52 PM               174 .buildpath
		// 08/08/2011  08:52 PM               706 .project
		// 08/08/2011  08:52 PM    <DIR>          .settings
	    $regexWindows = '^(?P<month>\d{2})-(?P<day>\d{2})-(?P<year>\d{2})\s+(?P<hour>\d{2}):(?P<minute>\d{2})(?P<apm>AM|PM)\s+(?P<size>\d+|<DIR>)\s+(?P<file>.+)\s*$';
	    foreach($filesInfo as $fileInfo)
	    {
	    	$matches = null;
	    	if(!preg_match("/$regexUnix/", $fileInfo, $matches))
	    	{
	    		KalturaLog::err("Unix regex does not match ftp rawlist output [$fileInfo]");
				if(!preg_match("/$regexWindows/", $fileInfo, $matches))
				{
					KalturaLog::err("Windows regex does not match ftp rawlist output [$fileInfo]");
					continue;
				}
	    	}
	    	
	    	if($matches['file'] == basename($remote_file))
	    		return $matches['fileSize'];
	    }
	    return false;
	}
	
	protected function doModificationTime($remote_file)
	{
	    $remote_file = ltrim($remote_file,'/');
	    $modificationTime = ftp_mdtm($this->getConnection(), $remote_file);
	    if ($modificationTime < 0) {
	        return null;
	    }
	    return $modificationTime;
	}	


	/*******************/
	/* Other functions */
	/*******************/

	// closes the FTP connection.
	public function __destruct( )
	{
		// close the connection
		if ($this->getConnection()) {
			ftp_close($this->getConnection());
		}
	}

}
