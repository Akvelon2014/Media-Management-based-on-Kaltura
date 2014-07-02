<?php

/**
 * PermissionItem service lets you create and manage permission items
 * @service permissionItem
 * @package api
 * @subpackage services
 */
class PermissionItemService extends KalturaBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		myPartnerUtils::addPartnerToCriteria(new PermissionPeer(), $this->getPartnerId(), $this->private_partner_data, $this->partnerGroup());
		myPartnerUtils::addPartnerToCriteria(new PermissionItemPeer(), $this->getPartnerId(), $this->private_partner_data, $this->partnerGroup());
	}
	
	protected function globalPartnerAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'list') {
			return true;
		}
		return parent::globalPartnerAllowed($actionName);
	}
	
	/**
	 * Adds a new permission item object to the account.
	 * This action is available only to Kaltura system administrators.
	 * 
	 * @action add
	 * @param KalturaPermissionItem $permissionItem The new permission item
	 * @return KalturaPermissionItem The added permission item object
	 * 
	 * @throws KalturaErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 * @throws KalturaErrors::PROPERTY_VALIDATION_NOT_UPDATABLE
	 */
	public function addAction(KalturaPermissionItem $permissionItem)
	{							    
	    $dbPermissionItem = $permissionItem->toInsertableObject(null, array('type'));
	    $dbPermissionItem->setPartnerId($this->getPartnerId());
		$dbPermissionItem->save();
		
		$permissionItem = new KalturaPermissionItem();
		$permissionItem->fromObject($dbPermissionItem);
		
		return $permissionItem;
	}
	
	/**
	 * Retrieves a permission item object using its ID.
	 * 
	 * @action get
	 * @param int $permissionItemId The permission item's unique identifier
	 * @return KalturaPermissionItem The retrieved permission item object
	 * 
	 * @throws KalturaErrors::INVALID_OBJECT_ID
	 */		
	public function getAction($permissionItemId)
	{
		$dbPermissionItem = PermissionItemPeer::retrieveByPK($permissionItemId);
		
		if (!$dbPermissionItem) {
			throw new KalturaAPIException(KalturaErrors::INVALID_OBJECT_ID, $permissionItemId);
		}
			
		if ($dbPermissionItem->getType() == PermissionItemType::API_ACTION_ITEM) {
			$permissionItem = new KalturaApiActionPermissionItem();
		}
		else if ($dbPermissionItem->getType() == PermissionItemType::API_PARAMETER_ITEM) {
			$permissionItem = new KalturaApiParameterPermissionItem();
		}
		else {
			$permissionItem = new KalturaPermissionItem();
		}
		
		$permissionItem->fromObject($dbPermissionItem);
		
		return $permissionItem;
	}


	/**
	 * Updates an existing permission item object.
	 * This action is available only to Kaltura system administrators.
	 * 
	 * @action update
	 * @param int $permissionItemId The permission item's unique identifier
	 * @param KalturaPermissionItem $permissionItem The updated permission item parameters
	 * @return KalturaPermissionItem The updated permission item object
	 *
	 * @throws KalturaErrors::INVALID_OBJECT_ID
	 */	
	public function updateAction($permissionItemId, KalturaPermissionItem $permissionItem)
	{
		$dbPermissionItem = PermissionItemPeer::retrieveByPK($permissionItemId);
	
		if (!$dbPermissionItem) {
			throw new KalturaAPIException(KalturaErrors::INVALID_OBJECT_ID, $permissionItemId);
		}
		
		$dbPermissionItem = $permissionItem->toUpdatableObject($dbPermissionItem, array('type'));
		$dbPermissionItem->save();
	
		$permissionItem = new KalturaPermissionItem();
		$permissionItem->fromObject($dbPermissionItem);
		
		return $permissionItem;
	}

	/**
	 * Deletes an existing permission item object.
	 * This action is available only to Kaltura system administrators.
	 * 
	 * @action delete
	 * @param int $permissionItemId The permission item's unique identifier
	 * @return KalturaPermissionItem The deleted permission item object
	 *
	 * @throws KalturaErrors::INVALID_OBJECT_ID
	 */		
	public function deleteAction($permissionItemId)
	{
		$dbPermissionItem = PermissionItemPeer::retrieveByPK($permissionItemId);
	
		if (!$dbPermissionItem) {
			throw new KalturaAPIException(KalturaErrors::INVALID_OBJECT_ID, $permissionItemId);
		}
		
		$dbPermissionItem->delete();
			
		$permissionItem = new KalturaPermissionItem();
		$permissionItem->fromObject($dbPermissionItem);
		
		return $permissionItem;
	}
	
	/**
	 * Lists permission item objects that are associated with an account.
	 * 
	 * @action list
	 * @param KalturaPermissionItemFilter $filter A filter used to exclude specific types of permission items
	 * @param KalturaFilterPager $pager A limit for the number of records to display on a page
	 * @return KalturaPermissionItemListResponse The list of permission item objects
	 */
	public function listAction(KalturaPermissionItemFilter  $filter = null, KalturaFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new KalturaPermissionItemFilter();
			
		$permissionItemFilter = $filter->toObject();
		
		$c = new Criteria();
		$permissionItemFilter->attachToCriteria($c);
		$count = PermissionItemPeer::doCount($c);
		
		if (! $pager)
			$pager = new KalturaFilterPager ();
		
		$pager->attachToCriteria ( $c );
		$list = PermissionItemPeer::doSelect($c);
		
		$response = new KalturaPermissionItemListResponse();
		$response->objects = KalturaPermissionItemArray::fromDbArray($list);
		$response->totalCount = $count;
		
		return $response;
	}	
}
