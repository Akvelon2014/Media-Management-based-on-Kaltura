<?php
/**
 * Thumbnail Params Output service
 *
 * @service thumbParamsOutput
 * @package api
 * @subpackage services
 */
class ThumbParamsOutputService extends KalturaBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		if($this->getPartnerId() != Partner::BATCH_PARTNER_ID && $this->getPartnerId() != Partner::ADMIN_CONSOLE_PARTNER_ID)
			throw new KalturaAPIException(KalturaErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
	
	/**
	 * Get thumb params output object by ID
	 * 
	 * @action get
	 * @param int $id
	 * @return KalturaThumbParamsOutput
	 * @throws KalturaErrors::THUMB_PARAMS_OUTPUT_ID_NOT_FOUND
	 */
	public function getAction($id)
	{
		$thumbParamsOutputDb = assetParamsOutputPeer::retrieveByPK($id);
		
		if (!$thumbParamsOutputDb)
			throw new KalturaAPIException(KalturaErrors::THUMB_PARAMS_OUTPUT_ID_NOT_FOUND, $id);
			
		$thumbParamsOutput = new KalturaThumbParamsOutput();
		$thumbParamsOutput->fromObject($thumbParamsOutputDb);
		
		return $thumbParamsOutput;
	}
	
	/**
	 * List thumb params output objects by filter and pager
	 * 
	 * @action list
	 * @param KalturaThumbParamsOutputFilter $filter
	 * @param KalturaFilterPager $pager
	 * @return KalturaThumbParamsOutputListResponse
	 */
	function listAction(KalturaThumbParamsOutputFilter $filter = null, KalturaFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new KalturaThumbParamsOutputFilter();

		if (!$pager)
			$pager = new KalturaFilterPager();
			
		$thumbParamsOutputFilter = new assetParamsOutputFilter();
		
		$filter->toObject($thumbParamsOutputFilter);

		$c = new Criteria();
		$thumbParamsOutputFilter->attachToCriteria($c);
		
		$thumbTypes = KalturaPluginManager::getExtendedTypes(assetParamsOutputPeer::OM_CLASS, assetType::THUMBNAIL);
		$c->add(assetParamsOutputPeer::TYPE, $thumbTypes, Criteria::IN);
		
		$totalCount = assetParamsOutputPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = assetParamsOutputPeer::doSelect($c);
		
		$list = KalturaThumbParamsOutputArray::fromDbArray($dbList);
		$response = new KalturaThumbParamsOutputListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;
	}
}
