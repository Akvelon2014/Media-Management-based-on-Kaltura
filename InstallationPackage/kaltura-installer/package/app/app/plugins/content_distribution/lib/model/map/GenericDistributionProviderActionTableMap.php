<?php


/**
 * This class defines the structure of the 'generic_distribution_provider_action' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package plugins.contentDistribution
 * @subpackage model.map
 */
class GenericDistributionProviderActionTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'plugins.contentDistribution.GenericDistributionProviderActionTableMap';

	/**
	 * Initialize the table attributes, columns and validators
	 * Relations are not initialized by this method since they are lazy loaded
	 *
	 * @return     void
	 * @throws     PropelException
	 */
	public function initialize()
	{
	  // attributes
		$this->setName('generic_distribution_provider_action');
		$this->setPhpName('GenericDistributionProviderAction');
		$this->setClassname('GenericDistributionProviderAction');
		$this->setPackage('plugins.contentDistribution');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
		$this->addColumn('PARTNER_ID', 'PartnerId', 'INTEGER', false, null, null);
		$this->addColumn('GENERIC_DISTRIBUTION_PROVIDER_ID', 'GenericDistributionProviderId', 'INTEGER', false, null, null);
		$this->addColumn('ACTION', 'Action', 'TINYINT', false, null, null);
		$this->addColumn('STATUS', 'Status', 'TINYINT', false, null, null);
		$this->addColumn('RESULTS_PARSER', 'ResultsParser', 'TINYINT', false, null, null);
		$this->addColumn('PROTOCOL', 'Protocol', 'INTEGER', false, null, null);
		$this->addColumn('SERVER_ADDRESS', 'ServerAddress', 'VARCHAR', false, 255, null);
		$this->addColumn('REMOTE_PATH', 'RemotePath', 'VARCHAR', false, 255, null);
		$this->addColumn('REMOTE_USERNAME', 'RemoteUsername', 'VARCHAR', false, 127, null);
		$this->addColumn('REMOTE_PASSWORD', 'RemotePassword', 'VARCHAR', false, 127, null);
		$this->addColumn('EDITABLE_FIELDS', 'EditableFields', 'VARCHAR', false, 255, null);
		$this->addColumn('MANDATORY_FIELDS', 'MandatoryFields', 'VARCHAR', false, 255, null);
		$this->addColumn('CUSTOM_DATA', 'CustomData', 'LONGVARCHAR', false, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
	} // buildRelations()

} // GenericDistributionProviderActionTableMap
