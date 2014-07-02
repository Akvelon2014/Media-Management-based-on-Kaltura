<?php

$flavorParamsId = 0; // zero for new

$partnerId = 0;
$name = 'Adobe PDF - readonly';
$tags = 'pdf-readonly';
$description = 'Adobe PDF';
$readyBehavior = 2;
$isDefault = false;
$width = 0;
$height = 0;


$resolution = null;
$paperWidth = null;
$paperHeight = null;
$isReadonly = true;

/**************************************************
 * DON'T TOUCH THE FOLLOWING CODE
 ***************************************************/

error_reporting(E_ALL);
chdir(dirname(__FILE__));

require_once(realpath(dirname(__FILE__)).'/../../../alpha/config/sfrootdir.php');
define('SF_APP',         'kaltura');
define('SF_ENVIRONMENT', 'prod');
define('SF_DEBUG',       false);

define('MODULES' , SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR);

require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');


define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../'));
require_once(ROOT_DIR . '/infra/bootstrap_base.php');
require_once(ROOT_DIR . '/infra/KAutoloader.php');

KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"));
KAutoloader::setClassMapFilePath(kConf::get("cache_root_path") . '/scripts/' . basename(__FILE__) . '.cache');
KAutoloader::register();

date_default_timezone_set(kConf::get("date_default_timezone")); // America/New_York

KalturaLog::setLogger(new KalturaStdoutLogger());

DbManager::setConfig(kConf::getDB());
DbManager::initialize();

$flavorParams = null;

if($flavorParamsId)
{
	$flavorParams = assetParamsPeer::retrieveByPK($flavorParamsId);
	if(!($flavorParams instanceof PdfFlavorParams))
	{
		echo "Flavor params id [$flavorParamsId] is not PDF flavor params\n";
		exit;
	}
	$flavorParams->setVersion($flavorParams->getVersion() + 1);
}
else
{
	$flavorParams = new PdfFlavorParams();
	$flavorParams->setVersion(1);
	$flavorParams->setFormat(flavorParams::CONTAINER_FORMAT_PDF);
	$flavorParams->setVideoBitrate(1);
}

$pdfOperator = new kOperator();
$pdfOperator->id = conversionEngineType::PDF_CREATOR;
$operators = new kOperatorSets();
$operators->addSet(array($pdfOperator));

$flavorParams->setPartnerId($partnerId);
$flavorParams->setName($name);
$flavorParams->setTags($tags);
$flavorParams->setDescription($description);
$flavorParams->setReadyBehavior($readyBehavior);
$flavorParams->setIsDefault($isDefault);
$flavorParams->setWidth($width);
$flavorParams->setHeight($height);
$flavorParams->setOperators($operators->getSerialized());
$flavorParams->setEngineVersion(1);

// specific for pdf
$flavorParams->setResolution($resolution);
$flavorParams->setPaperWidth($paperWidth);
$flavorParams->setPaperHeight($paperHeight);
$flavorParams->setReadonly($isReadonly);
//$flavorParams->setType(DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::PDF)); for dragonfly
$flavorParams->save();

echo "Flavor params [" . $flavorParams->getId() . "] saved\n";
