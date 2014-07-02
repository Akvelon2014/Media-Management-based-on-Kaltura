<?php

/**
 * @package plugins.dropFolder 
 * @subpackage Scheduler.fileHandlers
 */
class DropFolderContentFileHandler extends DropFolderFileHandler
{	
	
	// if regexp includes (?P<referenceId>\w+) or (?P<flavorName>\w+), they will be translated to the parsedSlug and parsedFlavor
	
	const REFERENCE_ID_WILDCARD = 'referenceId';
	const FLAVOR_NAME_WILDCARD  = 'flavorName';
	const DEFAULT_SLUG_REGEX = '/(?P<referenceId>.+)[.]\w{3,}/';
// 	const DEFAULT_SLUG_REGEX = '/(?P<referenceId>\w+)_(?P<flavorName>\w+)[.]\w+/'; // matches "referenceId_flavorName.extension"
	
	/**
	 * @var KalturaDropFolderContentFileHandlerConfig
	 */
	protected $config;
	
	/**
	 * @var KalturaConversionProfileAssetParams
	 */
	private $parsedFlavorObject;
	
	/**
	 * @var KalturaConversionProfile
	 */
	private $conversionProfile = null;
	
	public function getType() {
		return KalturaDropFolderFileHandlerType::CONTENT;
	}
	
	/**
	 * @return KalturaConversionProfile
	 */
	protected function getConversionProfile()
	{
		if (is_null($this->conversionProfile)) {
			$this->conversionProfile = parent::getConversionProfile();
		}
		return $this->conversionProfile;
	}
	
	public function handle()
	{
		$this->conversionProfile = null;
		
		// check prerequisites
		$checkConfig = $this->checkConfig();
		if (!$checkConfig) {
			return false;
		}		
		
		// parse file name according to slugRegex and extract parsedSlug and parsedFlavor
		$regexMatch = $this->parseRegex();
		if (!$regexMatch) {
			$this->dropFolderFile->status = KalturaDropFolderFileStatus::ERROR_HANDLING;
			$this->dropFolderFile->errorCode = KalturaDropFolderFileErrorCode::SLUG_REGEX_NO_MATCH;
			$this->dropFolderFile->errorDescription = 'File name ['.$this->dropFolderFile->fileName.'] does not match defined slug regex ['.$this->config->slugRegex.']';
			KalturaLog::err($this->dropFolderFile->errorDescription);
			$this->updateDropFolderFile(); // update errors tatus
			return false; // file not handled
		}
		
		// check if parsed flavor exists
		if (!is_null($this->dropFolderFile->parsedFlavor))
		{
			$conversionProfileId = $this->getConversionProfile()->id;
			$this->parsedFlavorObject = $this->getFlavorBySystemName($this->dropFolderFile->parsedFlavor, $conversionProfileId);
			if (!$this->parsedFlavorObject) {
				$this->dropFolderFile->status = KalturaDropFolderFileStatus::ERROR_HANDLING;
				$this->dropFolderFile->errorCode = KalturaDropFolderFileErrorCode::FLAVOR_NOT_FOUND;
				$this->dropFolderFile->errorDescription = 'Parsed flavor system name ['.$this->dropFolderFile->parsedFlavor.'] could not be found';
				KalturaLog::err($this->dropFolderFile->errorDescription);
				$this->updateDropFolderFile(); // update errors tatus
				return false; // file not handled
			}
			
		}
		
		$updatedStatus = false;
		// handle file according to the defined policy
		switch ($this->config->contentMatchPolicy)
		{
			case KalturaDropFolderContentFileHandlerMatchPolicy::ADD_AS_NEW:
				$updatedStatus = $this->addAsNewContent();
				break;
			
			case KalturaDropFolderContentFileHandlerMatchPolicy::MATCH_EXISTING_OR_KEEP_IN_FOLDER:
				$updatedStatus = $this->addAsExistingContent();
				break;
				
			case KalturaDropFolderContentFileHandlerMatchPolicy::MATCH_EXISTING_OR_ADD_AS_NEW:
				$updatedStatus = $this->addAsExistingContent();
				if ($this->dropFolderFile->status === KalturaDropFolderFileStatus::NO_MATCH) {
					$updatedStatus = $this->addAsNewContent();
				}
				break;
				
			default:
				KalturaLog::err('No content match policy is defined for drop folder ['.$this->dropFolder->id.']');
				return false;
		}

		// update file with all changes that were done during the handling process except for the status which will is already updated by the addContent/updateContent API call
		try {
			$this->updateDropFolderFile($updatedStatus);
		}
		catch (Exception $e) {
			KalturaLog::err('Cannot update file - '.$e->getMessage());
			return false;			
		}
		
		return in_array($this->dropFolderFile->status, array(KalturaDropFolderFileStatus::HANDLED, KalturaDropFolderFileStatus::DOWNLOADING)); // return true if handled, false otherwise
	}
	
