<?php

/**
 * Subclass for performing query and update operations on the 'conversion_profile_2' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class conversionProfile2Peer extends BaseconversionProfile2Peer
{
	public static function alternativeCon($con, $queryDB = kQueryCache::QUERY_DB_UNDEFINED)
	{
		if($con === null)
			$con = myDbHelper::alternativeCon($con);
			
		if($con === null)
			$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL3);
		
		return $con;
	}

	public static function setDefaultCriteriaFilter ()
	{
		if ( self::$s_criteria_filter == null )
		{
			self::$s_criteria_filter = new criteriaFilter ();
		}

		$c = new Criteria();
		$c->add ( self::DELETED_AT, null, Criteria::EQUAL );
		$c->add ( self::STATUS, ConversionProfileStatus::DELETED, Criteria::NOT_EQUAL );
		self::$s_criteria_filter->setFilter ( $c );
	}

	public static function retrieveByPKNoFilter ($pk, $con = null)
	{
		self::setUseCriteriaFilter ( false );
		$res = parent::retrieveByPK( $pk , $con );
		self::setUseCriteriaFilter ( true );
		return $res;
	}

	public static function retrieveByPKsNoFilter ($pks, $con = null)
	{
		self::setUseCriteriaFilter ( false );
		$res = parent::retrieveByPKs( $pks , $con );
		self::setUseCriteriaFilter ( true );
		return $res;
	}

	public static function getIds(Criteria $criteria, $con = null)
	{
		$criteria->addSelectColumn(conversionProfile2Peer::ID);

		$stmt = conversionProfile2Peer::doSelectStmt($criteria, $con);
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
	public static function getCacheInvalidationKeys()
	{
		return array(array("conversionProfile2:partnerId=%s", self::PARTNER_ID));		
	}
}
