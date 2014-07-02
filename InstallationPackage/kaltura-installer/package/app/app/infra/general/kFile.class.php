<?php
/**
 * @package infra
 * @subpackage Storage
 */
class kFile
{
	/**
	 * Returns directory $path contents as an array of :
	 *  array[0] = name
	 *  array[1] = type (dir/file)
	 *  array[2] = filesize
	 * @param string $path
	 * @param string $pathPrefix
	 */
	public static function listDir($path, $pathPrefix = '')
	{
		$fileList = array();
		$path = str_ireplace(DIRECTORY_SEPARATOR, '/', $path);
		$handle = opendir($path);
		if ($handle)
		{
		    while (false !== ($file = readdir($handle)))
		    {
		    	if ($file != '.' && $file != '..')
		    	{
		    		$fullPath = $path.'/'.$file;
		    		$tmpPrefix = $pathPrefix.$file;
		    		
			    	if (is_dir($fullPath))
			    	{
			    		$tmpPrefix = $tmpPrefix.'/';
			    		$fileList[] = array($tmpPrefix, 'dir', kFile::fileSize($fullPath));
			    		$fileList = array_merge($fileList, kFile::listDir($fullPath, $tmpPrefix));
			    	}	
			    	else
			    	{
			    		$fileList[] = array($tmpPrefix, 'file', kFile::fileSize($fullPath));
			    	}	    	
		    	}
		    }
		    closedir($handle);
		}
		return $fileList;
	}
	
	/**
	 * @param string $filename - path to file
	 * @return float
	 */
	static public function fileSize($filename)
	{
		if(PHP_INT_SIZE >= 8)
			return filesize($filename);
			
		$filename = str_replace('\\', '/', $filename);

		$url = "file://localhost/$filename";

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$headers = curl_exec($ch);
		if(!$headers)
			KalturaLog::err('Curl error: ' . curl_error($ch));
		curl_close($ch);
		
		if(!$headers)
			return false;
		       
		if (preg_match('/Content-Length: (\d+)/', $headers, $matches))
			return floatval($matches[1]);
			
		return false;	
	}
	
	// TODO - implement recursion
	static public function dirList($directory, $return_directory_as_prefix = true, $should_recurse = false)
	{
		// create an array to hold directory list
		$results = array();
		
		// create a handler for the directory
		$handler = @opendir($directory);
		if(! $handler)
			KalturaLog::info("dirList $directory does not exist");
		
		// keep going until all files in directory have been read
		while($file = readdir($handler))
		{
			
			// if $file isn't this directory or its parent,
			// add it to the results array
			if($file != '.' && $file != '..')
			{
				$results[] = ($return_directory_as_prefix ? $directory . "/" : "") . $file;
			}
		}
		
		// tidy up: close the handler
		closedir($handler);
		
		// done!
		return $results;
	}
	
	/*
	 * Besure to limit the search with $max_results if not all files are reqquired
	 */
	static public function recursiveDirList($directory, $return_directory_as_prefix = true, $should_recurse = false, $file_pattern = NULL, $depth = 0, $max_results = -1)
	{
		if($depth > 10)
		{
			// exceeded the recursion depth
			return NULL;
		}
		
		$depth ++;
		
		// create an array to hold directory list
		$results = array();
		// create a handler for the directory
		$handler = @opendir($directory);
		if(! $handler)
			return NULL;
		
		//		echo  ( "directory: " .$directory . "<br>" );
		

		$current_path = pathinfo($directory, PATHINFO_DIRNAME) . "/" . pathinfo($directory, PATHINFO_BASENAME) . "/";
		// keep going until all files in directory have been read
		while(($file = readdir($handler)) != NULL)
		{
			// if $file isn't this directory or its parent,
			// add it to the results array
			if($file != '.' && $file != '..')
			{
				$match = 1;
				if($file_pattern != NULL)
				{
					$match = preg_match($file_pattern, $file);
				}
				
				if($match > 0)
				{
					$results[] = ($return_directory_as_prefix ? $directory . "/" : "") . $file;
					if($max_results > 1 && count($results) > $max_results)
						return $results;
				}
				
				if($should_recurse && is_dir($current_path . $file))
				{
					//				echo "Recursing... [$should_recurse] [$current_path $file]<br>"; 	
					

					$child_dir_results = self::recursiveDirList($current_path . $file, $return_directory_as_prefix, $should_recurse, $file_pattern, $depth);
					if($child_dir_results)
					{
						$results = kArray::append($results, $child_dir_results);
					}
				}
			}
		}
		// tidy up: close the handler
		closedir($handler);
		
		// done!
		return $results;
	}
	
