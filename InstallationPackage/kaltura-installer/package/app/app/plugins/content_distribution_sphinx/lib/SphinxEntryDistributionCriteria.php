<?php
/**
 * @package plugins.contentDistribution
 * @subpackage DB
 */
class SphinxEntryDistributionCriteria extends SphinxCriteria
{
	public static $sphinxFields = array(
		EntryDistributionPeer::ID => 'entry_distribution_id',
		EntryDistributionPeer::CREATED_AT => 'created_at',
		EntryDistributionPeer::UPDATED_AT => 'updated_at',
		EntryDistributionPeer::SUBMITTED_AT => 'submitted_at',
		EntryDistributionPeer::ENTRY_ID => 'entry_id',
		EntryDistributionPeer::PARTNER_ID => 'partner_id',
		EntryDistributionPeer::DISTRIBUTION_PROFILE_ID => 'distribution_profile_id',
		EntryDistributionPeer::STATUS => 'entry_distribution_status',
		EntryDistributionPeer::DIRTY_STATUS => 'dirty_status',
		EntryDistributionPeer::THUMB_ASSET_IDS => 'thumb_asset_ids',
		EntryDistributionPeer::FLAVOR_ASSET_IDS => 'flavor_asset_ids',
		EntryDistributionPeer::SUNRISE => 'sunrise',
		EntryDistributionPeer::SUNSET => 'sunset',
		EntryDistributionPeer::SUN_STATUS => 'sun_status',
		EntryDistributionPeer::REMOTE_ID => 'remote_id',
		EntryDistributionPeer::PLAYS => 'plays',
		EntryDistributionPeer::VIEWS => 'views',
		EntryDistributionPeer::ERROR_TYPE => 'error_type',
		EntryDistributionPeer::ERROR_NUMBER => 'error_number',
		EntryDistributionPeer::LAST_REPORT => 'last_report',
		EntryDistributionPeer::NEXT_REPORT => 'next_report',
	);
	
	public static $sphinxOrderFields = array(
		EntryDistributionPeer::CREATED_AT => 'created_at',
		EntryDistributionPeer::UPDATED_AT => 'updated_at',
		EntryDistributionPeer::SUBMITTED_AT => 'submitted_at',
		EntryDistributionPeer::SUNRISE => 'sunrise',
		EntryDistributionPeer::SUNSET => 'sunset',
		EntryDistributionPeer::PLAYS => 'plays',
		EntryDistributionPeer::VIEWS => 'views',
		EntryDistributionPeer::LAST_REPORT => 'last_report',
		EntryDistributionPeer::NEXT_REPORT => 'next_report',
	);
	
	public static $sphinxTypes = array(
		'entry_distribution_id' => IIndexable::FIELD_TYPE_INTEGER,
		'created_at' => IIndexable::FIELD_TYPE_DATETIME,
		'updated_at' => IIndexable::FIELD_TYPE_DATETIME,
		'submitted_at' => IIndexable::FIELD_TYPE_DATETIME,
		'entry_id' => IIndexable::FIELD_TYPE_STRING,
		'partner_id' => IIndexable::FIELD_TYPE_INTEGER,
		'distribution_profile_id' => IIndexable::FIELD_TYPE_INTEGER,
		'entry_distribution_status' => IIndexable::FIELD_TYPE_INTEGER,
		'dirty_status' => IIndexable::FIELD_TYPE_INTEGER,
		'thumb_asset_ids' => IIndexable::FIELD_TYPE_STRING,
		'flavor_asset_ids' => IIndexable::FIELD_TYPE_STRING,
		'sunrise' => IIndexable::FIELD_TYPE_DATETIME,
		'sunset' => IIndexable::FIELD_TYPE_DATETIME,
		'sun_status' => IIndexable::FIELD_TYPE_INTEGER,
		'remote_id' => IIndexable::FIELD_TYPE_STRING,
		'plays' => IIndexable::FIELD_TYPE_INTEGER,
		'views' => IIndexable::FIELD_TYPE_INTEGER,
		'error_type' => IIndexable::FIELD_TYPE_INTEGER,
		'error_number' => IIndexable::FIELD_TYPE_INTEGER,
		'last_report' => IIndexable::FIELD_TYPE_DATETIME,
		'next_report' => IIndexable::FIELD_TYPE_DATETIME,
	);

	/**
	 * @return criteriaFilter
	 */
	protected function getDefaultCriteriaFilter()
	{
		return EntryDistributionPeer::getCriteriaFilter();
	}
	
	public function getSphinxOrderFields()
	{
		return self::$sphinxOrderFields;
	}
	
	/**
	 * @return string
	 */
	protected function getSphinxIndexName()
	{
		return kSphinxSearchManager::getSphinxIndexName(EntryDistributionPeer::TABLE_NAME);;
	}

	/* (non-PHPdoc)
	 * @see SphinxCriteria::getSphinxIdField()
	 */
	protected function getSphinxIdField()
	{
		return 'entry_distribution_id';
	}
	
	/* (non-PHPdoc)
	 * @see SphinxCriteria::getPropelIdField()
	 */
	protected function getPropelIdField()
	{
		return EntryDistributionPeer::ID;
	}
	
	/* (non-PHPdoc)
	 * @see SphinxCriteria::doCountOnPeer()
	 */
	protected function doCountOnPeer(Criteria $c)
	{
		return EntryDistributionPeer::doCount($c);
	}
	
	public function hasSphinxFieldName($fieldName)
	{
		return isset(self::$sphinxFields[$fieldName]);
	}
	
	public function getSphinxFieldName($fieldName)
	{
		if(!isset(self::$sphinxFields[$fieldName]))
			return $fieldName;
			
		return self::$sphinxFields[$fieldName];
	}
	
	public function getSphinxFieldType($fieldName)
	{
		if(!isset(self::$sphinxTypes[$fieldName]))
			return null;
			
		return self::$sphinxTypes[$fieldName];
	}
	
	public function hasMatchableField($fieldName)
	{
		return in_array($fieldName, array("thumb_asset_ids", "flavor_asset_ids"));
	}
}