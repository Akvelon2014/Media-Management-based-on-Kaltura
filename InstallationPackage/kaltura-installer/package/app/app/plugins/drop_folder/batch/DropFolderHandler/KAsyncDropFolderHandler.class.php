<?php
/**
 * Handles files in drop folders
 *
 * @package plugins.dropFolder
 * @subpackage Scheduler
 */
class KAsyncDropFolderHandler extends KPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::DROP_FOLDER_HANDLER;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	
	/* (non-PHPdoc)
	 * @see KBatchBase::run()
	*/
	public function run($jobs = null)
	{
		KalturaLog::info("Drop folder handler batch is running");
		
		if($this->taskConfig->isInitOnly())
			return $this->init();
		
		//TODO: use getFilter instead of taskConfig->params
		// get drop folder tags to work on from configuration
		$folderTags = $this->taskConfig->params->tags;
		$currentDc  = $this->taskConfig->params->dc;
		
		if (strlen($folderTags) == 0) {
			KalturaLog::err('Tags configuration is empty - cannot continue');
			return;
		}
		
		if (strlen($currentDc) == 0) {
			KalturaLog::err('DC configuration is empty - cannot continue');
			return;
		}
		
		// get list of drop folders according to configuration
		$filter = new KalturaDropFolderFilter();
		
		if ($folderTags != '*') {
			$filter->tagsMultiLikeOr = $folderTags;
		}
			
		$filter->dcEqual = $currentDc;
		$filter->statusEqual = KalturaDropFolderStatus::ENABLED;
		
		try {
			$dropFolders = $this->kClient->dropFolder->listAction($filter);
		}
		catch (Exception $e) {
			KalturaLog::err('Cannot get drop folder list - '.$e->getMessage());
			return;
		}
		
		$dropFolders = $dropFolders->objects;
		KalturaLog::log('['.count($dropFolders).'] folders to handle');
		
		foreach ($dropFolders as $folder)
		{
		    try {
			    $this->handleFolder($folder);
		    }
		    catch (Exception $e) {
		        KalturaLog::err('Unknown error with folder id ['.$folder->id.'] - '.$e->getMessage());			
		    }
		}
	}
		
	/**
	 * Main logic function.
	 * @param KalturaDropFolder $folder
	 */
	private function handleFolder(KalturaDropFolder $folder)
	{
		KalturaLog::debug('Handling folder ['.$folder->id.']');
		
		$fileNamePatterns = explode(',', $folder->fileNamePatterns);
		if(count($fileNamePatterns) > 1)
		{
			$fileNamePatterns = null;
		}
		elseif(count($fileNamePatterns) == 1)
		{
			foreach($fileNamePatterns as $index => $fileNamePattern)
				$fileNamePatterns[$index] = trim($fileNamePattern, ' *');
				
			$fileNamePatterns = reset($fileNamePatterns);
		}
	 	
		$dropFolderFilePlugin = KalturaDropFolderClientPlugin::get($this->kClient);
		$dropFolderFileFilter = new KalturaDropFolderFileFilter();
		$dropFolderFileFilter->dropFolderIdEqual = $folder->id;
		$dropFolderFileFilter->statusIn = KalturaDropFolderFileStatus::PENDING.','.KalturaDropFolderFileStatus::WAITING.','.KalturaDropFolderFileStatus::NO_MATCH;
		$dropFolderFileFilter->orderBy = KalturaDropFolderFileOrderBy::CREATED_AT_DESC;
		if($fileNamePatterns)
			$dropFolderFileFilter->fileNameLike = $fileNamePatterns;
		$pager = new KalturaFilterPager();
		$pager->pageIndex = 1;
		
		try{		
			$dropFolderFiles = $dropFolderFilePlugin->dropFolderFile->listAction($dropFolderFileFilter, $pager);
			/* @var $dropFolderFiles KalturaDropFolderFileListResponse */ 
		}
		catch (KalturaAPIException $e) {
			KalturaLog::err('Cannot get list of files for drop folder id ['.$folder->id.'] pageIndex ['.$pager->pageIndex.'] - '.$e->getMessage());
			return false;
		}
		
		while (count ($dropFolderFiles->objects)){
			foreach ($dropFolderFiles->objects as $file)
			{
				$fileHandled = $this->handleFile($folder, $file);
				
				if ($fileHandled) {
					// break loop and go to next folder, because current folder files' status might have changed
					return true;
				}
			}
			
			$pager->pageIndex++;
			try{	
				$dropFolderFiles = $dropFolderFilePlugin->dropFolderFile->listAction($dropFolderFileFilter, $pager);
			}
			catch (KalturaAPIException $e) {
				KalturaLog::err('Cannot get list of files for drop folder id ['.$folder->id.'] pageIndex ['.$pager->pageIndex.']- '.$e->getMessage());
				return false;
			} 
		}
		
		if ($pager->pageIndex > 1)
			return true;
		else
			return false; // no file was handled
	}
	
	/**
	 * Handle the given file if it matches the defined file name pattern by executing the right file handler
	 * @param KalturaDropFolder $dropFolder
	 * @param KalturaDropFolderFile $dropFolderFile
	 */
	private function handleFile(KalturaDropFolder $dropFolder, KalturaDropFolderFile $dropFolderFile)
	{
		KalturaLog::debug('Handling file id ['.$dropFolderFile->id.'] name ['.$dropFolderFile->fileName.']');
		
		// get defined file name patterns
		$filePatterns = $dropFolder->fileNamePatterns;
		$filePatterns = array_map('trim', explode(',', $filePatterns));
		
		// get current file name
		$fileName = $dropFolderFile->fileName;
		
		// search for a match
		$matchFound = false;
		foreach ($filePatterns as $pattern)
		{
			if (!is_null($pattern) && ($pattern != '')) {
				if (fnmatch($pattern, $fileName)) {
					$matchFound = true;
					break;
				}
			}
		}
		
		// if file name doesn't match pattern - quit
		if (!$matchFound) {
			KalturaLog::debug("File name [$fileName] does not match any of the defined patterns");
			return false;
		}
		
		//  handle file by the file handelr configured for its drop folder
		$fileHandler = DropFolderFileHandler::getHandler($dropFolder->fileHandlerType);
		$fileHandler->setConfig($this->kClient, $dropFolderFile, $dropFolder, $this->taskConfig);
		$fileHandled = $fileHandler->handle();
		if ($fileHandled) {
			KalturaLog::debug('File handled succesfully');
			return true;
		}
		else {
			KalturaLog::err('File was not handled!');
			return false;
		}
	}
	
	function log($message)
	{
		KalturaLog::debug($message);
	}
}