	/**
	 * Parse file name according to defined slugRegex and set the extracted parsedSlug and parsedFlavor.
	 * The following expressions are currently recognized and used:
	 * 	- (?P<referenceId>\w+) - will be used as the drop folder file's parsed slug.
	 *  - (?P<flavorName>\w+)  - will be used as the drop folder file's parsed flavor. 
	 * 
	 * @return bool true if file name matches the slugRegex or false otherwise
	 */
	private function parseRegex()
	{
		$matches = null;
		$slugRegex = (is_null($this->config->slugRegex) || empty($this->config->slugRegex)) ? self::DEFAULT_SLUG_REGEX : $this->config->slugRegex;
		$matchFound = @preg_match($slugRegex, $this->dropFolderFile->fileName, $matches);
		
		if (!$matchFound) {
			return false; // file name does not match defined regex
		}
		
		$this->dropFolderFile->parsedSlug   = isset($matches[self::REFERENCE_ID_WILDCARD]) ? $matches[self::REFERENCE_ID_WILDCARD] : null;
		$this->dropFolderFile->parsedFlavor = isset($matches[self::FLAVOR_NAME_WILDCARD])  ? $matches[self::FLAVOR_NAME_WILDCARD]  : null;
			
		KalturaLog::debug('Parsed slug ['.$this->dropFolderFile->parsedSlug.'], Parsed flavor ['.$this->dropFolderFile->parsedFlavor.']');
		return true; // file name matches the defined regex
	}
	
	/**
	 * Add the new file to a new entry, together with all other relevant drop folder files, according to the ingestion profile
	 * 
	 * @return bool true if drop folder file status should be updated or false otherwise
	 */
	private function addAsNewContent()
	{ 
		$resource = null;
		
		$conversionProfile = null;
		if (is_null($this->dropFolderFile->parsedFlavor))
		{
			$resource = new KalturaDropFolderFileResource();
			$resource->dropFolderFileId = $this->dropFolderFile->id;
		}
		else
		{
			$conversionProfile = $this->getConversionProfile();
			$resource = $this->getAllIngestedFiles($conversionProfile->id);
			if (!$resource) {
				KalturaLog::debug('Some required flavors do not exist in the drop folder - changing status to WAITING');
				$this->dropFolderFile->status = KalturaDropFolderFileStatus::WAITING;
				return true;
			}
		}
		
		$newEntry = new KalturaBaseEntry();
		if (!$conversionProfile) {
			$conversionProfile = $this->getConversionProfile();
		}
		$newEntry->conversionProfileId = $conversionProfile->id;
		$newEntry->name = $this->dropFolderFile->parsedSlug;
		$newEntry->referenceId = $this->dropFolderFile->parsedSlug;
		
		if (is_null($newEntry->name))
		{
			// if parsed slug not defined -> file name without extension and flavor is taken the default entry name
			$tempSlug = str_replace($this->dropFolderFile->parsedFlavor, '', $this->dropFolderFile->fileName); // remove flavor name part
			$tempSlug = substr($tempSlug, 0, strrchr($tempSlug, '.')+1); // remove extension
			$newEntry->name = $tempSlug;
		}

		try 
		{
			$this->impersonate($this->dropFolderFile->partnerId);
			$addedEntry = $this->kClient->baseEntry->add($newEntry, null);
			$addedEntry = $this->kClient->baseEntry->addContent($addedEntry->id, $resource);			
			$this->unimpersonate();		
		}
		catch (Exception $e)
		{
			KalturaLog::err('Cannot add new entry - '.$e->getMessage());
			$this->dropFolderFile->status = KalturaDropFolderFileStatus::ERROR_HANDLING;
			$this->dropFolderFile->errorCode = KalturaDropFolderFileErrorCode::ERROR_ADD_ENTRY;
			$this->dropFolderFile->errorDescription = 'Internal error adding new entry';	
			return true;
		}
		
		return false;
	}

	/**
	 * Match the current file to an existing entry and flavor according to the slug regex.
	 * Update the matched entry with the new file and all other relevant files from the drop folder, according to the ingestion profile.
	 *
	 * @return bool true if drop folder file status should be updated or false otherwise
	 */
	private function addAsExistingContent()
	{	    
		// check for matching entry and flavor
		$resource = null;
		$matchedEntry = $this->getEntryByReferenceId($this->dropFolderFile->parsedSlug);
		
		if (!$matchedEntry)
		{
			$this->dropFolderFile->status = KalturaDropFolderFileStatus::NO_MATCH;
			$this->dropFolderFile->errorDescription = 'No matching entry found';
			KalturaLog::debug($this->dropFolderFile->errorDescription);
			return true;
		}
		
		// if configuration does not include a flavor system name reference -> just update content with the file
		if (is_null($this->dropFolderFile->parsedFlavor))
		{
		    $resource = new KalturaDropFolderFileResource();
			$resource->dropFolderFileId = $this->dropFolderFile->id;
		}		

		if (!$resource && !$this->parsedFlavorObject)
		{
			$this->dropFolderFile->status = KalturaDropFolderFileStatus::ERROR_HANDLING;
			$this->dropFolderFile->errorCode = KalturaDropFolderFileErrorCode::FLAVOR_NOT_FOUND;
			$this->dropFolderFile->errorDescription = 'Parsed flavor system name ['.$this->dropFolderFile->parsedFlavor.'] could not be found';
			KalturaLog::err($this->dropFolderFile->errorDescription);
			return true;
		}
		

//		$entryConversionProfileId = $matchedEntry->conversionProfileId;
//		if (is_null($entryConversionProfileId)) {
			$entryConversionProfileId = $this->getConversionProfile()->id;
//		}
        if (!$resource) {
		    $resource = $this->getAllIngestedFiles($entryConversionProfileId);
        }
		if (!$resource) {
			$this->dropFolderFile->status = KalturaDropFolderFileStatus::WAITING;
			return true;
		}
		
		try 
		{
			$this->impersonate($this->dropFolderFile->partnerId);
			$conversionProfile = $this->getConversionProfile();
			$updatedEntry = $this->kClient->baseEntry->updateContent($matchedEntry->id, $resource, $conversionProfile->id);
			$this->unimpersonate();		
		}
		catch (Exception $e)
		{
			$this->unimpersonate();
			KalturaLog::err('Cannot update entry - '.$e->getMessage());
			$this->dropFolderFile->status = KalturaDropFolderFileStatus::ERROR_HANDLING;
			$this->dropFolderFile->errorCode = KalturaDropFolderFileErrorCode::ERROR_UPDATE_ENTRY;
			$this->dropFolderFile->errorDescription = 'Internal error updating entry';	
			return true;
		}
		
		return false;		
	}
	
