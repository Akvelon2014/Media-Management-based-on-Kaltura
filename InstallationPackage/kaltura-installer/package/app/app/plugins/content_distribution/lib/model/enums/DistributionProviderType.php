<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.enum
 */
interface DistributionProviderType extends BaseEnum
{
	const GENERIC = 1;
	const SYNDICATION = 2;
}