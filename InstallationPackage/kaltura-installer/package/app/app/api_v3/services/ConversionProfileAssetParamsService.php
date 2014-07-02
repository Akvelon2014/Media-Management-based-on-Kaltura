<?php

/**
 * Manage the connection between Conversion Profiles and Asset Params
 *
 * @service conversionProfileAssetParams
 * @package api
 * @subpackage services
 */
class ConversionProfileAssetParamsService extends KalturaBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		parent::applyPartnerFilterForClass(new conversionProfile2Peer());
		
		$partnerGroup = null;
		if($actionName == 'list')
			$partnerGroup = $this->partnerGroup . ',0';
			
			
		parent::applyPartnerFilterForClass(new assetParamsPeer(), $partnerGroup);
	}
	
	/**
	 * Lists asset parmas of conversion profile by ID
	 * 
	 * @action list
	 * @param KalturaConversionProfileAssetParamsFilter $filter
	 * @param KalturaFilterPager $pager
	 * @return KalturaConversionProfileAssetParamsListResponse
	 */
	public function listAction(KalturaConversionProfileAssetParamsFilter $filter = null, KalturaFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new KalturaConversionProfileAssetParamsFilter();

		if (!$pager)
			$pager = new KalturaFilterPager();
			
		$assetParamsConversionProfileFilter = $filter->toObject();

		$c = new Criteria();
		$assetParamsConversionProfileFilter->attachToCriteria($c);
		
		$totalCount = flavorParamsConversionProfilePeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = flavorParamsConversionProfilePeer::doSelect($c);
		
		$list = KalturaConversionProfileAssetParamsArray::fromDbArray($dbList);
		$response = new KalturaConversionProfileAssetParamsListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response; 
	}
	
	/**
	 * Update asset parmas of conversion profile by ID
	 * 
	 * @action update
	 * @param int $conversionProfileId
	 * @param int $assetParamsId
	 * @param KalturaConversionProfileAssetParams $conversionProfileAssetParams
	 * @return KalturaConversionProfileAssetParams
	 */
	public function updateAction($conversionProfileId, $assetParamsId, KalturaConversionProfileAssetParams $conversionProfileAssetParams)
	{
		$conversionProfile = ConversionProfile2Peer::retrieveByPK($conversionProfileId);
		if(!$conversionProfile)
			throw new KalturaAPIException(KalturaErrors::CONVERSION_PROFILE_ID_NOT_FOUND, $conversionProfileId);
			
		$flavorParamsConversionProfile = flavorParamsConversionProfilePeer::retrieveByFlavorParamsAndConversionProfile($assetParamsId, $conversionProfileId);
		if(!$flavorParamsConversionProfile)
			throw new KalturaAPIException(KalturaErrors::CONVERSION_PROFILE_ASSET_PARAMS_NOT_FOUND, $conversionProfileId, $assetParamsId);
			
		$conversionProfileAssetParams->toUpdatableObject($flavorParamsConversionProfile);
		$flavorParamsConversionProfile->save();
			
		$conversionProfileAssetParams->fromObject($flavorParamsConversionProfile);
		return $conversionProfileAssetParams;
	}
}