	/**
	 * the result is a list of tuples - files_name , files_size 
	 */
	// TODO - implement recursion
	static public function dirListExtended($directory, $return_directory_as_prefix = true, $should_recurse = false, $file_pattern = NULL, $depth = 0, $fetch_content = false)
	{
		if($depth > 10)
		{
			// exceeded the recursion depth
			return NULL;
		}
		
		// create an array to hold directory list
		$results = array();
		
		// create a handler for the directory
		$handler = @opendir($directory);
		if(! $handler)
			return NULL;
		
		//		echo  ( "directory: " .$directory . "<br>" );
		

		$current_path = pathinfo($directory, PATHINFO_DIRNAME) . "/" . pathinfo($directory, PATHINFO_BASENAME) . "/";
		// keep going until all files in directory have been read
		while(($file = readdir($handler)) != NULL)
		{
			
			// if $file isn't this directory or its parent,
			// add it to the results array
			if($file != '.' && $file != '..')
			{
				if(! $file_pattern)
					$match = 1;
				else
					$match = preg_match($file_pattern, $file);
				
				if($match > 0)
				{
					$file_full_path = $directory . "/" . $file;
					$result = array();
					// first - name (with or without the full path)
					$result[] = ($return_directory_as_prefix ? $directory . "/" : "") . $file;
					// second - size 
					$result[] = kFile::fileSize($file_full_path);
					// third - time
					$result[] = filemtime($file_full_path);
					// forth - content (only if requested
					if($fetch_content)
						$result[] = file_get_contents($file_full_path);
					$results[] = $result;
				}
				
				if($should_recurse && is_dir($current_path . $file))
				{
					//				echo "Recursing... [$should_recurse] [$current_path $file]<br>"; 	
					

					$child_dir_results = self::dirListExtended($current_path . $file, $return_directory_as_prefix, $should_recurse, $file_pattern, ++ $depth);
					if($child_dir_results)
					{
						$results = kArray::append($results, $child_dir_results);
					}
				}
			}
		}
		
		// tidy up: close the handler
		closedir($handler);
		
		// done!
		return $results;
	}
	
	static public function getFileContent($file_name, $from_byte = 0, $to_byte = -1)
	{
		$file_name = self::fixPath($file_name);
		
		try
		{
			if(! file_exists($file_name))
				return NULL;
			$fh = fopen($file_name, 'r');
			
			if($fh == NULL)
				return NULL;
			if($from_byte > 0)
			{
				$from_byte = max(0, $from_byte);
				$dummy = fread($fh, $from_byte);
			}
			
			if($to_byte > 0)
			{
				$to_byte = min($to_byte, kFile::fileSize($file_name));
				$length = $to_byte - $from_byte;
			}
			else
			{
				$length = kFile::fileSize($file_name);
			}
			
			$theData = fread($fh, $length);
			fclose($fh);
			return $theData;
		}
		catch(Exception $ex)
		{
			return NULL;
		}
	}
	
	static public function setFileContent($file_name, $content)
	{
		$file_name = self::fixPath($file_name);
		
		// TODO - this code should be written in fullMkdir
		if(! file_exists(dirname($file_name)))
			self::fullMkdir($file_name);
		
		$fh = fopen($file_name, 'w');
		try
		{
			fwrite($fh, $content);
		}
		catch(Exception $ex)
		{
			// whatever happens - don't forget to cloes $fh
		}
		fclose($fh);
	}
	
