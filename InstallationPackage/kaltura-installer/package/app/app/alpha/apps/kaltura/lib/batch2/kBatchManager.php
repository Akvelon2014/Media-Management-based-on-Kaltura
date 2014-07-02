<?php

/**
 * 
 * Manages the batch mechanism
 *  
 * @package Core
 * @subpackage Batch
 */
class kBatchManager
{
	/**
	 * @var BatchJob
	 */
	protected static $currentUpdatingJob;
	
	/**
	 * @return BatchJob
	 */
	public static function getCurrentUpdatingJob()
	{
		return self::$currentUpdatingJob;
	}
	
	/**
	 * batch createFlavorAsset orgenize a convert job data 
	 * 
	 * @param flavorParamsOutputWrap $flavor
	 * @param int $partnerId
	 * @param int $entryId
	 * @param string $flavorAssetId
	 * @return flavorAsset
	 */
	public static function createFlavorAsset(flavorParamsOutputWrap $flavor, $partnerId, $entryId, $flavorAssetId = null)
	{
		$description = kBusinessConvertDL::parseFlavorDescription($flavor);
		
		$flavorAsset = null;
		if($flavorAssetId)
			$flavorAsset = assetPeer::retrieveById($flavorAssetId);
		
		if(!$flavorAsset)
			$flavorAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $flavor->getFlavorParamsId());
		
		if($flavorAsset)
		{
			$description = $flavorAsset->getDescription() . "\n" . $description;
			$flavorAsset->setDescription($description);
//			$flavorAsset->incrementVersion();
		}	
		else
		{
			// creates the flavor asset 
			$flavorAsset = new flavorAsset();
			$flavorAsset->setPartnerId($partnerId);
			$flavorAsset->setEntryId($entryId);
			$flavorAsset->setDescription($description);
		}
		
