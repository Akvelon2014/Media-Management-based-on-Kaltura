<?php
/**
 * @package plugins.audit
 * @subpackage errors
 */
class kAuditTrailException extends kCoreException
{
	const UNIQUE_ID_NOT_GENERATED = "UNIQUE_ID_NOT_GENERATED";
	const OBJECT_TYPE_NOT_ALLOWED = "OBJECT_TYPE_NOT_ALLOWED";
	const OBJECT_TYPE_DISABLED = "OBJECT_TYPE_DISABLED";
}