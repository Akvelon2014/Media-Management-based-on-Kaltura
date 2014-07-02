<?php

set_time_limit(0);

ini_set("memory_limit","700M");

chdir(dirname(__FILE__));

define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../'));
require_once(ROOT_DIR . '/infra/bootstrap_base.php');
require_once(ROOT_DIR . '/infra/KAutoloader.php');
require_once(ROOT_DIR . '/infra/kConf.php');

KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "*"));
KAutoloader::setClassMapFilePath(kConf::get("cache_root_path") . '/deploy/' . basename(__FILE__) . '.cache');
KAutoloader::register();

date_default_timezone_set(kConf::get("date_default_timezone"));

error_reporting(E_ALL);

KalturaLog::setLogger(new KalturaStdoutLogger());

$dbConf = kConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();

$c = new Criteria();

if($argc > 1 && is_numeric($argv[1]))
	$c->add(TagPeer::ID, $argv[1], Criteria::GREATER_EQUAL);

if($argc > 2 && is_numeric($argv[2]))
	$c->add(TagPeer::PARTNER_ID, $argv[2], Criteria::EQUAL);
	
$c->addAscendingOrderByColumn(TagPeer::ID);
$c->setLimit(10000);

$con = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2);
//$sphinxCon = DbManager::getSphinxConnection();

$tags = TagPeer::doSelect($c, $con);
$sphinx = new kSphinxSearchManager();
while(count($tags))
{
	foreach($tags as $tag)
	{
	    /* @var $tag Tag */
		KalturaLog::log('tag id ' . $tag->getId() . ' tag string [' . $tag->getTag() . '] crc id[' . $sphinx->getSphinxId($tag) . ']');
		
		try {
			$ret = $sphinx->saveToSphinx($tag, true);
		}
		catch(Exception $e){
			KalturaLog::err($e->getMessage());
			exit -1;
		}
	}
	
	$c->setOffset($c->getOffset() + count($tags));
	kMemoryManager::clearMemory();
	$tags = TagPeer::doSelect($c, $con);
}

KalturaLog::log('Done');