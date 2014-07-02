<?php


/**
 * Skeleton subclass for representing a row from the 'invalid_session' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class invalidSession extends BaseinvalidSession {

	public function getCacheInvalidationKeys()
	{
		return array("invalidSession:ks=".$this->getKs());
	}
} // invalidSession