		$flavorAsset->setTags($flavor->getTags());
		$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_QUEUED);
		$flavorAsset->setFlavorParamsId($flavor->getFlavorParamsId());
		$flavorAsset->setFileExt($flavor->getFileExt());
		
		// decided by the business logic layer
		if($flavor->_create_anyway)
		{
			KalturaLog::log("Flavor [" . $flavor->getFlavorParamsId() . "] selected to be created anyway");
		}
		else
		{
			if(!$flavor->IsValid())
			{
				KalturaLog::log("Flavor [" . $flavor->getFlavorParamsId() . "] is invalid");
				$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
				$flavorAsset->save();	
				return null;
			}
			
			if($flavor->_force)
			{
				KalturaLog::log("Flavor [" . $flavor->getFlavorParamsId() . "] is forced");
			}
			else
			{
				if($flavor->_isNonComply)
				{
					KalturaLog::log("Flavor [" . $flavor->getFlavorParamsId() . "] is none-comply");
					$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE);
					$flavorAsset->save();	
					return null;
				}

				$vidCodec=$flavor->getVideoCodec();
				if(($flavor->_isRedundant) && !isset($vidCodec))
				{
					KalturaLog::log("Flavor [" . $flavor->getFlavorParamsId() . "] is redandant audio-only");
					$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_NOT_APPLICABLE);
					$flavorAsset->save();
					return null;
				}
				
				KalturaLog::log("Flavor [" . $flavor->getFlavorParamsId() . "] is valid");
			}
		}
		$flavorAsset->save();
		
		// save flavor params
		$flavor->setPartnerId($partnerId);
		$flavor->setEntryId($entryId);
		$flavor->setFlavorAssetId($flavorAsset->getId());
		$flavor->setFlavorAssetVersion($flavorAsset->getVersion());
		$flavor->save();
			
		return $flavorAsset;
	}
	
	/**
	 * batch createFlavorAsset orgenize a convert job data 
	 * 
	 * @param flavorParamsOutputWrap $flavor
	 * @param int $partnerId
	 * @param int $entryId
	 * @param string $description
	 * @return flavorAsset
	 */
	public static function createErrorFlavorAsset(flavorParamsOutputWrap $flavor, $partnerId, $entryId, $description)
	{
		$flavorAsset = assetPeer::retrieveByEntryIdAndParams($entryId, $flavor->getFlavorParamsId());
		
		if($flavorAsset)
		{
			$description = $flavorAsset->getDescription() . "\n" . $description;
			$flavorAsset->setDescription($description);
//			$flavorAsset->incrementVersion();
		}	
		else
		{
			// creates the flavor asset 
			$flavorAsset = new flavorAsset();
			$flavorAsset->setPartnerId($partnerId);
			$flavorAsset->setEntryId($entryId);
			$flavorAsset->setDescription($description);
		}
		
		$flavorAsset->setTags($flavor->getTags());
		$flavorAsset->setStatus(flavorAsset::FLAVOR_ASSET_STATUS_ERROR);
		$flavorAsset->setFlavorParamsId($flavor->getFlavorParamsId());
		$flavorAsset->setFileExt($flavor->getFileExt());
		$flavorAsset->save();
		
		// save flavor params
		$flavor->setPartnerId($partnerId);
		$flavor->setEntryId($entryId);
		$flavor->setFlavorAssetId($flavorAsset->getId());
		$flavor->setFlavorAssetVersion($flavorAsset->getVersion());
		$flavor->save();
			
		return $flavorAsset;
	}
	
	
	/**
	 * batch addMediaInfo adds a media info and updates the flavor asset 
	 * 
	 * @param mediaInfo $mediaInfoDb  
	 * @return mediaInfo 
	 */
	public static function addMediaInfo(mediaInfo $mediaInfoDb)
	{
		$mediaInfoDb->save();
		KalturaLog::log("Added media info [" . $mediaInfoDb->getId() . "] for flavor asset [" . $mediaInfoDb->getFlavorAssetId() . "]");
		
		if(!$mediaInfoDb->getFlavorAssetId())
			return $mediaInfoDb;
			
		$flavorAsset = assetPeer::retrieveById($mediaInfoDb->getFlavorAssetId());
		if(!$flavorAsset)
			return $mediaInfoDb;

		if($flavorAsset->getIsOriginal())
		{
			KalturaLog::log("Media info is for the original flavor asset");
			$tags = null;
			
			$profile = myPartnerUtils::getConversionProfile2ForEntry($flavorAsset->getEntryId());
			if($profile)
				$tags = $profile->getInputTagsMap();
			KalturaLog::log("Flavor asset tags from profile [$tags]");
			
			if(!is_null($tags))
			{
				$tagsArray = explode(',', $tags);
				
				// support for old migrated profiles
				if($profile->getCreationMode() == conversionProfile2::CONVERSION_PROFILE_2_CREATION_MODE_AUTOMATIC_BYPASS_FLV)
				{
					if(!KDLWrap::CDLIsFLV($mediaInfoDb))
					{
						$key = array_search(flavorParams::TAG_MBR, $tagsArray);
						unset($tagsArray[$key]);
					}
				}
				
				$finalTagsArray = KDLWrap::CDLMediaInfo2Tags($mediaInfoDb, $tagsArray);
				$finalTags = join(',', array_unique($finalTagsArray));
				KalturaLog::log("Flavor asset tags from KDL [$finalTags]");
//KalturaLog::log("Flavor asset tags [".print_r($flavorAsset->setTags(),1)."]");
				$flavorAsset->addTags($finalTagsArray);
			}
		}
		else 
		{
			KalturaLog::log("Media info is for the destination flavor asset");
			$tags = null;
			
			$flavorParams = assetParamsPeer::retrieveByPK($flavorAsset->getFlavorParamsId());
			if($flavorParams)
				$tags = $flavorParams->getTags();
			KalturaLog::log("Flavor asset tags from flavor params [$tags]");
			
			if(!is_null($tags))
			{
				$tagsArray = explode(',', $tags);
				$assetTagsArray = $flavorAsset->getTagsArray();
				foreach($assetTagsArray as $tag)
					$tagsArray[] = $tag;
				
				if(!KDLWrap::CDLIsFLV($mediaInfoDb))
				{
					$key = array_search(flavorParams::TAG_MBR, $tagsArray);
					unset($tagsArray[$key]);
				}
				
				$finalTagsArray = $tagsArray;
//				bypass, KDLWrap::CDLMediaInfo2Tags doesn't support destination flavors and mobile tags
//				$finalTagsArray = KDLWrap::CDLMediaInfo2Tags($mediaInfoDb, $tagsArray);

				$finalTags = join(',', array_unique($finalTagsArray));
				KalturaLog::log("Flavor asset tags from KDL [$finalTags]");
				$flavorAsset->setTags($finalTags);
			}
		}
				
		KalturaLog::log("KDLWrap::ConvertMediainfoCdl2FlavorAsset(" . $mediaInfoDb->getId() . ", " . $flavorAsset->getId() . ");");
		KDLWrap::ConvertMediainfoCdl2FlavorAsset($mediaInfoDb, $flavorAsset);
		$flavorAsset->save();

//		if(!$flavorAsset->hasTag(flavorParams::TAG_MBR))
//			return $mediaInfoDb;
			
		$entry = entryPeer::retrieveByPK($flavorAsset->getEntryId());
		if(!$entry)
			return $mediaInfoDb;
		
		$contentDuration = $mediaInfoDb->getContainerDuration();
		if (!$contentDuration)
		{
			$contentDuration = $mediaInfoDb->getVideoDuration();
			if (!$contentDuration)
				$contentDuration = $mediaInfoDb->getAudioDuration();
		}
		
		if ($contentDuration)
		{
			$entry->setLengthInMsecs($contentDuration);
		}
		
		if($mediaInfoDb->getVideoWidth() && $mediaInfoDb->getVideoHeight())
		{
        		$entry->setDimensions($mediaInfoDb->getVideoWidth(), $mediaInfoDb->getVideoHeight());
		}
				
		$entry->save();
		return $mediaInfoDb;
	} 
	
	// common to all the jobs using the BatchJob table 
	public static function freeExclusiveBatchJob($id, kExclusiveLockKey $lockKey, $resetExecutionAttempts = false)
	{
		return kBatchExclusiveLock::freeExclusive($id, $lockKey, $resetExecutionAttempts);
	}
	
	public static function getQueueSize($schedulerId, $workerId, $jobType, $filter)
	{
		$priority = self::getNextJobPriority($jobType);
		
		$c = new Criteria();
		$filter->attachToCriteria($c);
		return kBatchExclusiveLock::getQueueSize($c, $schedulerId, $workerId, $priority, $jobType);
		
		
//		// gets queues length
//		$c = new Criteria();
//		$filter->attachToCriteria($c);
//		
//		$crit = $c->getNewCriterion(BatchJobPeer::CHECK_AGAIN_TIMEOUT, time(), Criteria::LESS_THAN);
//		$crit->addOr($c->getNewCriterion(BatchJobPeer::CHECK_AGAIN_TIMEOUT, null, Criteria::ISNULL));
//		$c->addAnd($crit);
//		
//		$queueSize = BatchJobPeer::doCount($c, false, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
//		
//		// gets queues length
//		$c = new Criteria();
//		$c->add(BatchJobPeer::SCHEDULER_ID, $schedulerId);
//		$c->add(BatchJobPeer::WORKER_ID, $workerId);
//		$c->add(BatchJobPeer::PROCESSOR_EXPIRATION, time(), Criteria::LESS_THAN);
//		$c->add(BatchJobPeer::EXECUTION_ATTEMPTS, BatchJobPeer::getMaxExecutionAttempts($jobType), Criteria::LESS_THAN);
//		$c->add(BatchJobPeer::JOB_TYPE, $jobType);
//		$queueSize += BatchJobPeer::doCount($c, false, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
//		
//		return $queueSize;
	}
	
	public static function cleanExclusiveJobs()
	{
		$jobs = kBatchExclusiveLock::getExpiredJobs();
		foreach($jobs as $job)
		{
			KalturaLog::log("Cleaning job id[" . $job->getId() . "]");
			kJobsManager::updateBatchJob($job, BatchJob::BATCHJOB_STATUS_FATAL);
		}
		
		$c = new Criteria();
		$c->add(BatchJobPeer::STATUS, BatchJobPeer::getClosedStatusList(), Criteria::IN);
		$c->add(BatchJobPeer::BATCH_INDEX, null, Criteria::ISNOTNULL);
			// The 'closed' jobs should be donn for at least 10min. 
			// before the cleanup starts messing upo with'em
			// This solves cases when job (convert) completes succesfully, 
			// but the next job (closure)does not get a chance to take over due to the clean-up
		$c->add(BatchJobPeer::FINISH_TIME, time()-600, Criteria::LESS_THAN); 
		
		// MUST be the master DB
		$jobs = BatchJobPeer::doSelect($c, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_MASTER));
		foreach($jobs as $job)
		{
			KalturaLog::log("Cleaning job id[" . $job->getId() . "]");
			$job->setSchedulerId(null);
			$job->setWorkerId(null);
			$job->setBatchIndex(null);
			$job->setProcessorExpiration(null);
			$job->save();
		}
			
		return count($jobs);
	}
	
	/**
	 * Common to all the jobs using the BatchJob table
	 * 
	 * @param unknown_type $id
	 * @param kExclusiveLockKey $lockKey
	 * @param BatchJob $dbBatchJob
	 * @return Ambigous <BatchJob, NULL, unknown, multitype:>
	 */
	public static function updateExclusiveBatchJob($id, kExclusiveLockKey $lockKey, BatchJob $dbBatchJob)
	{
		self::$currentUpdatingJob = $dbBatchJob;
		
		$dbBatchJob = kBatchExclusiveLock::updateExclusive($id, $lockKey, $dbBatchJob);
		
		$event = new kBatchJobStatusEvent($dbBatchJob);
		kEventsManager::raiseEvent($event);
		
		$dbBatchJob->reload();
		return $dbBatchJob;
	}
	
	public static function getExclusiveAlmostDoneJobs(kExclusiveLockKey $lockKey, $maxExecutionTime, $numberOfJobs, $jobType, BatchJobFilter $filter)
	{
		$priority = self::getNextJobPriority($jobType);
		
		$c = new Criteria();
		$filter->attachToCriteria($c);
		return kBatchExclusiveLock::getExclusiveAlmostDoneJobs($c, $lockKey, $maxExecutionTime, $numberOfJobs, $priority, $jobType);
	}

	private static function getNextJobPriorityFromCache($jobType)
	{
		if (!function_exists('apc_fetch'))
			return false;
			
		$priority = apc_fetch("getNextJobPriority:$jobType:priority");
		if ($priority !== false) // found priority in cache
		{
			$cacheExpiry = kConf::hasParam("get_next_job_priority_default_expiry") ? kConf::get("get_next_job_priority_default_expiry") : 2;
			$cachedTime = apc_fetch("getNextJobPriority:$jobType:time");

			if ($cachedTime === false || $cachedTime + $cacheExpiry < time()) // cache expired
			$priority = false;
		}

		KalturaLog::debug("getNextJobPriorityFromCache jobType:$jobType $priority:$priority ".($priority === false ? "nocache" : "cache"));

		return $priority;
	}


	private static function saveNextJobPriorityInCache($jobType, $priority)
	{
		if (function_exists('apc_store'))
		{
			apc_store("getNextJobPriority:$jobType:priority", $priority);
			apc_store("getNextJobPriority:$jobType:time", time());
	
			KalturaLog::debug("saveNextJobPriorityInCache jobType:$jobType $priority:$priority time:".time());
		}

		return $priority;
	}


	/*
	 * Find what is the priority that should be used for next task
	 */
	public static function getNextJobPriority($jobType)
	{
		$priority = self::getNextJobPriorityFromCache($jobType);
		if ($priority !== false)
			return $priority;

		//$priorities = array(1 => 33, 2 => 27, 3 => 20, 4 => 13, 5 => 7);
		$priorities = kConf::get('priority_percent');

		$createdAt = time() - kConf::get('priority_time_range');

		$c = new Criteria();
		$c->add(BatchJobPeer::CREATED_AT, $createdAt, Criteria::GREATER_THAN);
		$c->add(BatchJobPeer::JOB_TYPE, $jobType);
		$c->add(BatchJobPeer::STATUS, BatchJob::BATCHJOB_STATUS_PENDING);
		$c->clearSelectColumns();
		$c->addSelectColumn('MAX(' . BatchJobPeer::PRIORITY . ')');
		$stmt = BatchJobPeer::doSelectStmt($c, myDbHelper::getConnection    (myDbHelper::DB_HELPER_CONN_PROPEL2));
		$maxPriority = $stmt->fetchColumn();

		// gets the current queues
		$c = new Criteria();
		$c->add(BatchJobPeer::CREATED_AT, $createdAt, Criteria::GREATER_THAN);
		$c->add(BatchJobPeer::JOB_TYPE, $jobType);
		$c->add(BatchJobPeer::STATUS, BatchJob::BATCHJOB_STATUS_PENDING, Criteria::GREATER_THAN);
		$c->addGroupByColumn(BatchJobPeer::PRIORITY);

		// To prevent stress on the master DB - use the slave for checking the queue sizes
		$queues = BatchJobPeer::doCountGroupBy($c, myDbHelper::getConnection    (myDbHelper::DB_HELPER_CONN_PROPEL2));

		// copy the queues and calcs the total
		$total = 0;
		$queues_size = array();
		foreach($queues as $queue)
		{
			$queues_size[$queue['PRIORITY']] = $queue[BatchJobPeer::COUNT];
			$total += $queue[BatchJobPeer::COUNT];
		}

		$result = 1;

		// go over the priorities and see if its percent not used
		foreach($priorities as $priority => $top_percent)
		{
			if($priority > $maxPriority)
			continue;

			if(! isset($queues_size[$priority]))
			{
				$result = $priority;
				break;
			}

			$percent = $queues_size[$priority] / ($total / 100);
			if($percent < $top_percent)
			{
				$result = $priority;
				break;
			}
		}

		return self::saveNextJobPriorityInCache($jobType, $result);
    }
	
	public static function updateEntry($entryId, $status)
	{
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry) {
			KalturaLog::err("Entry was not found for id [$entryId]");
			return null;
		}
		
		// entry status didn't change - no need to send notification
		if($entry->getStatus() == $status)
			return $entry;
		
		// backward compatibility 
		// if entry has kshow, and this is the first entry in the mix, 
		// the thumbnail of the entry should be copied into the mix entry  
		if ($status == entryStatus::READY && $entry->getKshowId())
			myEntryUtils::createRoughcutThumbnailFromEntry($entry, false);
			
		// entry status is ready and above, not changing status through batch job
		$unAcceptedStatuses = array(
			entryStatus::READY,
			entryStatus::DELETED,
		);
		
		if(in_array($entry->getStatus(), $unAcceptedStatuses))
		{
			KalturaLog::debug("Entry status [" . $entry->getStatus() . "] will not be changed");
			return $entry;
		}
		
		$entry->setStatus($status);
		$entry->save();
		
		myNotificationMgr::createNotification(kNotificationJobData::NOTIFICATION_TYPE_ENTRY_UPDATE, $entry, null, null, null, null, $entry->getId());
		
		return $entry;
	}

}