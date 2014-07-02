<?php
/**
 * Used to ingest media that is available on remote server and accessible using the supplied URL, the media file won’t be downloaded but a file sync object of URL type will point to the media URL.
 *
 * @package Core
 * @subpackage model.data
 */
class kRemoteStorageResource extends kUrlResource implements IRemoteStorageResource
{
	/**
	 * ID of storage profile to be associated with the created file sync, used for file serving URL composing, keep null to use the default. 
	 * @var int
	 */
	private $storageProfileId;
	
	/**
	 * @return the $storageProfileId
	 */
	public function getStorageProfileId()
	{
		return $this->storageProfileId;
	}

	/**
	 * @param int $storageProfileId
	 */
	public function setStorageProfileId($storageProfileId)
	{
		$this->storageProfileId = $storageProfileId;
	}
	
	/* (non-PHPdoc)
	 * @see IRemoteStorageResource::getResources()
	*/
	public function getResources()
	{
		return array($this);
	}

	/**
	 * @return string
	 */
	public function getFileExt()  
	{
		$parsedUrl = parse_url($this->getUrl());
		return pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);
	}
}