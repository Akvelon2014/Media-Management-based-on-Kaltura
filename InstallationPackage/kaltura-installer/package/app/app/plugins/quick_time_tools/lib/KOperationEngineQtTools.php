<?php
/**
 * @package plugins.quickTimeTools
 * @subpackage lib
 */
class KOperationEngineQtTools  extends KSingleOutputOperationEngine
{
	protected $tmpFolder;
	
	public function configure(KSchedularTaskConfig $taskConfig, KalturaConvartableJobData $data, KalturaClient $client)
	{
		parent::configure($taskConfig, $data, $client);
		$this->tmpFolder = $taskConfig->params->localTempPath;
	}
	
	public function operate(kOperator $operator = null, $inFilePath, $configFilePath = null)
	{
		$qtInFilePath = "$this->tmpFolder/$inFilePath.stb";

		if(symlink($inFilePath, $qtInFilePath))
			$inFilePath = $qtInFilePath;
		
		parent::operate($operator, $inFilePath, $configFilePath);
	}
}
