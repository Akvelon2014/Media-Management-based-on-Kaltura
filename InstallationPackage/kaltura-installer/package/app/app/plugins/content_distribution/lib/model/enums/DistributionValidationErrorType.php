<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.enum
 */
interface DistributionValidationErrorType extends BaseEnum
{
	const CUSTOM_ERROR = 0;
	const STRING_EMPTY = 1;
	const STRING_TOO_LONG = 2;
	const STRING_TOO_SHORT = 3;
	const INVALID_FORMAT = 4;
}