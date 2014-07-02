<?php
require_once 'Configuration.php';

class KettleRunner
{
	public static function execute($job, $params=array())
	{
		global $CONF;
		
		$exec = 'kitchen.sh';
		
		#find if file ends with '.ktr' - so we need pan and not kitchen:
		if(substr($job, -4) === '.ktr')
		{
			$exec = 'pan.sh';
		}
		
		$args = ' /file ' .$CONF->EtlBasePath.$job;		
		foreach ($params as $k => $v)
		{
			$args=$args.' -param:'.$k.'=\''.$v.'\'';
		}
		putenv('KETTLE_HOME='.Configuration::$KETTLE_HOME);
		passthru('export KETTLE_HOME='.Configuration::$KETTLE_HOME.';/usr/local/pentaho/pdi/'.$exec.$args);
	}
}
?>
