<?php
/**
 * @package UI-infra
 * @subpackage bootstrap
 */
class Infra_InfraLoader implements Zend_Loader_Autoloader_Interface
{
	public function Infra_InfraLoader()
	{
		$infaDir = realpath(dirname(__FILE__) . '/../../infra/');
		$pluginsDir = realpath(dirname(__FILE__) . '/../../plugins/');
		
		require_once($infaDir . DIRECTORY_SEPARATOR . 'bootstrap_base.php');
		require_once($infaDir . DIRECTORY_SEPARATOR . 'KAutoloader.php');
		KAutoloader::setClassPath(array($infaDir . DIRECTORY_SEPARATOR . '*'));
		KAutoloader::addClassPath(KAutoloader::buildPath($pluginsDir, '*'));
		KAutoloader::setClassMapFilePath(kConf::get("cache_root_path") . '/admin/classMap.cache');
		KAutoloader::register();
	}
	
	public function autoload($class)
	{
		KAutoloader::autoload($class);
	}
}