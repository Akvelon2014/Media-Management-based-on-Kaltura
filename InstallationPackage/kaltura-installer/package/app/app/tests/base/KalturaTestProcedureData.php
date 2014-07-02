<?php
require_once(dirname(__FILE__) . '/../bootstrap/bootstrapServer.php');

/**
 * 
 * Represents a test procedure data 
 * @author Roni
 *
 */
class KalturaTestProcedureData
{
	/**
	 * 
	 * Creates a new test procedure data 
	 * @param string $testProcedureName
	 * @param array $testCasesData
	 */
	public function __construct($testProcedureName = "", array $testCasesData = array())
	{
		$this->procedureName = "$testProcedureName";
		$this->testCasesData = $testCasesData;
	}
	
	/**
	 * 
	 * The test procedure name
	 * @var string
	 */
	private $procedureName = null;
	
	/**
	 * 
	 * The test procedure test cases data
	 * @var array<KalturaTestCaseInstanceData>
	 */
	private $testCasesData = array();
	
	//TODO: add support for configuration in the tests (currently is passed through the test case additional data)
	/**
	 * 
	 * Defines the test procedure configuration (for all test case instances) 
	 * @var KalturaTestDataConfiguration
	 */
	private $testProcedureConfiguration = null; 

	/**
	 * 
	 * Adds a test case instance into the test procedure data
	 * @param KalturaTestCaseInstanceData $testCaseInstance
	 */
	public function addTestCaseInstance(KalturaTestCaseInstanceData $testCaseInstance)
	{
		if($this->testCasesData == null)
		{
			$this->testCasesData = array();
		}
		
		$name = $testCaseInstance->getTestCaseInstanceName();
		
		$this->testCasesData["$name"] = $testCaseInstance;
	}
	
	/**
	 * @return the $procedureName
	 */
	public function getProcedureName() {
		return $this->procedureName;
	}

	/**
	 * @return the $testCasesData
	 */
	public function getTestCasesData() {
		return $this->testCasesData;
	}
	
	/**
	 * @var string $testCaseName
	 * @return KalturaTestCaseInstanceData $testCasesData
	 */
	public function getTestCaseData($testCaseName) {
		if(isset($this->testCasesData[$testCaseName]))
		{
			KalturaLog::debug("testCaseName [$testCaseName] was found\n");
			return $this->testCasesData["$testCaseName"];
		}
		else
		{
			KalturaLog::debug("testCaseName [$testCaseName] was NOT FOUND\n");
			return null;
		}
	}

	/**
	 * @param string $procedureName
	 */
	public function setProcedureName($procedureName) {
		$this->procedureName = "$procedureName";
	}

	/**
	 * @param array<KalturaTestCaseInstanceData> $testCasesData
	 */
	public function setTestCasesData($testCasesData) {
		$this->testCasesData = $testCasesData;
	}

	/**
	 * 
	 * Creates a new test procedure data from a given test procedure data xml
	 * @param SimpleXMLElement $xmlTestProcedureData
	 * @return KalturaTestProcedureData
	 */
	public static function generateFromDataXml(SimpleXMLElement $xmlTestProcedureData)
	{
		$testProcedureData = new KalturaTestProcedureData();
		$testProcedureData->fromDataXml($xmlTestProcedureData);
		return $testProcedureData;
	}
	
	/**
	 * 
	 * Retruns a new 
	 * @param SimpleXMLElement $xmlTestProcedureData
	 */
	public function fromDataXml(SimpleXMLElement $xmlTestProcedureData)
	{
		if(isset($xmlTestProcedureData["testProcedureName"]))
		{
			$this->procedureName = ((string)$xmlTestProcedureData["testProcedureName"]);
		}
		
		foreach ($xmlTestProcedureData->TestCaseData as $testCaseInstanceDataXml)
		{
			$testCaseData = KalturaTestCaseInstanceData::generateFromDataXml($testCaseInstanceDataXml);
			$name = $testCaseData->getTestCaseInstanceName();
			$this->testCasesData["$name"] = $testCaseData;
		}
	}
	
	/**
	 * 
	 * Returns the given KalturaTestProcedureData as a DomDocument
	 * @param KalturaTestProcedureData $testProcedureData
	 */
	public static function toXml(KalturaTestProcedureData $testProcedureData)
	{
		$dom = new DOMDocument("1.0");
		
		$testProcedureDataElement = $dom->createElement("TestProcedureData");
		$testProcedureDataElement->setAttribute("testProcedureName", $testProcedureData->getProcedureName());
		$dom->appendChild($testProcedureDataElement);
					
		foreach ($testProcedureData->getTestCasesData() as $testCaseData)
		{
			$domTestCaseData = KalturaTestCaseInstanceData::toXml($testCaseData);
			kXml::appendDomToElement($domTestCaseData, $testProcedureDataElement, $dom);
		}
		
		return $dom;
	}

	/**
	 * 
	 * Checks if the given test case instance data exists
	 * @param string $testCaseInstanceKey
	 * @return bool - if the test case instance exists
	 */
	public function isTestCaseInstanceExists($testCaseInstanceKey)
	{
		$isExists = false;
		
		foreach ($this->testCasesData as $key => $testCaseData)
		{
			if($key == $testCaseInstanceKey)
			{
				$isExists = true;
				break;
			}
		}
		
		return $isExists; 
	}
}