	static public function fixPath($file_name)
	{
		$res = str_replace("\\", "/", $file_name);
		$res = str_replace("//", "/", $res);
		return $res;
	}
	
	/**
	 * 
	 * creates a dirctory using the specified path
	 * @param unknown_type $path
	 * @param unknown_type $rights
	 * @param unknown_type $recursive
	 */
	public static function fullMkfileDir ($path, $rights = 0777, $recursive = true)
	{		
		if(file_exists($path))
			return true;
			
		$oldUmask = umask(00);
		$result = @mkdir($path, $rights, $recursive);
		umask($oldUmask);
		return $result;
	}
	
	/**
	 * 
	 * creates a dirctory using the dirname of the specified path
	 * @param unknown_type $path
	 * @param unknown_type $rights
	 * @param unknown_type $recursive
	 */
	public static function fullMkdir($path, $rights = 0777, $recursive = true)
	{
		return self::fullMkfileDir(dirname($path), $rights, $recursive);
	}
	
	private static function rename_wrap($src, $trg)
	{
		if(!file_exists($src))
		{
			KalturaLog::err("Source file doesn't exist [$src]");
			return false;
		}
			
//	KalturaLog::log("before rename");
		if(rename($src, $trg)) 
			return true;
		
//	KalturaLog::log("failed rename");
		$out_arr = array();
		$rv = 0;
		exec("mv \"$src\" \"$trg\"", $out_arr, $rv);
//			echo "RV($rv)\n";
		if($rv==0)
			return true;
		else
			return false;
	}
	
	public static function moveFile($from, $to, $override_if_exists = false, $copy = false)
	{
		$from = str_replace("\\", "/", $from);
		$to = str_replace("\\", "/", $to);
		
		if($override_if_exists && is_file($to))
		{
			self::deleteFile($to);
		}
		
		if(! is_dir(dirname($to)))
		{
			self::fullMkdir($to);
		}
		
		if($copy)
			return copy($from, $to);
		else
			return self::rename_wrap($from, $to);
	}
	
	// make sure the file is closed , then remove it
	public static function deleteFile($file_name)
	{
		$fh = fopen($file_name, 'w') or die("can't open file");
		fclose($fh);
		unlink($file_name);
	}
	
	static public function replaceExt($file_name, $new_ext)
	{
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
		$len = strlen($ext);
		return ($len ? substr($file_name, 0, - strlen($ext)) : $file_name) . $new_ext;
	}
	
	static public function getFileNameNoExtension($file_name, $include_file_path = false)
	{
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
		$base_file_name = pathinfo($file_name, PATHINFO_BASENAME);
		$len = strlen($base_file_name) - strlen($ext);
		if(strlen($ext) > 0)
		{
			$len = $len - 1;
		}
		
		$res = substr($base_file_name, 0, $len);
		if($include_file_path)
		{
			$res = pathinfo($file_name, PATHINFO_DIRNAME) . "/" . $res;
		}
		return $res;
	}
	
	public static function readLastBytesFromFile($file_name, $bytes = 1024)
	{
		$fh = fopen($file_name, 'r');
		$data = "";
		if($fh)
		{
			fseek($fh, - $bytes, SEEK_END);
			$data = fread($fh, $bytes);
		}
		
		fclose($fh);
		
		return $data;
	}
	//
	// downloadHeader - 1 only body, 2 - only header, 3 - both body and header
	//
	static public function downloadUrlToString($sourceUrl, $downloadHeader = 1, $extraHeaders = null)
	{
		// create a new curl resource
		// TODO - remove this hack !!!
		// I added it only because for some reason or other I couldn't get hold of the http_get 
		/*
		if ( function_exists ('http_get'))
		{
			return http_get($sourceUrl, array('redirect' => 5));
			
		}
		else
		*/
		{
			$ch = curl_init();
			
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $sourceUrl);
			curl_setopt($ch, CURLOPT_USERAGENT, "curl/7.11.1");
			curl_setopt($ch, CURLOPT_HEADER, ($downloadHeader & 2) ? 1 : 0);
			curl_setopt($ch, CURLOPT_NOBODY, ($downloadHeader & 1) ? 0 : 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			if($extraHeaders)
				curl_setopt($ch, CURLOPT_HTTPHEADER, $extraHeaders);
			
		// grab URL and pass it to the browser
			$content = curl_exec($ch);
			
			// close curl resource, and free up system resources
			curl_close($ch);
		
		}
		return $content;
	}
	
