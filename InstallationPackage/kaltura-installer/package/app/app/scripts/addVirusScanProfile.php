<?php

error_reporting(E_ALL);
require_once(dirname(__FILE__).'/../alpha/config/sfrootdir.php');
require_once(dirname(__FILE__).'/../api_v3/bootstrap.php');

DbManager::setConfig(kConf::getDB());
DbManager::initialize();

/**************************************************
* PLEASE CONFIGURE REQUIRED SETTINGS
***************************************************/

// partner's ID - must be set!
$partnerId = null;

// please enter a name for the profile:
$profileName = 'virusScan';

// please entery profile's status (enabled/disabled):
$profileStatus = KalturaVirusScanProfileStatus::ENABLED; // can be changed to KalturaVirusScanProfileStatus::DISABLED

// please choose engine type:
$engineType = SymantecScanEnginePlugin::getPluginName() . IKalturaEnumerator::PLUGIN_VALUE_DELIMITER . SymantecScanEngineVirusScanEngineType::SYMANTEC_SCAN_ENGINE; // Value from KalturaVirusScanEngineType

// action if file is found infected:
$actionIfInfected = KalturaVirusFoundAction::CLEAN_DELETE;

// please enter required parameters for entry filter - only entries that suit the filter will be scanned by this profile
$entryFilter = new KalturaBaseEntryFilter();

$entryFilter->typeIn = KalturaEntryType::MEDIA_CLIP . "," . KalturaEntryType::MIX;

/**************************************************
* DON'T TOUCH THE FOLLOWING CODE
***************************************************/

if (!$partnerId)
{
      die('$partnerId cannot be empty');
}

$profile = new KalturaVirusScanProfile();
$profile->name = $profileName;
$profile->status = $profileStatus;
$profile->engineType = $engineType;
$profile->entryFilter = $entryFilter;
$profile->actionIfInfected = $actionIfInfected;

$dbProfile = null;
$dbProfile = $profile->toObject($dbProfile);
$dbProfile->setPartnerId($partnerId);
$dbProfile->save();

die('Done!');
