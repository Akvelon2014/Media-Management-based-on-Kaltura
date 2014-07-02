<?php

/**
 * Add & Manage Thumb Params
 *
 * @service thumbParams
 * @package api
 * @subpackage services
 */
class ThumbParamsService extends KalturaBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		parent::applyPartnerFilterForClass(new conversionProfile2Peer());
		parent::applyPartnerFilterForClass(new assetPeer());
		parent::applyPartnerFilterForClass(new assetParamsOutputPeer());
		
		$partnerGroup = null;
		if(
			$actionName == 'get' ||
			$actionName == 'list'
			)
			$partnerGroup = $this->partnerGroup . ',0';
			
		parent::applyPartnerFilterForClass(new assetParamsPeer(), $partnerGroup);
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
	 * Add new Thumb Params
	 * 
	 * @action add
	 * @param KalturaThumbParams $thumbParams
	 * @return KalturaThumbParams
	 */
	public function addAction(KalturaThumbParams $thumbParams)
	{	
		$thumbParamsDb = new thumbParams();
		$thumbParams->toInsertableObject($thumbParamsDb);
		
		$thumbParamsDb->setPartnerId($this->getPartnerId());
		$thumbParamsDb->save();
		
		$thumbParams->fromObject($thumbParamsDb);
		return $thumbParams;
	}
	
	/**
	 * Get Thumb Params by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return KalturaThumbParams
	 */
	public function getAction($id)
	{
		$thumbParamsDb = assetParamsPeer::retrieveByPK($id);
		
		if (!$thumbParamsDb)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$thumbParams = KalturaFlavorParamsFactory::getFlavorParamsInstance($thumbParamsDb->getType());
		$thumbParams->fromObject($thumbParamsDb);
		
		return $thumbParams;
	}
	
	/**
	 * Update Thumb Params by ID
	 * 
	 * @action update
	 * @param int $id
	 * @param KalturaThumbParams $thumbParams
	 * @return KalturaThumbParams
	 */
	public function updateAction($id, KalturaThumbParams $thumbParams)
	{
		$thumbParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$thumbParamsDb)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$thumbParams->toUpdatableObject($thumbParamsDb);
		$thumbParamsDb->save();
			
		$thumbParams->fromObject($thumbParamsDb);
		return $thumbParams;
	}
	
	/**
	 * Delete Thumb Params by ID
	 * 
	 * @action delete
	 * @param int $id
	 */
	public function deleteAction($id)
	{
		$thumbParamsDb = assetParamsPeer::retrieveByPK($id);
		if (!$thumbParamsDb)
			throw new KalturaAPIException(KalturaErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $id);
			
		$thumbParamsDb->setDeletedAt(time());
		$thumbParamsDb->save();
	}
	
	/**
	 * List Thumb Params by filter with paging support (By default - all system default params will be listed too)
	 * 
	 * @action list
	 * @param KalturaThumbParamsFilter $filter
	 * @param KalturaFilterPager $pager
	 * @return KalturaThumbParamsListResponse
	 */
	public function listAction(KalturaThumbParamsFilter $filter = null, KalturaFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new KalturaThumbParamsFilter();

		if (!$pager)
			$pager = new KalturaFilterPager();
			
		$thumbParamsFilter = new assetParamsFilter();
		
		$filter->toObject($thumbParamsFilter);
		
		$c = new Criteria();
		$thumbParamsFilter->attachToCriteria($c);
		$pager->attachToCriteria($c);
		
		$thumbTypes = KalturaPluginManager::getExtendedTypes(assetParamsPeer::OM_CLASS, assetType::THUMBNAIL);
		$c->add(assetParamsPeer::TYPE, $thumbTypes, Criteria::IN);
		
		$dbList = assetParamsPeer::doSelect($c);
		
		$c->setLimit(null);
		$totalCount = assetParamsPeer::doCount($c);

		$list = KalturaThumbParamsArray::fromDbArray($dbList);
		$response = new KalturaThumbParamsListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;    
	}
	
	/**
	 * Get Thumb Params by Conversion Profile ID
	 * 
	 * @action getByConversionProfileId
	 * @param int $conversionProfileId
	 * @return KalturaThumbParamsArray
	 */
	public function getByConversionProfileIdAction($conversionProfileId)
	{
		$conversionProfileDb = conversionProfile2Peer::retrieveByPK($conversionProfileId);
		if (!$conversionProfileDb)
			throw new KalturaAPIException(KalturaErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
			
		$thumbParamsConversionProfilesDb = $conversionProfileDb->getflavorParamsConversionProfilesJoinflavorParams();
		$thumbParamsDb = array();
		foreach($thumbParamsConversionProfilesDb as $item)
		{
			/* @var $item flavorParamsConversionProfile */
			$thumbParamsDb[] = $item->getassetParams();
		}
		
		$thumbParams = KalturaThumbParamsArray::fromDbArray($thumbParamsDb);
		
		return $thumbParams; 
	}
}