	static public function downloadUrlToFile($sourceUrl, $fullPath)
	{
		if(empty($sourceUrl))
		{
			$fullPath = null;
			return false;
		}
		$f = fopen($fullPath, "wb");
		
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $sourceUrl);
		curl_setopt($ch, CURLOPT_USERAGENT, "curl/7.11.1");
		curl_setopt($ch, CURLOPT_FILE, $f);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		
		$result = 0;
		if(curl_exec($ch))
		{
			$result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			KalturaLog::info("curl_exec result [$result]");
		}
		else
		{
			KalturaLog::info("curl_exec failed [$sourceUrl]");
		}
		
		curl_close($ch);
		fclose($f);
		
		//226:The server has fulfilled a GET request for the resource, and the response is a representation 
		//    of the result of one or more instance-manipulations applied to the current instance.
		//200:Standard response for successful HTTP requests. The actual response will depend on the request
		//    method used. In a GET request, the response will contain an entity corresponding to the 
		//    requested resource. In a POST request the response will contain an entity describing or
		//    containing the result of the action
		$validCodes = array(
			200,
			226
		);
		return in_array($result, $validCodes);
	}
	
	public static function getFileData($file_full_path)
	{
		return new kFileData($file_full_path);
	}
	
	public static function getFileDataWithContent($file_full_path)
	{
		$add_content = (strpos($file_full_path, ".txt") !== false || strpos($file_full_path, ".log") !== false);
		
		return new kFileData($file_full_path, $add_content);
	
	}
	
	public static function findInFile($file_name, $pattern)
	{
	
	}
	
	public static function read_header($ch, $string)
	{
		$length = strlen($string);
		header($string);
		return $length;
	}
	
	public static function read_body($ch, $string)
	{
		$length = strlen($string);
		echo $string;
		return $length;
	}
	
	public static function dumpApiRequest($host)
	{
		if (kCurrentContext::$multiRequest_index > 1)
            KExternalErrors::dieError(KExternalErrors::MULTIREQUEST_PROXY_FAILED);
		self::closeDbConnections();
		
		// prevent loop back of the proxied request by detecting the "X-Kaltura-Proxy header
		if (isset($_SERVER["HTTP_X_KALTURA_PROXY"]))
			KExternalErrors::dieError(KExternalErrors::PROXY_LOOPBACK);
			
		$get_params = $post_params = array();
		
		// pass uploaded files by adding them as post data with curl @ prefix
		// signifying a file. the $_FILES[xxx][tmp_name] points to the location
		// of the uploaded file.
		// we preserve the original file name by passing the extra ;filename=$_FILES[xxx][name]
		foreach($_FILES as $key => $value)
		{
			$post_params[$key] = "@".$value['tmp_name'].";filename=".$value['name'];
		}
		
		foreach($_POST as $key => $value)
		{
			$post_params[$key] = $value;
		}
		
		$url = $_SERVER['REQUEST_URI'];
		
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $host . $url );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Kaltura-Proxy: dumpApiRequest"));
		curl_setopt($ch, CURLOPT_USERAGENT, "curl/7.11.1");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		// Set callback function for body
		curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'kFile::read_body');
		// Set callback function for headers
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'kFile::read_header');
		
		header("X-Kaltura:dumpApiRequest " . kDataCenterMgr::getCurrentDcId());
		// grab URL and pass it to the browser
		$content = curl_exec($ch);
		
		// close curl resource, and free up system resources
		curl_close($ch);
		die();
	}
	
	public static function getRequestHeaders()
	{
		if(function_exists('apache_request_headers'))
			return apache_request_headers();
		
		foreach($_SERVER as $key => $value)
		{
			if(substr($key, 0, 5) == "HTTP_")
			{
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
				$out[$key] = $value;
			}
		}
		return $out;
	}

	public static function cacheRedirect($url)
	{
		if (function_exists('apc_store'))
		{
			$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
			apc_store("redirect-".$protocol.$_SERVER["REQUEST_URI"], $url, 60);
		}
	}

	public static function dumpUrl($url, $allowRange = true, $passHeaders = false)
	{
		KalturaLog::debug("URL [$url], $allowRange [$allowRange], $passHeaders [$passHeaders]");
		self::closeDbConnections();
		
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "curl/7.11.1");

		// prevent loop back of the proxied request by detecting the "X-Kaltura-Proxy header
		if (isset($_SERVER["HTTP_X_KALTURA_PROXY"]))
			KExternalErrors::dieError(KExternalErrors::PROXY_LOOPBACK);
			
		$sendHeaders = array("X-Kaltura-Proxy: dumpUrl");
		
		if($passHeaders)
		{
			$sentHeaders = self::getRequestHeaders();
			foreach($sentHeaders as $header => $value)
				$sendHeaders[] = "$header: $value";
		}
		elseif($allowRange && isset($_SERVER['HTTP_RANGE']) && $_SERVER['HTTP_RANGE'])
		{
			// get range parameters from HTTP range requst headers
			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			curl_setopt($ch, CURLOPT_RANGE, $range);
		}
		
		// when proxying request to other datacenter we may be already in a proxied request (from one of the internal proxy servers)
		// we need to ensure the original HOST is sent in order to allow restirctions checks

		$host = isset($_SERVER["HTTP_X_FORWARDED_HOST"]) ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["HTTP_HOST"];

		for($i = 0; $i < count($sendHeaders); $i++)
		{
			if (strpos($sendHeaders[$i], "HOST:") === 0)
			{
				array_splice($sendHeaders, $i, 1);
				break;
			}
		}

		$sendHeaders[] = "HOST:$host";

		curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);

		if($_SERVER['REQUEST_METHOD'] == 'HEAD')
		{
			// request was HEAD, proxy only HEAD response
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		}
		else
		{
			// Set callback function for body
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'kFile::read_body');
		}
		// Set callback function for headers
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'kFile::read_header');
		
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		
		header("Access-Control-Allow-Origin:*"); // avoid html5 xss issues
		header("X-Kaltura:dumpUrl");
		// grab URL and pass it to the browser
		$content = curl_exec($ch);
		KalturaLog::debug("CURL executed [$content]");
		
		// close curl resource, and free up system resources
		curl_close($ch);
		
		die();
	}
	
	public static function closeDbConnections()
	{
		// close all opened db connetion while we are dumping the file.
		// this will limit the number of concurrent connections as the dumpFile make take
		// a long time
		

		try
		{
			Propel::close();
		}
		catch(Exception $e)
		{
			$this->logMessage("dumpFile: error closing db $e");
		}
	}
	
	public static function dumpFile($file_name, $mime_type = null, $max_age = null, $limit_file_size = 0)
	{
		self::closeDbConnections();
		
		$nfs_file_tries = 0;
		while(! file_exists($file_name))
		{
			//			clearstatcache(true,$file_name);
			clearstatcache();
			$nfs_file_tries ++;
			if($nfs_file_tries > 3) // if after 9 seconds file did not appear in NFS - probably not found...
			{
				break;
			
		// when breaking, kFile will try to dump, if file not exist - will die...
			}
			else
			{
				sleep(3);
			}
		}
		
		// if by now there is no file - die !
		if(! file_exists($file_name))
			die();
		
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
		$total_length = $limit_file_size ? $limit_file_size : kFile::fileSize($file_name);
		
		$useXSendFile = false;
		if (in_array('mod_xsendfile', apache_get_modules()))
		{
			$xsendfile_uri = kConf::hasParam('xsendfile_uri') ? kConf::get('xsendfile_uri') : null;
			if ($xsendfile_uri !== null && strpos($_SERVER["REQUEST_URI"], $xsendfile_uri) !== false)
			{
				$xsendfile_paths = kConf::hasParam('xsendfile_paths') ? kConf::get('xsendfile_paths') : array();
				foreach($xsendfile_paths as $path)
				{
					if (strpos($file_name, $path) === 0)
					{
						header('X-Kaltura-Sendfile:');
						$useXSendFile = true;
						break;
					}
				}
			}
		}

		if ($useXSendFile)
			$range_length = null;
		else
		{
			// get range parameters from HTTP range requst headers
			list($range_from, $range_to, $range_length) = infraRequestUtils::handleRangeRequest($total_length);
		}
		
		if($mime_type)
		{
			infraRequestUtils::sendCdnHeaders($file_name, $range_length, $max_age, $mime_type);
		}
		else
			infraRequestUtils::sendCdnHeaders($ext, $range_length, $max_age);

		// return "Accept-Ranges: bytes" header. Firefox looks for it when playing ogg video files
		// upon detecting this header it cancels its original request and starts sending byte range requests
		header("Accept-Ranges: bytes");
		header("Access-Control-Allow-Origin:*");		

		if ($useXSendFile)
		{
			if (isset($GLOBALS["start"]))
				header("X-Kaltura:dumpFile:".(microtime(true) - $GLOBALS["start"]));
			header("X-Sendfile: $file_name");
			die;
		}

		$chunk_size = 100000;
		$fh = fopen($file_name, "rb");
		if($fh)
		{
			$pos = 0;
			fseek($fh, $range_from);
			while($range_length > 0)
			{
				$content = fread($fh, min($chunk_size, $range_length));
				echo $content;
				$range_length -= $chunk_size;
			}
			fclose($fh);
		}
		
		die();
	}
	
	public static function mimeType($file_name)
	{
		if(! function_exists('mime_content_type'))
		{
			//            ob_start();
			//            system('file -i -b ' . realpath($file_name));
			//           $type = ob_get_clean();
			

			$type = null;
			exec('file -i -b ' . realpath($file_name), $type);
			
			$parts = @ explode(";", $type[0]); // can be of format text/plain;  charset=us-ascii 
			

			return trim($parts[0]);
		}
		else
		{
			return mime_content_type($file_name);
		}
	}

	public static function safeFilePutContents($filePath, $var, $mode=null)
	{
		// write to a temp file and then rename, so that the write will be atomic
		$tempFilePath = tempnam(dirname($filePath), basename($filePath));
		if (file_put_contents($tempFilePath, $var) === false)
			return false;
		if (rename($tempFilePath, $filePath) === false)
		{
			@unlink($tempFilePath);
			return false;
		}
		if($mode)
		{
			chmod($filePath, $mode);
		}
		return true;
	}
	
}

/**
 * @package infra
 * @subpackage Storage
 */
class kFileData
{
	public $exists;
	public $full_path = NULL;
	public $name = NULL;
	public $size = NULL;
	public $timestamp = NULL;
	public $ext = NULL;
	public $content = NULL;
	public $raw_timestamp = NULL;
	
	public function kFileData($full_file_path, $add_content = false)
	{
		//debugUtils::st();
		$this->full_path = realpath($full_file_path);
		$this->exists = file_exists($full_file_path);
		$this->name = pathinfo($full_file_path, PATHINFO_BASENAME);
		$this->ext = pathinfo($full_file_path, PATHINFO_EXTENSION);
		
		if($this->exists)
		{
			$this->size = kFile::fileSize($full_file_path);
			$this->raw_timestamp = filectime($full_file_path);
			$this->timestamp = date("Y-m-d H:i:s.", $this->raw_timestamp);
			
			if($add_content)
			{
				$this->content = file_get_contents($full_file_path);
			}
		}
	}
}
