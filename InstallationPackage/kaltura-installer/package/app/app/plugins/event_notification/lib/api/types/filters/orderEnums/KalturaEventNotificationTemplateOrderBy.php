<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.filters.enum
 */
class KalturaEventNotificationTemplateOrderBy extends KalturaStringEnum
{
	const ID_ASC = "+id";
	const ID_DESC = "-id";
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
}
