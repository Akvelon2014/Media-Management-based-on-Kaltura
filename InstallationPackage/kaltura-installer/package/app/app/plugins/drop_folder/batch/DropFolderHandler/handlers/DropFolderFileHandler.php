<?php

/**
 * @package plugins.dropFolder 
 * @subpackage Scheduler.fileHandlers
 */
abstract class DropFolderFileHandler
{
	/**
	 * @var KalturaClient
	 */
	protected $kClient;
	
	/**
	 * @var KalturaDropFolderFileHandlerConfig
	 */
	protected $config;
	
	/**
	 * @var KalturaDropFolder
	 */
	protected $dropFolder;
	
	/**
	 * @var KalturaDropFolderFile
	 */
	protected $dropFolderFile;
	
	/**
	 * @var int
	 */
	private $batchPartnerId;
	
	
	
	/**
	 * Return a new instance of a class extending DropFolderFileHandler, according to give $type
	 * @param KalturaDropFolderFileHandlerType $type
	 * @return DropFolderFileHandler
	 */
	public static function getHandler($type)
	{
		switch ($type)
		{
			case KalturaDropFolderFileHandlerType::CONTENT:
				return new DropFolderContentFileHandler();		
				
			default:
				return KalturaPluginManager::loadObject('DropFolderFileHandler', $type);
		}
	}
	

	public function setConfig(KalturaClient $client, KalturaDropFolderFile $dropFolderFile, KalturaDropFolder $dropFolder, KSchedularTaskConfig $taskConfig)
	{
		$this->kClient = $client;
		$this->dropFolder = $dropFolder;
		$this->dropFolderFile = $dropFolderFile;
		$this->config = $dropFolder->fileHandlerConfig;
		$this->batchPartnerId = $this->kClient->getConfig()->partnerId;
	}

	
	/**
	 * Should handle the drop folder file with the given id
	 * At the end of execution, the DropFolderFile object's STATUS may be one of the following:
	 * 1. HANDLED - success
	 * 2. WAITING - waiting for another file
	 * 3. ERROR_HANDLING - an error happened
	 * 4. NO_MATCH - no error occured, but the file cannot be handled since it does not match any entry
	 * 
	 * @return true if file was handled or false otherwise
	 */
	public abstract function handle();	
		// must be implemented by extending classes
	
	
	/**
	 * @return KalturaDropFolderFileHandlerType
	 */
	public abstract function getType();
		// must be implemented by extending classes
	
	protected function checkConfig()
	{
		if (!$this->config) {
			KalturaLog::err('File handler configuration not defined');
			return false; // file not handled
		}
		
		if (!$this->kClient) {
			KalturaLog::err('Kaltura client not defined');
			return false; // file not handled
		}
		
		if (!$this->dropFolder) {
			KalturaLog::err('Drop folder not defined');
			return false; // file not handled
		}
		
		if (!$this->dropFolderFile) {
			KalturaLog::err('Drop folder file not defined');
			return false; // file not handled
		}
		
		return true;
	}
	
	/**
	 * Update the associated drop folder file object with its current state
	 * @return KalturaDropFolderFile
	 * @param $updateStatus bool update status or not
	 */
	protected function updateDropFolderFile($updateStatus = true)
	{
		$dropFolderFilePlugin = KalturaDropFolderClientPlugin::get($this->kClient);
		
		$updateFile = new KalturaDropFolderFile();
		$updateFile->parsedSlug = $this->dropFolderFile->parsedSlug;
		$updateFile->parsedFlavor = $this->dropFolderFile->parsedFlavor;
		$updateFile->errorCode = $this->dropFolderFile->errorCode;
		$updateFile->errorDescription = $this->dropFolderFile->errorDescription;		
		
		$this->impersonate($this->dropFolderFile->partnerId);
		$updatedFile = $dropFolderFilePlugin->dropFolderFile->update($this->dropFolderFile->id, $updateFile);
		if ($updateStatus) {
		    $updatedFile = $dropFolderFilePlugin->dropFolderFile->updateStatus($this->dropFolderFile->id, $this->dropFolderFile->status);
		}
		$this->unimpersonate();
		
		return $updatedFile;
	}
	
	
	/**
	 * @param string $parsedFlavor
	 * @return KalturaConversionProfileAssetParams the flavor matching the given $systemName
	 */
	protected function getFlavorBySystemName($systemName, $conversionProfileId = null)
	{
		if (is_null($conversionProfileId)) {
			$conversionProfile = $this->getConversionProfile();
			$conversionProfileId = $conversionProfile->id;
		}
		
		$assetParamsFilter = new KalturaConversionProfileAssetParamsFilter();
		$assetParamsFilter->conversionProfileIdEqual = $conversionProfileId;
		
		$this->impersonate($this->dropFolderFile->partnerId);
		$assetParamsList = $this->kClient->conversionProfileAssetParams->listAction($assetParamsFilter);
		$this->unimpersonate();
		$assetParamsList = $assetParamsList->objects;
		
		foreach ($assetParamsList as $assetParams)
		{
			if ($assetParams->systemName === $systemName) {
				return $assetParams;
			}
		}
		
		return null;		
	}
		
	
	/**
	 * @param string $referenceId
	 * @return KalturaFlavorParams the entry matching the given $referenceId
	 */
	protected function getEntryByReferenceId($referenceId)
	{
		$entryFilter = new KalturaBaseEntryFilter();
		$entryFilter->referenceIdEqual = $referenceId;
		$entryFilter->statusIn = KalturaEntryStatus::IMPORT.','.KalturaEntryStatus::PRECONVERT.','.KalturaEntryStatus::READY.','.KalturaEntryStatus::PENDING.','.KalturaEntryStatus::NO_CONTENT;		
		
		$entryPager = new KalturaFilterPager();
		$entryPager->pageSize = 1;
		$entryPager->pageIndex = 1;
		$this->impersonate($this->dropFolderFile->partnerId);
		$entryList = $this->kClient->baseEntry->listAction($entryFilter, $entryPager);
		$this->unimpersonate();
		
		if (is_array($entryList->objects) && isset($entryList->objects[0]) ) {
			$result = $entryList->objects[0];
			if ($result->referenceId === $this->dropFolderFile->parsedSlug) {
				return $result;
			}
			else {
				KalturaLog::err("baseEntry->list returned wrong results when filtered by referenceId [$referenceId]");
			}
		}

		return null;
	}
	
	/**
	 * @return KalturaConversionProfile
	 */
	protected function getConversionProfile()
	{
		$this->impersonate($this->dropFolderFile->partnerId);
		if (!is_null($this->dropFolder->conversionProfileId)) {
			$result = $this->kClient->conversionProfile->get($this->dropFolder->conversionProfileId);
		}
		else {
			$result = $this->kClient->conversionProfile->getDefault();
		}
		$this->unimpersonate();
		return $result;
	}
		
	
	protected function impersonate($partnerId)
	{
		$config = $this->kClient->getConfig();
		$config->partnerId = $partnerId;
		$this->kClient->setConfig($config);
	}
	
	protected function unimpersonate()
	{
		$config = $this->kClient->getConfig();
		$config->partnerId = $this->batchPartnerId;
		$this->kClient->setConfig($config);
	}
}