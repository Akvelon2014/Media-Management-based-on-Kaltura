<?php

/**
 * metadata service base test case.
 */
abstract class MetadataServiceTestBase extends KalturaApiTestCase
{
	/**
	 * Tests metadata->add action
	 * @param int $metadataProfileId 
	 * @param KalturaMetadataObjectType $objectType 
	 * @param string $objectId 
	 * @param string $xmlData XML metadata
	 * @param KalturaMetadata $reference
	 * @return KalturaMetadata
	 * @dataProvider provideData
	 */
	public function testAdd($metadataProfileId, $objectType, $objectId, $xmlData, KalturaMetadata $reference)
	{
		$resultObject = $this->client->metadata->add($metadataProfileId, $objectType, $objectId, $xmlData);
		if(method_exists($this, 'assertInstanceOf'))
			$this->assertInstanceOf('KalturaMetadata', $resultObject);
		else
			$this->assertType('KalturaMetadata', $resultObject);
		$this->assertAPIObjects($reference, $resultObject, array('createdAt', 'updatedAt', 'id', 'thumbnailUrl', 'downloadUrl', 'rootEntryId', 'operationAttributes', 'deletedAt', 'statusUpdatedAt', 'widgetHTML', 'totalCount', 'objects', 'cropDimensions', 'dataUrl', 'requiredPermissions', 'confFilePath', 'feedUrl'));
		$this->assertNotNull($resultObject->id);
		$this->validateAdd($resultObject);
		
		return $resultObject->id;
	}

	/**
	 * Validates testAdd results
	 * Hook to be overriden by the extending class
	 * 
	 * @param KalturaMetadata $resultObject
	 */
	protected function validateAdd(KalturaMetadata $resultObject){}

	/**
	 * Tests metadata->get action
	 * @param int $id 
	 * @param KalturaMetadata $reference
	 * @depends testAdd with data set #0
	 * @dataProvider provideData
	 */
	public function testGet($id, KalturaMetadata $reference)
	{
		$resultObject = $this->client->metadata->get($id);
		if(method_exists($this, 'assertInstanceOf'))
			$this->assertInstanceOf('KalturaMetadata', $resultObject);
		else
			$this->assertType('KalturaMetadata', $resultObject);
		$this->assertAPIObjects($reference, $resultObject, array('createdAt', 'updatedAt', 'id', 'thumbnailUrl', 'downloadUrl', 'rootEntryId', 'operationAttributes', 'deletedAt', 'statusUpdatedAt', 'widgetHTML', 'totalCount', 'objects', 'cropDimensions', 'dataUrl', 'requiredPermissions', 'confFilePath', 'feedUrl'));
		$this->validateGet($resultObject);
	}

	/**
	 * Validates testGet results
	 * Hook to be overriden by the extending class
	 * 
	 * @param KalturaMetadata $resultObject
	 */
	protected function validateGet(KalturaMetadata $resultObject){}

	/**
	 * Tests metadata->update action
	 * @param int $id 
	 * @param string $xmlData XML metadata
	 * @param int $version Enable update only if the metadata object version did not change by other process
	 * @param KalturaMetadata $reference
	 * @depends testAdd with data set #1
	 * @dataProvider provideData
	 */
	public function testUpdate($id, $xmlData = "", $version = "", KalturaMetadata $reference)
	{
		$resultObject = $this->client->metadata->update($id, $xmlData, $version);
		if(method_exists($this, 'assertInstanceOf'))
			$this->assertInstanceOf('KalturaMetadata', $resultObject);
		else
			$this->assertType('KalturaMetadata', $resultObject);
		$this->assertAPIObjects($reference, $resultObject, array('createdAt', 'updatedAt', 'id', 'thumbnailUrl', 'downloadUrl', 'rootEntryId', 'operationAttributes', 'deletedAt', 'statusUpdatedAt', 'widgetHTML', 'totalCount', 'objects', 'cropDimensions', 'dataUrl', 'requiredPermissions', 'confFilePath', 'feedUrl'));
		$this->validateUpdate($resultObject);
	}

	/**
	 * Validates testUpdate results
	 * Hook to be overriden by the extending class
	 * 
	 * @param KalturaMetadata $resultObject
	 */
	protected function validateUpdate(KalturaMetadata $resultObject){}

	/**
	 * Tests metadata->listAction action
	 * @param KalturaMetadataFilter $filter 
	 * @param KalturaFilterPager $pager 
	 * @param KalturaMetadataListResponse $reference
	 * @dataProvider provideData
	 */
	public function testListAction(KalturaMetadataFilter $filter = null, KalturaFilterPager $pager = null, KalturaMetadataListResponse $reference)
	{
		$resultObject = $this->client->metadata->listAction($filter, $pager);
		if(method_exists($this, 'assertInstanceOf'))
			$this->assertInstanceOf('KalturaMetadataListResponse', $resultObject);
		else
			$this->assertType('KalturaMetadataListResponse', $resultObject);
		$this->assertAPIObjects($reference, $resultObject, array('createdAt', 'updatedAt', 'id', 'thumbnailUrl', 'downloadUrl', 'rootEntryId', 'operationAttributes', 'deletedAt', 'statusUpdatedAt', 'widgetHTML', 'totalCount', 'objects', 'cropDimensions', 'dataUrl', 'requiredPermissions', 'confFilePath', 'feedUrl'));
		$this->validateListAction($resultObject);
	}

	/**
	 * Validates testListAction results
	 * Hook to be overriden by the extending class
	 * 
	 * @param KalturaMetadataListResponse $resultObject
	 */
	protected function validateListAction(KalturaMetadataListResponse $resultObject){}

	/**
	 * Tests metadata->delete action
	 * @param int $id 
	 * @depends testAdd with data set #2
	 * @dataProvider provideData
	 */
	public function testDelete($id)
	{
		$resultObject = $this->client->metadata->delete($id);
	}

}
