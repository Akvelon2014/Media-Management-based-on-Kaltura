<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaBatchJob extends KalturaBaseJob implements IFilterable
{
	/**
	 * @var string
	 * @filter eq
	 */
	public $entryId;
	
	/**
	 * @var string
	 */
	public $entryName;
	
	/**
	 * @var KalturaBatchJobType
	 * @readonly 
	 * @filter eq,in,notin
	 */
    public $jobType;
    
	/**
	 * @var int
	 * @filter eq,in,notin
	 */
    public $jobSubType;
    
	/**
	 * @var int
	 * @filter eq,in,notin
	 */
    public $onStressDivertTo;
    
    
	/**
	 * @var KalturaJobData
	 */
    public $data;

    /**
	 * @var KalturaBatchJobStatus
	 * @filter eq,in,notin,order
	 */
    public $status;
    
    /**
	 * @var int
	 * @filter eq
	 */
    public $abort;
    
    /**
	 * @var int
	 * @filter gte,lte,order
	 */
    public $checkAgainTimeout;

    /**
	 * @var int
	 * @filter gte,lte,order
	 */
    public $progress;
    
    /**
	 * @var string
	 */
    public $message ;
    
    /**
	 * @var string
	 */
    public $description ;
    
    /**
	 * @var int
	 * @filter gte,lte,order
	 */
    public $updatesCount ;
    
    /**
	 * @var int
	 * @filter gte,lte,eq,in,notin,order
	 */
    public $priority ;
    
    
    /**
     * The id of identical job
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $twinJobId;
    
    
    /**
     * The id of the bulk upload job that initiated this job
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $bulkJobId;
    
    
    /**
     * When one job creates another - the parent should set this parentJobId to be its own id.
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $parentJobId;
    
    
    /**
     * The id of the root parent job
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $rootJobId;
    
    
    /**
     * The time that the job was pulled from the queue
	 * @var int
	 * @filter gte,lte,order
	 */    
    public $queueTime;
    
    
    /**
     * The time that the job was finished or closed as failed
	 * @var int
	 * @filter gte,lte,order
	 */    
    public $finishTime;
    
    
    /**
	 * @var KalturaBatchJobErrorTypes
	 * @filter eq,in,notin
	 */    
    public $errType;
    
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $errNumber;
    
    
    /**
	 * @var int
	 * @filter lt,gt,order
	 */    
    public $fileSize;
    
    
    /**
	 * @var bool
	 * @filter eq
	 */    
    public $lastWorkerRemote;
	
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $schedulerId;
	
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $workerId;
	
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $batchIndex;
	
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $lastSchedulerId;
	
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $lastWorkerId;
	
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $dc;

	
	private static $map_between_objects = array
	(
		"entryId" ,
		"jobType" , 
	 	"status" , "abort" , "checkAgainTimeout" , "progress" ,
		"message", "description" , "updatesCount" , "parentJobId" ,
		"rootJobId", "bulkJobId" , "twinJobId" , "priority" ,
		"queueTime" , "finishTime" , "errType", "errNumber", "fileSize",
		"lastWorkerRemote", "onStressDivertTo",
		"schedulerId",
		"workerId",
		"batchIndex",
		"lastSchedulerId",
		"lastWorkerId",
		"dc",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	    
	public function fromStatisticsObject($dbBatchJob)
	{
		$this->fromObject($dbBatchJob);
		
		if(!($dbBatchJob instanceof BatchJob))
			return $this;
			
		$entry = $dbBatchJob->getEntry(true);
		if($entry)
			$this->entryName = $entry->getName();
		
		return $this;
	}
	    
	public function fromData(BatchJob $dbBatchJob, $dbData)
	{
		if(!$dbData)
			return;
				
		switch(get_class($dbData))
		{
			case 'kConvartableJobData':
				$this->data = new KalturaConvartableJobData();
				break;
				
			case 'kConvertJobData':
				$this->data = new KalturaConvertJobData();
				break;
				
			case 'kConvertProfileJobData':
				$this->data = new KalturaConvertProfileJobData();
				break;
				
			case 'kExtractMediaJobData':
				$this->data = new KalturaExtractMediaJobData();
				break;
				
			case 'kImportJobData':
				$this->data = new KalturaImportJobData();
				break;
				
			case 'kSshImportJobData':
				$this->data = new KalturaSshImportJobData();
				break;
				
			case 'kPostConvertJobData':
				$this->data = new KalturaPostConvertJobData();
				break;
				
			case 'kMailJobData':
				$this->data = new KalturaMailJobData();
				break;
				
			case 'kNotificationJobData':
				$this->data = new KalturaNotificationJobData();
				break;
				
			case 'kBulkDownloadJobData':
				$this->data = new KalturaBulkDownloadJobData();
				break;
				
			case 'kFlattenJobData':
				$this->data = new KalturaFlattenJobData();
				break;
			
			case 'kProvisionJobData':
				$this->data = new KalturaProvisionJobData();
				break;
				
			case 'kAkamaiProvisionJobData':
				$this->data = new KalturaAkamaiProvisionJobData();
				break;				
				
			case 'kConvertCollectionJobData':
				$this->data = new KalturaConvertCollectionJobData();
				break;
				
			case 'kStorageExportJobData':
				$this->data = new KalturaStorageExportJobData();
				break;
				
			case 'kMoveCategoryEntriesJobData':
				$this->data = new KalturaMoveCategoryEntriesJobData();
				break;
				
			case 'kStorageDeleteJobData':
				$this->data = new KalturaStorageDeleteJobData();
				break;
				
			case 'kCaptureThumbJobData':
				$this->data = new KalturaCaptureThumbJobData();
				break;
				
			case 'kMoveCategoryEntriesJobData':
			    $this->data = new KalturaMoveCategoryEntriesJobData();
			    break;

			case 'kIndexJobData':
				$this->data = new KalturaIndexJobData();
				break;
				
			case 'kCopyJobData':
				$this->data = new KalturaCopyJobData();
				break;
				
			case 'kDeleteJobData':
				$this->data = new KalturaDeleteJobData();
				break;
				
			default:			
				if($dbData instanceof kBulkUploadJobData)
					$this->data = KalturaPluginManager::loadObject('KalturaBulkUploadJobData', $dbBatchJob->getJobSubType());
				else if($dbData instanceof kImportJobData)
					$this->data = KalturaPluginManager::loadObject('KalturaImportJobData', get_class($dbData));
				else
					$this->data = KalturaPluginManager::loadObject('KalturaJobData', $this->jobType, array('coreJobSubType' => $dbBatchJob->getJobSubType()));
		}
			
		if($this->data)
			$this->data->fromObject($dbData);
	}
	    
	public function fromObject($dbBatchJob)
	{
		parent::fromObject( $dbBatchJob );
		$this->queueTime = $dbBatchJob->getQueueTime(null); // to return the timestamp and not string
		$this->finishTime = $dbBatchJob->getFinishTime(null); // to return the timestamp and not string
		
		if(!($dbBatchJob instanceof BatchJob))
			return $this;
			
		$dbData = $dbBatchJob->getData();
		$this->fromData($dbBatchJob, $dbData);
		if($this->data)
			$this->jobSubType = $this->data->fromSubType($dbBatchJob->getJobSubType());
		
		return $this;
	}
	
	public function toData(BatchJob $dbBatchJob)
	{
		$dbData = null;
		
		if(is_null($this->jobType))
			$this->jobType = kPluginableEnumsManager::coreToApi('BatchJobType', $dbBatchJob->getJobType());
		
		switch($dbBatchJob->getJobType())
		{
			case KalturaBatchJobType::BULKUPLOAD:
				$dbData = new kBulkUploadJobData();
				if(is_null($this->data))
					$this->data = new KalturaBulkUploadJobData();
				break;
				
			case KalturaBatchJobType::CONVERT:
				$dbData = new kConvertJobData();
				if(is_null($this->data))
					$this->data = new KalturaConvertJobData();
				break;
				
			case KalturaBatchJobType::CONVERT_PROFILE:
				$dbData = new kConvertProfileJobData();
				if(is_null($this->data))
					$this->data = new KalturaConvertProfileJobData();
				break;
				
			case KalturaBatchJobType::EXTRACT_MEDIA:
				$dbData = new kExtractMediaJobData();
				if(is_null($this->data))
					$this->data = new KalturaExtractMediaJobData();
				break;
				
			case KalturaBatchJobType::IMPORT:
				$dbData = new kImportJobData();
				if(is_null($this->data))
					$this->data = new KalturaImportJobData();
				break;
				
			case KalturaBatchJobType::POSTCONVERT:
				$dbData = new kPostConvertJobData();
				if(is_null($this->data))
					$this->data = new KalturaPostConvertJobData();
				break;
				
			case KalturaBatchJobType::MAIL:
				$dbData = new kMailJobData();
				if(is_null($this->data))
					$this->data = new KalturaMailJobData();
				break;
				
			case KalturaBatchJobType::NOTIFICATION:
				$dbData = new kNotificationJobData();
				if(is_null($this->data))
					$this->data = new KalturaNotificationJobData();
				break;
				
			case KalturaBatchJobType::BULKDOWNLOAD:
				$dbData = new kBulkDownloadJobData();
				if(is_null($this->data))
					$this->data = new KalturaBulkDownloadJobData();
				break;
				
			case KalturaBatchJobType::FLATTEN:
				$dbData = new kFlattenJobData();
				if(is_null($this->data))
					$this->data = new KalturaFlattenJobData();
				break;
				
			case KalturaBatchJobType::PROVISION_PROVIDE:
			case KalturaBatchJobType::PROVISION_DELETE:
				$jobSubType = $dbBatchJob->getJobSubType();
				if  ($jobSubType == KalturaSourceType::AKAMAI_LIVE)
				{
					$dbData = new kAkamaiProvisionJobData();
					if(is_null($this->data))
						$this->data = new KalturaAkamaiProvisionJobData();
				}
				else
				{
					$dbData = new kProvisionJobData();
					if(is_null($this->data))
						$this->data = new KalturaProvisionJobData();
				}
				break;
				
			case KalturaBatchJobType::CONVERT_COLLECTION:
				$dbData = new kConvertCollectionJobData();
				if(is_null($this->data))
					$this->data = new KalturaConvertCollectionJobData();
				break;
				
			case KalturaBatchJobType::STORAGE_EXPORT:
				$dbData = new kStorageExportJobData();
				if(is_null($this->data))
					$this->data = new KalturaStorageExportJobData();
				break;
				
			case KalturaBatchJobType::MOVE_CATEGORY_ENTRIES:
				$dbData = new kMoveCategoryEntriesJobData();
				if(is_null($this->data))
					$this->data = new KalturaMoveCategoryEntriesJobData();
				break;
				
			case KalturaBatchJobType::STORAGE_DELETE:
				$dbData = new kStorageDeleteJobData();
				if(is_null($this->data))
					$this->data = new KalturaStorageDeleteJobData();
				break;
				
			case KalturaBatchJobType::CAPTURE_THUMB:
				$dbData = new kCaptureThumbJobData();
				if(is_null($this->data))
					$this->data = new KalturaCaptureThumbJobData();
				break;
				
			case KalturaBatchJobType::INDEX:
				$dbData = new kIndexJobData();
				if(is_null($this->data))
					$this->data = new KalturaIndexJobData();
				break;
				
			case KalturaBatchJobType::COPY:
				$dbData = new kCopyJobData();
				if(is_null($this->data))
					$this->data = new KalturaCopyJobData();
				break;
				
			case KalturaBatchJobType::DELETE:
				$dbData = new kDeleteJobData();
				if(is_null($this->data))
					$this->data = new KalturaDeleteJobData();
				break;
				
			default:
				$dbData = KalturaPluginManager::loadObject('kJobData', $dbBatchJob->getJobType());
				if(is_null($this->data)) {
					$this->data = KalturaPluginManager::loadObject('KalturaJobData', $this->jobType);
				}
		}
		
		if(is_null($dbBatchJob->getData()))
			$dbBatchJob->setData($dbData);
	
		if($this->data instanceof KalturaJobData)
		{
			$dbData = $this->data->toObject($dbBatchJob->getData());
			$dbBatchJob->setData($dbData);
		}
		
		return $dbData;
	}
	
	public function toObject($dbBatchJob = null, $props_to_skip = array())
	{
		if(is_null($dbBatchJob))
			$dbBatchJob = new BatchJob();

		$dbBatchJob = parent::toObject($dbBatchJob);
		
		if (!is_null($this->data))
		    $this->toData($dbBatchJob);
		if(!is_null($this->jobSubType) && $this->data instanceof KalturaJobData)
			$dbBatchJob->setJobSubType($this->data->toSubType($this->jobSubType));
		
		return $dbBatchJob;
	}   
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	} 
}
