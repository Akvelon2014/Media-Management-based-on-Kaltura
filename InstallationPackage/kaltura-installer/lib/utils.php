<?php 
    
	/** 
	 * Taken from: http://php.net/manual/en/function.copy.php
	 * 
	 * Recursively copies a directory from $src to $dst.
	 * If $dst does not exist it is created.
	 * Omits .svn directories.
	 * Omits .git directories.
	 **/
	function recurse_copy($src, $dst) { 
		$dir = opendir($src); 
		@mkdir($dst, 0775, true); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (($file != '.') && ($file != '..') && ($file != '.svn') && ($file != '.git')){ 
				if (is_dir($src . '/' . $file)) { 
					recurse_copy($src . '/' . $file,$dst . '/' . $file); 
				} 
				else { 
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		closedir($dir); 
	} 	
	
	/**
	* Taken from: http://lixlpixel.org/recursive_function/php/recursive_directory_delete/
	*
	* ------------ lixlpixel recursive PHP functions -------------
	* recursive_remove_directory( directory to delete, empty )
	* expects path to directory and optional TRUE / FALSE to empty
	* of course PHP has to have the rights to delete the directory
	* you specify and all files and folders inside the directory
	* ------------------------------------------------------------

	* to use this function to totally remove a directory, write:
	* recursive_remove_directory('path/to/directory/to/delete');

	* to use this function to empty a directory, write:
	* recursive_remove_directory('path/to/full_directory',TRUE);
	**/
	function recursive_remove_directory($directory, $empty=FALSE)
	{
		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}

		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory))
		{
			// ... we return false and exit the function
			return FALSE;

		// ... if the path is not readable
		}elseif(!is_readable($directory))
		{
			// ... we return false and exit the function
			return FALSE;

		// ... else if the path is readable
		}else{

			// we open the directory
			$handle = opendir($directory);

			// and scan through the items inside
			while (FALSE !== ($item = readdir($handle)))
			{
				// if the filepointer is not the current directory
				// or the parent directory
				if($item != '.' && $item != '..')
				{
					// we build the new path to delete
					$path = $directory.'/'.$item;

					// if the new path is a directory
					if(is_dir($path)) 
					{
						// we call this function with the new path
						recursive_remove_directory($path);

					// if the new path is a file
					}else{
						// we remove the file
						unlink($path);
					}
				}
			}
			// close the directory
			closedir($handle);

			// if the option to empty is not set to true
			if($empty == FALSE)
			{
				// try to delete the now empty directory
				if(!rmdir($directory))
				{
					// return false if not possible
					return FALSE;
				}
			}
			// return success
			return TRUE;
		}
	}

	/**
	 * Provides a function similar to `realpath()` that allows 
	 * `recurse_copy` to copy files to a path that does not yet exist
	 */
	function futurepath($path)
	{
		$path = explode('/', $path);
		foreach($path as $index => $directory) {
			if ($directory == "..") {
				unset($path[$index]);
				unset($path[$index-1]);
			}
		}
		return implode("/", $path);
	}

	// exports the $repository to the $destination using the $user and the $pass
	function svn_export($repository, $user, $pass, $destination)
	{
		$svn_cmd = "svn  --username $user --password $pass export --force $repository $destination | grep ^Exported";
		echo "$svn_cmd\n";
		$result = exec($svn_cmd);
		$pos = strpos($result, 'Exported');
		if ($pos !== 0) {
			echo "Failed svn export of $repository to $destination failed. Error: $result\n";
			die(1);
		} 
		return $repository . " : " .$result; 	
	}
	
	// exports a "svn group" using "svn global" and the given $base_dir to add to the local path
	// $group is a hashtable with ['svn_path'], ['local_path'], ['get'] (has '*' in [0] or a list of subdirectories to export)
	// $global is a hashtable with ['svn_repo'], ['svn_user'], ['svn_pass'] 
	function svn_export_group($group, $global, $base_dir, $singleGet = null)
	{	
		$revision = array();
		if ($group['get'][0] == '*') {
			$revision[$global['svn_repo'] . $group['svn_path']] = svn_export($global['svn_repo'] . $group['svn_path'], $global['svn_user'], $global['svn_pass'], $base_dir . $group['local_path']);
		}
		else {
			if($singleGet){
				$revision[$global['svn_repo'] . $group['svn_path'] . $singleGet] = svn_export($global['svn_repo'] . $group['svn_path'] . $singleGet, $global['svn_user'], $global['svn_pass'], $base_dir . $group['local_path'] . $singleGet);
			} else {
				foreach($group['get'] as $current) {
					$revision[$global['svn_repo'] . $group['svn_path'] . $current] = svn_export($global['svn_repo'] . $group['svn_path'] . $current, $global['svn_user'], $global['svn_pass'], $base_dir . $group['local_path'] . $current);
				}
			}
		}
		if (isset($group['remove'])) {
			foreach ($group['remove'] as $current) {
				$folders_to_remove = get_folders_to_remove($base_dir . $group['local_path'], $current);
				//$path = explode(" * ", $current);
				foreach ($folders_to_remove as $folder) {
					remove($base_dir . $group['local_path'] . '/' . $folder);
				}
			}
		}
		return $revision;
	}
	
	//get code from github
	function github_export_group($group, $base_dir)
	{
		$revision = array();
		
		mkdir("$base_dir/".$group['local_path'],null,true);
		foreach($group['get'] as $current) 
		{
			$revision[$group['git_path'] . $current] = github_export($group['git_path'] . '/' . $current, $base_dir . $group['local_path'] . $current, $current);
		}
		return $revision;
		
	}
	
	function github_export($repository, $destination, $version)
	{
		@mkdir($destination);
		
		$git_cmd = "wget $repository";
		echo "$git_cmd\n";
		$result = exec($git_cmd);
		
		$tar_cmd = "tar -C $destination -xvf ./$version --strip-components=1";		
		echo "$tar_cmd\n";
		$result = exec($tar_cmd);
		
		unlink("./$version");
		copy($destination."/LocalSettings.KalturaPlatform.php",  $destination."/LocalSettings.php");
		
		return $repository; 	
	}
	
	function get_folders_to_remove($base_path, $remove_exp) {
		$path = explode(" * ", $remove_exp);
		if (count($path) > 1) {
			$list_cmd = 'ls ' . $base_path . $path[0] ;
			
			for ($i = 1; $i < count($path); $i += 1) {
				$list_cmd .= ' | grep -v ' . $path[$i];
			}
			exec($list_cmd, $dirs_to_remove);
		} else {
			$dirs_to_remove[] = $remove_exp;
		}
		return $dirs_to_remove;
	}
	
	function remove($folder) {
		$remove_cmd = 'rm -rf ' . $folder;
		echo "$remove_cmd\n";
		$result = exec($remove_cmd);
		if ($result != '') {
			echo "Failed remove $folder. Error: $result\n";
			die(1);
		}
	}

	// checkout the $repository to the $destination using the $user and the $pass
	function svn_checkout($repository, $user, $pass, $destination)
	{
		$svn_cmd = "svn -q --username $user --password $pass checkout  $repository $destination";
		echo "$svn_cmd\n";
		$result = exec($svn_cmd);
		if ($result != '') {
			echo "Failed svn checkout from $repository to $destination failed. Error: $result\n";
			die(1);
		}	
	}
	
	// exports a "svn group" using "svn global" and the given $base_dir to add to the local path
	// $group is a hashtable with ['svn_path'], ['local_path'], ['get'] (has '*' in [0] or a list of subdirectories to export)
	// $global is a hashtable with ['svn_repo'], ['svn_user'], ['svn_pass'] 
	function svn_checkout_group($group, $global, $base_dir)
	{	
		if ($group['get'][0] == '*') {
			svn_checkout($global['svn_repo'] . $group['svn_path'], $global['svn_user'], $global['svn_pass'], $base_dir . $group['local_path']);
		}
		else {
			foreach($group['get'] as $current) {
				svn_checkout($global['svn_repo'] . $group['svn_path'] . $current, $global['svn_user'], $global['svn_pass'], $base_dir . $group['local_path'] . $current);
			}
		}
	}
	
	// manupliates all the uiconfs in the given directory (and subdirs recursively)
	function manipulateUiConfs($uiconfsdir)
	{
		$dir_handle = @opendir($uiconfsdir);

		if ($dir_handle == false) {
			echo "Failed to manipulate uiconfs in directory $uiconfsdir\n";
			die(1);
		}
		
		while (false !== ($file = readdir($dir_handle))) {
			$current = $uiconfsdir . '/' . $file;

			if (is_dir($current) && $file != '.' && $file !='..' ) {
				manipulateUiConfs($current);
			} elseif($file != '.' && $file !='..') {
				manipulateUiConf($current);
			}
		}

		closedir($dir_handle);
    }
		
	// manipulates a single uiconf by adding disableUrlHashing="true" to it in the kalturaMix plugin
	function manipulateUiConf($uiconf)
	{
		$uiconf_content = file_get_contents($uiconf);
		$manipulated_content = str_replace('<Plugin id="kalturaMix"','<Plugin id="kalturaMix" disableUrlHashing="true" ',$uiconf_content);
		file_put_contents($uiconf, $manipulated_content);
	}
	
	function getVersionFromKconf($kconf, $label)
	{
		if (preg_match("/".$label." = .*/", $kconf, $matches)) {
			$firstPos = stripos($matches[0],"=");
			return trim(substr($matches[0],1+$firstPos));
		}
	}
