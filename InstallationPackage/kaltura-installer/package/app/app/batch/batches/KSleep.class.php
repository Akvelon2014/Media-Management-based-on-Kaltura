<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */

/**
 * Will import a single URL and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	parse URL	(youTube is a special case) 
 * 		fetch heraders (to calculate the size of the file)
 * 		fetch file (update the job's progress - 100% is when the whole file as appeared in the header)
 * 		move the file to the archive
 * 		set the entry's new status and file details  (check if FLV) 
 *
 * @package Scheduler
 * @subpackage Debug
 */
class KSleep extends KPeriodicWorker
{
	public static function getType()
	{
		return -1;
	}
	
	
	/* (non-PHPdoc)
	 * @see KBatchBase::run()
	*/
	public function run($jobs = null)
	{
//		print_r ( $this->kClient );
		$r = rand ( 2,5);
		KalturaLog::info( "Slppeing for [$r]");
		sleep ( $r );		
		KalturaLog::info( "Bye!");		
	}
}