	/**
	 * Check if all required files for the given ingestion profile are in the drop folder.
	 * If yes -> retrun a KalturaAssetsParamsResourceContainers resource containing them + the current file
	 * If not -> return false
	 * 
	 * @param int $conversionProfileId
	 * @return KalturaAssetsParamsResourceContainers
	 */
	private function getAllIngestedFiles($conversionProfileId = null)
	{
		KalturaLog::debug("Ingest files according to conversion profile [$conversionProfileId]");
		
		if (is_null($conversionProfileId)) {
			$conversionProfileId = $this->getConversionProfile()->id;
		}
		
		$fileFilter = new KalturaDropFolderFileFilter();
		$fileFilter->dropFolderIdEqual = $this->dropFolder->id;
		$fileFilter->statusIn = KalturaDropFolderFileStatus::PENDING.','.KalturaDropFolderFileStatus::WAITING.','.KalturaDropFolderFileStatus::NO_MATCH;
		$fileFilter->parsedSlugEqual = $this->dropFolderFile->parsedSlug; // must belong to the same entry
				
		$existingFileList = $this->kClient->dropFolderFile->listAction($fileFilter);
		
		$existingFlavors = array();
		$existingFlavors[$this->dropFolderFile->parsedFlavor] = $this->dropFolderFile->id;
		
		foreach ($existingFileList->objects as $existingFile)
		{
			$existingFlavors[$existingFile->parsedFlavor] = $existingFile->id;
		}
		
		$assetContainerArray = array();
		$currentFlavorAdded = false;
		
		$assetParamsFilter = new KalturaConversionProfileAssetParamsFilter();
		$assetParamsFilter->conversionProfileIdEqual = $conversionProfileId;
		$this->impersonate($this->dropFolderFile->partnerId);
		$assetParamsList = $this->kClient->conversionProfileAssetParams->listAction($assetParamsFilter);
		$this->unimpersonate();
		foreach ($assetParamsList->objects as $assetParams)
		{
			if ($assetParams->origin == KalturaAssetParamsOrigin::CONVERT) {
				continue;
			}
			
			if(!array_key_exists($assetParams->systemName, $existingFlavors))
			{
				if ($assetParams->readyBehavior == KalturaFlavorReadyBehaviorType::REQUIRED && $assetParams->origin == KalturaAssetParamsOrigin::INGEST)
				{
					KalturaLog::debug("Flavor [$assetParams->systemName] is required and must be ingested");
					return false;
				}
			
				KalturaLog::debug("Flavor [$assetParams->systemName] not supplied");
				continue;
			}
			
			$assetContainer = new KalturaAssetParamsResourceContainer();
			$assetContainer->assetParamsId = $assetParams->assetParamsId;
			$assetContainer->resource = new KalturaDropFolderFileResource();
			$assetContainer->resource->dropFolderFileId = $existingFlavors[$assetParams->systemName];
			$assetContainerArray[] = $assetContainer;
			if ($assetContainer->resource->dropFolderFileId === $this->dropFolderFile->id) {
				$currentFlavorAdded = true;
			}
		}
		
		// add current drop folder file to list even if it is not part of the ingestion profile
		if (!$currentFlavorAdded) {
			$assetContainer = new KalturaAssetParamsResourceContainer();
			$assetContainer->assetParamsId = $this->parsedFlavorObject->assetParamsId;
			$assetContainer->resource = new KalturaDropFolderFileResource();
			$assetContainer->resource->dropFolderFileId = $this->dropFolderFile->id;
			$assetContainerArray[] = $assetContainer;
		}
		
		$containers = new KalturaAssetsParamsResourceContainers();
		$containers->resources = $assetContainerArray;
		
		return $containers;		
	}
}