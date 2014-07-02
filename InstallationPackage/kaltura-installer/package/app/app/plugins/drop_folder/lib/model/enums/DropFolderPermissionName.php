<?php

/**
 * @package plugins.dropFolder
 * @subpackage model.enum
 */ 
class DropFolderPermissionName implements IKalturaPluginEnum, PermissionName
{
	const SYSTEM_ADMIN_DROP_FOLDER_BASE = 'SYSTEM_ADMIN_DROP_FOLDER_BASE';
	const SYSTEM_ADMIN_DROP_FOLDER_MODIFY = 'SYSTEM_ADMIN_DROP_FOLDER_MODIFY';
	const CONTENT_INGEST_DROP_FOLDER_BASE = 'CONTENT_INGEST_DROP_FOLDER_BASE';
	const CONTENT_INGEST_DROP_FOLDER_MODIFY = 'CONTENT_INGEST_DROP_FOLDER_MODIFY';
	
	public static function getAdditionalValues()
	{
		return array
		(
			'SYSTEM_ADMIN_DROP_FOLDER_BASE' => self::SYSTEM_ADMIN_DROP_FOLDER_BASE,
			'SYSTEM_ADMIN_DROP_FOLDER_MODIFY' => self::SYSTEM_ADMIN_DROP_FOLDER_MODIFY,
			'CONTENT_INGEST_DROP_FOLDER_BASE' => self::CONTENT_INGEST_DROP_FOLDER_BASE,
			'CONTENT_INGEST_DROP_FOLDER_MODIFY' => self::CONTENT_INGEST_DROP_FOLDER_MODIFY,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
