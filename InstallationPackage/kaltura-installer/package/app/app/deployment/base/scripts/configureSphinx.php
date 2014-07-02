<?php

define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../'));
define('SPHINX_CONFIG_DIR', ROOT_DIR . '/configurations/sphinx/');

require_once(ROOT_DIR . '/infra/kConf.php');
require_once(ROOT_DIR . '/infra/bootstrap_base.php');
require_once(ROOT_DIR . '/infra/KAutoloader.php');

KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "*"));
KAutoloader::setClassMapFilePath(kConf::get("cache_root_path") . '/deploy/' . basename(__FILE__) . '.cache');
KAutoloader::register();

require_once(SPHINX_CONFIG_DIR . '/SphinxBaseConfiguration.php');

$sphinxConfigHandler = fopen(SPHINX_CONFIG_DIR . 'kaltura.conf', 'w') or die("can't open configuration file");


if (!$baseConfigurations || !count($baseConfigurations))
	die ('missing sphinx base configuration');

$sphinxConfigurations = $baseConfigurations;
$sphinxConfigurationIndexs = $baseConfigurationIndexs;


$pluginInstances = KalturaPluginManager::getPluginInstances('IKalturaSphinxConfiguration');
foreach($pluginInstances as $pluginName => $pluginInstance)
{ 	/*@var $pluginInstance IKalturaSphinxConfiguration */
	$pluginSchema = $pluginInstance->getSphinxSchema();
	echo 'build schema for: '. $pluginName . PHP_EOL;
	
	foreach ($pluginSchema as $index => $schemaFields)
	{
		if (!isset($sphinxConfigurationIndexs[$index]))
			$sphinxConfigurationIndexs[$index] = array();
		
		foreach($schemaFields as $schemaFieldName => $schemaFieldValue){
				if($schemaFieldName != 'fields'){
					if (isset($sphinxConfigurationIndexs[$index][$schemaFieldName]))
						throw new Exception('duplicated fields ' . $schemaFieldName . ' for index ' . $index);
				
					$sphinxConfigurationIndexs[$index][$schemaFieldName] = $schemaFieldValue;
				}elseif ($schemaFieldName == 'fields'){
					foreach($schemaFieldValue as $schemaSubFieldName => $schemaSubFieldValue){						
						if (isset($sphinxConfigurationIndexs[$index][$schemaFieldName][$schemaSubFieldName]))
							throw new Exception('duplicated fields ' . $schemaFieldName . ' for index ' . $index);
						
						$sphinxConfigurationIndexs[$index][$schemaFieldName][$schemaSubFieldName] = $schemaSubFieldValue;
					}
				}
		}
	}
}

foreach ($sphinxConfigurationIndexs as $sphinxIndexName => $sphinxIndexValues){
	
	// applies default values
	$sphinxIndexValues = kSphinxSearchManager::getSphinxDefaultConfig($sphinxIndexValues);
	
	fwrite($sphinxConfigHandler, 'index ' . $sphinxIndexName . PHP_EOL . '{' . PHP_EOL);
	foreach ($sphinxIndexValues as $key => $value)
		if ($key == 'fields'){
			foreach ($value as $fieldValue => $fieldName){
				fwrite($sphinxConfigHandler, "\t" . $fieldName . "\t" . ' = ' . $fieldValue . PHP_EOL);	
			}
		}else{
			if ($key == 'path'){
				$value = $baseDir . $value;
			}
			fwrite($sphinxConfigHandler, "\t" . $key . "\t" . ' = ' . $value . PHP_EOL);
		}
		
	fwrite($sphinxConfigHandler, '}' . PHP_EOL);
}
foreach ($sphinxConfigurations as $sphinxConfigurationName => $sphinxConfigurationValues){
		fwrite($sphinxConfigHandler, $sphinxConfigurationName . PHP_EOL . '{' . PHP_EOL);
		foreach ($sphinxConfigurationValues as $key => $value)
			fwrite($sphinxConfigHandler, "\t" . $key . ' = ' . $value . PHP_EOL);
			
		fwrite($sphinxConfigHandler, '}'. PHP_EOL);
}

fclose($sphinxConfigHandler);
