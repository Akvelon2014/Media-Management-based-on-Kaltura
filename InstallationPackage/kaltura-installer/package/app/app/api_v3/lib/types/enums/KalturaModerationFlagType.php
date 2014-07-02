<?php
/**
 * @package api
 * @subpackage enum
 */
class KalturaModerationFlagType extends KalturaEnum
{
	const SEXUAL_CONTENT = 1;
	const VIOLENT_REPULSIVE = 2;
	const HARMFUL_DANGEROUS = 3;
	const SPAM_COMMERCIALS = 4; 
	const COPYRIGHT = 5;
	const TERMS_OF_USE_VIOLATION = 6;
}