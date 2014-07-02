<?php
/**
 * Will transform metadata XML based on XSL and will update the metadata object with the new version 
 *
 * @package plugins.metadata
 * @subpackage Scheduler.Transform
 */
class KAsyncTransformMetadata extends KJobHandlerWorker
{
	/**
	 * @var int
	 */
	protected $multiRequestSize = 20;
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return KalturaBatchJobType::METADATA_TRANSFORM;
	}
	
	/* (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(KalturaBatchJob $job)
	{
		return $this->upgrade($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::getJobs()
	 * 
	 * TODO remove the destXsdPath from the job data and get it later using the api, then delete this method
	 */
	protected function getJobs()
	{
		return $this->kClient->metadataBatch->getExclusiveTransformMetadataJobs($this->getExclusiveLockKey(), $this->taskConfig->maximumExecutionTime, 1, $this->getFilter());
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	private function invalidateFailedMetadatas($results, $transformObjectIds = array())
	{
		$this->kClient->startMultiRequest();
		foreach($results as $index => $result){
        	if(is_array($result) && isset($result['code']) && isset($result['message'])){
              	KalturaLog::err('error in object id['.$transformObjectIds[$index] .'] with code: '. $result['code']."\n".$result['message']." going to invalidate it");
              	$this->kClient->metadata->invalidate($transformObjectIds[$index]);
        	}
        }
        $resultsOfInvalidating = $this->kClient->doMultiRequest();	
		foreach($resultsOfInvalidating as $index => $resultOfInvalidating){
        	if(is_array($resultOfInvalidating) && isset($resultOfInvalidating['code']) && isset($resultOfInvalidating['message'])){
              	KalturaLog::err('error while invalidating object id['.$transformObjectIds[$index] .'] with code: '. $resultOfInvalidating['code']."\n".$resultOfInvalidating['message']);        	
        	}
        }	
	}
	
	private function upgrade(KalturaBatchJob $job, KalturaTransformMetadataJobData $data)
	{
		KalturaLog::debug("transform($job->id)");
		
		if($this->taskConfig->params->multiRequestSize)
			$this->multiRequestSize = $this->taskConfig->params->multiRequestSize;
		
		$pager = new KalturaFilterPager();
		$pager->maxPageSize = 40;
		if($this->taskConfig->params && $this->taskConfig->params->maxObjectsEachRun)
			$pager->maxPageSize = $this->taskConfig->params->maxObjectsEachRun;
		
		$transformList = $this->kClient->metadataBatch->getTransformMetadataObjects(
			$data->metadataProfileId,
			$data->srcVersion,
			$data->destVersion,
			$pager
		);
			
		if(!$transformList->totalCount) // if no metadata objects returned
		{
			if(!$transformList->lowerVersionCount) // if no metadata objects of lower version exist
			{
				$this->closeJob($job, null, null, 'All metadata transformed', KalturaBatchJobStatus::FINISHED);
				return $job;
			}
			
			$this->closeJob($job, null, null, "Waiting for metadata objects [$transformList->lowerVersionCount] of lower versions", KalturaBatchJobStatus::RETRY);
			return $job;
		}
		
		if($transformList->lowerVersionCount || $transformList->totalCount) // another retry will be needed later
		{
			$this->kClient->batch->resetJobExecutionAttempts($job->id, $this->getExclusiveLockKey(), $job->jobType);
		}
			
		$this->kClient->startMultiRequest();
		$transformObjectIds = array();
		foreach($transformList->objects as $object)
		{
			/* @var $object KalturaMetadata */
			$xml = kXsd::transformXmlData($object->xml, $data->destXsdPath, $data->srcXslPath);
			if($xml)
			{
				$this->kClient->metadata->update($object->id, $xml, $object->version);
			}
			else 
			{			
				$this->kClient->metadata->invalidate($object->id, $object->version);
			}
			
			$transformObjectIds[] = $object->id;
				    
			if($this->kClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
			{
				$results = $this->kClient->doMultiRequest();
				$this->invalidateFailedMetadatas($results, $transformObjectIds);
				$transformObjectIds = array();
				$this->kClient->startMultiRequest();
			}
			
		}
		$results = $this->kClient->doMultiRequest();
		$this->invalidateFailedMetadatas($results, $transformObjectIds);
		
		$this->closeJob($job, null, null, "Metadata objects [" . count($transformList->objects) . "] transformed", KalturaBatchJobStatus::RETRY);
		
		return $job;
	}
	
	
}
