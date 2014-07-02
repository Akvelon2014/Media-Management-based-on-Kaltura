<?php
/**
 * Applicative event that raised implicitly by the developer
 * @package Core
 * @subpackage events
 */
interface kBatchJobStatusEventConsumer extends KalturaEventConsumer
{
	/**
	 * @param BatchJob $dbBatchJob
	 * @param BatchJob $twinJob
	 * @return bool true if should continue to the next consumer
	 */
	public function updatedJob(BatchJob $dbBatchJob, BatchJob $twinJob = null);
	
	/**
	 * @param BatchJob $dbBatchJob
	 * @return bool true if the consumer should handle the event
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob);
}