<?php


/**
 * Skeleton subclass for performing query and update operations on the 'invalid_session' table.
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
class invalidSessionPeer extends BaseinvalidSessionPeer {
	
	/**
	 * @param      ks $ks
	 * @param	   int $limit
	 * @return     invalidSession
	 */
	public static function actionsLimitKs(ks $ks, $limit)
	{
		$invalidSession = new invalidSession();
		$invalidSession->setKs($ks->getHash());
		$invalidSession->setActionsLimit($limit);
		$invalidSession->setKsValidUntil($ks->valid_until);
		$invalidSession->save();
		
		return $invalidSession;
	}
	
	/**
	 * @param      ks $ks
	 * @return     invalidSession
	 */
	public static function invalidateKs(ks $ks, PropelPDO $con = null)
	{
		$criteria = new Criteria();
		$criteria->add(invalidSessionPeer::KS, $ks->getHash());
		$invalidSession = invalidSessionPeer::doSelectOne($criteria, $con);
		
		if(!$invalidSession){
			$invalidSession = new invalidSession();
			$invalidSession->setKs($ks->getHash());
			$invalidSession->setKsValidUntil($ks->valid_until);
		}
		
		$invalidSession->setActionsLimit(null);
		$invalidSession->save();
		
		return $invalidSession;
	}
	
	public static function getCacheInvalidationKeys()
	{
		return array(array("invalidSession:ks=%s", self::KS));		
	}
} // invalidSessionPeer
