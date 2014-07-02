<?php
class UnitTestsGenerator extends ClientGeneratorFromPhp
{
	/**
	 * The files that we write to
	 */
	private $_txtBase = "";
	private $_txtTest = "";
	private $_txtIni = "";
	private $_txtXml = "";
	private $_txtXmlSource = "";
	
	/**
	 * Counts the actions that dependent on add action
	 * @var int
	 */
	private $dependencyIndex = 0;

	/**
	 *
	 * The last dependency test function
	 * @var string
	 */
	private $lastDependencyTest = "testFunction";

	protected function writeHeader(){}
	protected function writeFooter(){}
	protected function writeBeforeServices(){}
	protected function writeAfterServices(){}
	protected function writeBeforeTypes(){}
	protected function writeAfterTypes(){}
	protected function writeType(KalturaTypeReflector $type){}

	/**
	 * (non-PHPdoc)
	 * @see ClientGeneratorFromPhp::generate()
	 */
	public function generate()
	{
		parent::generate();

		foreach($this->_services as $serviceId => $serviceActionItem)
		{
		    /* @var $serviceActionItem KalturaServiceActionItem */
			if($serviceActionItem->serviceInfo->deprecated)
			    continue;

			$this->writeBeforeService($serviceActionItem);
			$this->writeService($serviceActionItem);
			$this->writeAfterService($serviceActionItem);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ClientGeneratorFromPhp::writeBeforeService()
	 */
	protected function writeBeforeService(KalturaServiceActionItem $serviceActionItem)
	{
		$this->dependencyIndex = 0;
		
		$serviceName = $serviceActionItem->serviceInfo->serviceName;
		$serviceClass = $serviceActionItem->serviceClass;

		$bootstrapPath = '/../../bootstrap.php';

		if(strpos($serviceActionItem->serviceId, "_"))
		{
			//			$serviceClass = $serviceReflector->getServiceClass();
			//			$servicePath = KAutoloader::getClassFilePath($serviceClass);
			//			$currentFolder = realpath(dirname($servicePath));
			//			$rootPath = realpath(dirname(__FILE__) . '/../');
			$upCounter = 3;
		}

		$this->_txtBase = '';
		$this->_txtTest = '';
		$this->_txtIni = '';
		$this->_txtXml = '';
		$this->_txtXmlSource = '';

		$this->writeXml("<?xml version='1.0'?>");
		$this->writeXmlSource("<?xml version='1.0'?>");
		$this->writeXmlSource("<TestsDataSource>");

		$this->writeBase("<?php");
		$this->writeBase("");
		$this->writeBase("/**");
		$this->writeBase(" * $serviceName service base test case.");
		$this->writeBase(" */");
		$this->writeBase("abstract class {$serviceClass}TestBase extends KalturaApiTestCase");
		$this->writeBase("{");
			
		$this->writeTest("<?php");
		$this->writeTest("");
		$this->writeTest("require_once(dirname(__FILE__) . '$bootstrapPath');");
		//$this->writeTest("require_once(dirname(__FILE__) . '/{$serviceClass}TestBase.php');"); no need to add this. files are added in the bootstrap
		
		$this->writeTest("");
		$this->writeTest("/**");
		$this->writeTest(" * $serviceName service test case.");
		$this->writeTest(" */");
		$this->writeTest("class {$serviceClass}Test extends {$serviceClass}TestBase");
		$this->writeTest("{");

		//Writes the SetUp function for the test
		//$this->writeSetUpServiceFunction($serviceReflector);

		$this->writeIni("[config]");
		$this->writeIni("source                                            = ini");
		$this->writeIni("serviceUrl                                        = @SERVICE_URL@");
		$this->writeIni("partnerId                                         = @TEST_PARTNER_ID@");
		$this->writeIni("clientTag                                         = unitTest");
		$this->writeIni("curlTimeout                                       = 90");
		$this->writeIni("startSession                                      = 1");
		$this->writeIni("secret                                            = @TEST_PARTNER_ADMIN_SECRET@");
		$this->writeIni("userId                                            = ");
		$this->writeIni("sessionType                                       = 2");
		$this->writeIni("expiry                                            = 86400");
		$this->writeIni("privileges                                        = ");

		$this->writeXml("<TestCaseData testCaseName=\"{$serviceClass}Test\">");
		$this->writeXmlSource("	<TestCaseData testCaseName=\"{$serviceClass}Test\">");
	}
		
	/**
	 *
	 * Add the set up function for the service
	 * @param KalturaServiceReflector $serviceReflector
	 */
	protected function writeSetUpServiceFunction(KalturaServiceActionItem $serviceReflector)
	{
		$this->writeTest("	/**");
		$this->writeTest("	 * Set up the test initial data");
		$this->writeTest("	 */");
		$this->writeTest("	protected function setUp()");
		$this->writeTest("	{");
		$this->writeTest("		parent::setUp();");
		$this->writeTest("	}");
		$this->writeTest("");

		$this->writeBase("	/**");
		$this->writeBase("	 * Set up the test initial data");
		$this->writeBase("	 */");
		$this->writeBase("	protected function setUp()");
		$this->writeBase("	{");
		$actions = $serviceReflector->actionMap;

		foreach ($actions as $actionId=>$actionReflector)
		{
			$actionName = ucfirst($actionId);
			$this->writeBase("		\$this->set{$actionName}TestData();");
		}

		$this->writeBase("");
		$this->writeBase("		parent::setUp();");
		$this->writeBase("	}");
		$this->writeBase("");

		foreach ($actions as $actionId=>$actionReflector) //creates the methods for the data set up
		{
			$actionName = ucfirst($action);
				
			$this->writeBase("	/**");
			$this->writeBase("	 * Set up the test{$actionName} initial data (If needed)");
			$this->writeBase("	 */");
			$this->writeBase("	protected function set{$actionName}TestData(){}");
			$this->writeBase("");
		}
	}

	/**
	 *
	 * Writes the service to the test and data files
	 * @param KalturaServiceReflector $serviceReflector
	 */
	protected function writeService(KalturaServiceActionItem $serviceReflector)
	{
		$serviceName = $serviceReflector->serviceInfo->serviceName;
		$serviceId = $serviceReflector->serviceId;
		$serviceClass = $serviceReflector->serviceClass;
		$actions = $serviceReflector->actionMap;

		foreach($actions as $action => $actionReflector)
		{
		    /* @var $actionReflector KalturaActionReflector */
			$actionInfo = $actionReflector->getActionInfo();
				
			if($actionInfo->serverOnly)
				continue;

			if (strpos($actionInfo->clientgenerator, "ignore") !== false)
				continue;

			$resgressionTests = array('addAction', 'getAction', 'deleteAction', 'updateAction', 'listAction');
			if(!in_array($actionReflector->getActionName() , $resgressionTests))
				continue;

			$outputTypeReflector = $actionReflector->getActionOutputType();
			$actionParams = $actionReflector->getActionParams();

			$this->writeServiceAction($serviceId, $serviceName, $actionInfo->action, $actionParams, $outputTypeReflector);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ClientGeneratorFromPhp::writeAfterService()
	 */
	protected function writeAfterService(KalturaServiceActionItem $serviceReflector)
	{
//		$this->writeBase("	/**");
//		$this->writeBase("	 * Called when all tests are done");
//		$this->writeBase("	 * @param int \$id");
//		$this->writeBase("	 * @return int");
//		$this->writeBase("	 * TODO: replace {$this->lastDependencyTest} with last test function that uses that id");
//		$this->writeBase("	 * @depends {$this->lastDependencyTest} with data set #0");
//		$this->writeBase("	 */");
//		$this->writeBase("	public function testFinished(\$id)");
//		$this->writeBase("	{");
//		$this->writeBase("		return \$id;");
//		$this->writeBase("	}");
//		$this->writeBase("");

		//Close the test file
		$this->writeTest("}");
		$this->writeTest("");
			
		//		$this->writeBase("	/**");
		//		$this->writeBase("	 * Called when all tests are done");
		//		$this->writeBase("	 * @param int \$id");
		//		$this->writeBase("	 * @return int");
		//		$this->writeBase("	 */");
		//		$this->writeBase("	abstract public function testFinished(\$id);");
		//		$this->writeBase("");

		$serviceClass = $serviceReflector->serviceClass;
		$serviceClass = ucfirst($serviceClass); //Capital first letter

//		$this->writeBase("	/**");
//		$this->writeBase("	 * ");
//		$this->writeBase("	 * Returns the suite for the test");
//		$this->writeBase("	 */");
//		$this->writeBase("	public static function suite()");
//		$this->writeBase("	{");
//		$this->writeBase("		return new KalturaTestSuite('{$serviceClass}Test');");
//		$this->writeBase("	}");
//		$this->writeBase("");

		//Close the base file
		$this->writeBase("}");

		$serviceName = $serviceReflector->serviceInfo->serviceName;
		$testPath = realpath(dirname(__FILE__) . '/../') . "/tests/api/$serviceName";

		if(strpos($serviceReflector->serviceId, "_"))
		{
			//			$servicePath = KAutoloader::getClassFilePath($serviceClass);
			//			$testPath = realpath(dirname($servicePath) . '/../') . "/tests/services/$serviceName";
			$testPath = realpath(dirname(__FILE__) . '/../') . "/tests/api/KalturaPlugins/{$serviceClass}_{$serviceName}";
		}

		$this->writeXml("</TestCaseData>"); // Close the XML tag for the test case
		$this->writeXmlSource("	</TestCaseData>"); // Close the XML tag for the test case
		$this->writeXmlSource("</TestsDataSource>");

		$this->writeToFile("$testPath/{$serviceClass}TestBase.php", $this->_txtBase);
		$this->writeToFile("$testPath/{$serviceClass}Test.php", $this->_txtTest, false);
		$this->writeToFile("$testPath/{$serviceClass}Test.php.ini", $this->_txtIni, false);
		//$this->writeToFile("$testPath/testsData/{$serviceClass}Test.data", $this->_txtXml, false);
		//$this->writeToFile("$testPath/testsData/{$serviceClass}Test.config", $this->_txtXmlSource, false); //TODO: change the file extension to source
	}

	/**
	 *
	 * Write the outpur data for the test
	 * @param KalturaTypeReflector $outputTypeReflector
	 */
	protected function setOutputData($outputTypeReflector, &$testParams, &$testValues, $isBase = false, &$validateValues = null)
	{
		$paramType = $outputTypeReflector->getType();
		$paramName = $outputTypeReflector->getName();
		$this->writeXmlSource("				<OutputReference name = '$paramName' type = '$paramType' key = 'Fill the object key' />");

		if($outputTypeReflector->isSimpleType() || $outputTypeReflector->isEnum() ||
		$outputTypeReflector->isDynamicEnum() || $outputTypeReflector->isDynamicEnum()
		)
		{
			$defaultValue = $outputTypeReflector->getDefaultValue();
				
			$this->writeIni("test1.reference = " . $defaultValue );
			$this->writeXml("		<OutputReference name = '$paramName' type = '$paramType' key = '$defaultValue' />");
		}
		elseif($outputTypeReflector->isFile())
		{
			$this->writeIni("test1.reference.objectType = file");
			$this->writeIni("test1.reference.path = ");
				
			//TODO: add support for files in XML
			$this->writeXml("		<OutputReference name = '$paramName' type='file' key='path/to/file'/>");
		}
		else
		{
			$this->writeIni("test1.reference.objectType = $paramType");
			$this->writeXml("		<OutputReference name = '$paramName' type = '$paramType' key = 'object key'>");
				
			$actionParamProperties = $outputTypeReflector->getTypeReflector()->getProperties();
			foreach($actionParamProperties as $actionParamProperty)
			{
				/* @var $actionParamProperty KalturaPropertyInfo */
				if($actionParamProperty->isReadOnly())
					continue;
					
				$propertyType = $actionParamProperty->getType();
				$propertyName = $actionParamProperty->getName();

				if( $actionParamProperty->isSimpleType() || $actionParamProperty->isEnum() ||
				$actionParamProperty->isDynamicEnum() || $actionParamProperty->isDynamicEnum()
				)
				{
					$paramDefaultValue = $actionParamProperty->getDefaultValue();
					$this->writeIni("test1.reference.$propertyName = " . $paramDefaultValue);
					$this->writeXml("			<$propertyName>$paramDefaultValue</$propertyName>");
				}
				elseif($actionParamProperty->isFile())
				{
					$this->writeIni("test1.reference.$propertyName.objectType = file");
					$this->writeIni("test1.reference.$propertyName.path = ");
						
					//TODO: add support for files in XML
					$this->writeXml("			<OutputReference name = '$paramName' type='file' key= 'path/to/file'>");
				}
				elseif(!$actionParamProperty->isAbstract())
				{
					if($propertyName == 'type')
					{
						//Causes bug in the Zend config
						$this->writeIni("test1.reference.objType.$propertyName = $propertyType");
					}
					else
					{
						$this->writeIni("test1.reference.$propertyName.objectType = $propertyType");
					}
						
					$this->writeXml("			<$propertyName>$propertyType</$propertyName>");
				}
			}
				
			$this->writeXml("		</OutputReference>");
		}

		$paramDesc = strlen($outputTypeReflector->getDescription()) ? ' ' . $outputTypeReflector->getDescription() : '';

		if($isBase)
			$this->writeBase("	 * @param $paramType \$reference{$paramDesc}");
		else
			$this->writeTest("	 * @param $paramType \$reference{$paramDesc}");
			
		if(!$outputTypeReflector->isComplexType() || //it the param is not: complex
			$outputTypeReflector->isEnum() || //or it is an enum then we dont print the type
			$outputTypeReflector->isStringEnum() ||
			$outputTypeReflector->isDynamicEnum()
		)
			$testParam = "\$reference";
		else
			$testParam = "$paramType \$reference";
			
		if($outputTypeReflector->isOptional())
		{
			if($outputTypeReflector->getDefaultValue())
			{
				if($outputTypeReflector->getType() == 'string')
				$testParam .= " = '" . $outputTypeReflector->getDefaultValue() . "'";
				else
				$testParam .= " = " . $outputTypeReflector->getDefaultValue();
			}
			else
			{
				$testParam .= " = null";
			}
		}
		$testParams[] = $testParam;

		if($isBase)
		$validateValues[] = "\$reference";
		else //write to TestFile
		$testValues[] = "\$reference";
	}

	/**
	 *
	 * Sets the test params and values for the given action param
	 * @param string $actionParam
	 * @param array $testParams - passed by reference
	 * @param array $testValues - passed by reference
	 * @param array $validateValues - passed by reference
	 * @param boolean $addId - sets if the given action depends on the id from the add action
	 * @param boolean $isBase
	 */
	protected function setTestParamsAndValues($actionParam, &$testParams, &$testValues, &$validateValues = null, $addId = false, $isBase = false)
	{
		$paramType = $actionParam->getType();
		$paramName = $actionParam->getName();
		$this->writeXmlSource("				<Input name=\"$paramName\" type=\"$paramType\" key=\"Fill object key\"/>");

		//KalturaLog::debug("paramName [$paramName] paramType [$paramType]");
			
		$isParamContainsId = (substr_count($paramName, "id") > 0) || (substr_count($paramName, "Id") > 0);

		if($actionParam->isSimpleType() || $actionParam->isEnum() ||
		$actionParam->isStringEnum() || $actionParam->isDynamicEnum()
		)
		{
			$paramDefaultValue = $actionParam->getDefaultValue();
			$this->writeIni("test1.$paramName = " . $paramDefaultValue );
			$this->writeXml("		<Input name=\"$paramName\" type=\"$paramType\" key=\"$paramDefaultValue\"/>");
		}
		elseif($actionParam->isFile())
		{
			$this->writeIni("test1.$paramName.objectType = file");
			$this->writeIni("test1.$paramName.path = ");
			$this->writeXml("		<Input name=\"$paramName\" type=\"file\" key=\"\"/>");
		}
		else
		{
			if($paramName == 'type')
			{
				$this->writeIni("test1.objType.$paramName = $paramType");
			}
			else
			{
				$this->writeIni("test1.$paramName.objectType = $paramType");
			}
				
			$this->writeXml("		<Input name=\"$paramName\" type=\"$paramType\" key=\"\">");
				
			$actionParamProperties = $actionParam->getTypeReflector()->getProperties();
			foreach($actionParamProperties as $actionParamProperty)
			{
				if($actionParamProperty->isReadOnly() || $actionParamProperty->isInsertOnly())
				continue;
					
				$propertyType = $actionParamProperty->getType();
				$propertyName = $actionParamProperty->getName();

				if($actionParamProperty->isSimpleType() || $actionParamProperty->isEnum() ||
				$actionParamProperty->isStringEnum() ||$actionParamProperty->isDynamicEnum()
				)
				{
					$defaultValue = $actionParamProperty->getDefaultValue();
					$this->writeIni("test1.$paramName.$propertyName = " . $defaultValue);
					$this->writeXml("			<$propertyName>$defaultValue</$propertyName>");
				}
				elseif($actionParamProperty->isFile())
				{
					$this->writeIni("test1.$paramName.$propertyName.objectType = file");
					$this->writeIni("test1.$paramName.$propertyName.path = ");
					$this->writeXml("			<$propertyName>file not supported yet...</$propertyName>");
				}
				elseif(!$actionParamProperty->isAbstract())
				{
					if($propertyName == 'type')
					{
						$this->writeIni("test1.$paramName.objType.$propertyName = $propertyType");
					}
					else
					{
						$this->writeIni("test1.$paramName.$propertyName.objectType = $propertyType");
					}
						
					$this->writeXml("			<$propertyName>$propertyType</$propertyName>");
				}
			}
			$this->writeXml("		</Input>");
		}
			
		$paramDesc = strlen($actionParam->getDescription()) ? ' ' . $actionParam->getDescription() : '';
		$this->write("	 * @param $paramType \${$paramName}{$paramDesc}", $isBase);
			
		if(!$actionParam->isComplexType() || //it the param is not: complex
		$actionParam->isEnum() || //or it is an enum then we dont print the type
		$actionParam->isStringEnum() ||
		$actionParam->isDynamicEnum())
		$testParam = "\$$paramName";
		else
		$testParam = "$paramType \$$paramName";
			
		if($actionParam->isOptional())
		{
			if ($actionParam->isSimpleType())
			{
				$defaultValue = $actionParam->getDefaultValue();

				if ($defaultValue === "false")
				$testParam .= " = false";
				else if ($defaultValue === "true")
				$testParam .= " = true";
				else if ($defaultValue === "null")
				$testParam .= " = null";
				else if ($paramType == "string")
				$testParam .= " = \"$defaultValue\"";
				else if ($paramType == "int")
				{
					if ($defaultValue == "")
					$testParam .= " = \"\""; // hack for partner.getUsage
					else
					$testParam .= " = $defaultValue";
				}
			}
			else
			$testParam .= " = null";
		}

		//Adds the new param / value to the test params / values
		$testParams[] = $testParam;
		$testValues[] = "\$$paramName";
			
		if($isBase)
		$validateValues[] = "\$$paramName";
	}

	/**
	 * (non-PHPdoc)
	 * @see ClientGeneratorFromPhp::writeServiceAction()
	 */
	protected function writeServiceAction($serviceId, $serviceName, $action, $actionParams, $outputTypeReflector)
	{
		if($outputTypeReflector && $outputTypeReflector->isFile())
			return;
			
		if(in_array($action, array("list", "clone", "goto")))
			$action = "{$action}Action";

		//KalturaLog::info("Generates action [$serviceName.$action]");

		$isBase = false;
		$testReturnedType = null;
		$addId = false;

		//Set the tests to be the regression tests
		if($action == 'add' || $action == 'update' || $action == 'get' || $action == 'listAction' || $action == 'delete' || ($action == 'addFromEntry' && $serviceName == 'documents'))
			$isBase = true;

		//Createds the dependency between the tests to the add tests
		if($action == 'update' || $action == 'get' || $action == 'delete' ) // || $action == 'listAction' TODO: add list if needed
			$addId = true;

		//Special care for add method as it needs to return the id to the other tests
		if($action == 'add' || ($action == 'addFromEntry' && $serviceName == 'documents'))
		{
			//TODO: support return type of int
			$outputType = $outputTypeReflector->getType();
			$testReturnedType = "$outputType"; // for the dependency (CRUD)
		}

		//TODO:delete this
		$resgressionTests = array('add', 'get', 'delete', 'update', 'listAction');
		if(!in_array($action , $resgressionTests ))
			return;

		if($action)
			$actionName = ucfirst($action);

		$this->writeIni("");
		$this->writeIni("[test{$actionName}]");

		$this->writeXml("<TestProcedureData testProcedureName='test$actionName'>");
		$this->writeXml("	<TestCaseData testCaseInstanceName='test$actionName with data set #0'>");

		$this->writeXmlSource("		<TestProcedureData testProcedureName='test$actionName'>");
		$this->writeXmlSource("			<TestCaseData testCaseInstanceName='test$actionName with template data set'>");

		$this->write("	/**", $isBase);
		$this->write("	 * Tests {$serviceName}->{$action} action", $isBase);

		$testParams = array();
		$testValues = array();
		$validateValues = array();

		foreach($actionParams as $actionParam)
			$this->setTestParamsAndValues($actionParam, $testParams, $testValues, $validateValues, $addId, $isBase);

		if($outputTypeReflector)
			$this->setOutputData($outputTypeReflector, $testParams, $testValues, $isBase, $validateValues);

		$this->writeXml("	</TestCaseData>");
		$this->writeXml("</TestProcedureData>");

		$this->writeXmlSource("			</TestCaseData>");
		$this->writeXmlSource("		</TestProcedureData>");
			
		$testParams = implode(', ', $testParams);
		$testValues = implode(', ', $testValues);
		$validateValues = implode(', ', $validateValues);

		$outputType = null;
		if($outputTypeReflector)
			$outputType = $outputTypeReflector->getType();

		if($testReturnedType)
		{
			$this->lastDependencyTest = "test{$actionName}";
			$this->write("	 * @return $testReturnedType", $isBase); //will always be for the base
		}
		if($addId)
		{
			$this->write("	 * @depends testAdd with data set #$this->dependencyIndex", $isBase);
			$this->dependencyIndex++;
		}

		if(count($testValues))
			$this->write("	 * @dataProvider provideData", $isBase);

		$this->writeActionTest($serviceName, $actionName, $action, $testParams, $testValues, $outputType, $isBase, $testReturnedType, $validateValues);

		if($isBase && $outputType)
		{
			$this->writeBase("	/**");
			$this->writeBase("	 * Validates test{$actionName} results");
			$this->writeBase("	 * Hook to be overriden by the extending class");
			$this->writeBase("	 * ");
			$this->writeBase("	 * @param $outputType \$resultObject");
			$this->writeBase("	 */");
			$this->writeBase("	protected function validate{$actionName}($outputType \$resultObject){}");
			$this->writeBase("");

			$serviceReflector = KalturaServiceReflector::constructFromServiceId($serviceId);
			$serviceClass = $serviceReflector->getServiceClass();
		
			$this->writeTest("	/* (non-PHPdoc)");
			$this->writeTest("	 * @see {$serviceClass}TestBase::validate{$actionName}()");
			$this->writeTest("	 */");
			$this->writeTest("	protected function validate{$actionName}($outputType \$resultObject)");
			$this->writeTest("	{");
			//$this->writeTest("		parent::validate{$actionName}($validateValues);");
			$this->writeTest("		// TODO - add your own validations here");
			$this->writeTest("	}");
			$this->writeTest("");
		}
	}

	/**
	 *
	 * Writes the action test for the given service and action
	 * @param string $serviceName
	 * @param string $actionName
	 * @param array $testParams
	 * @param array $testValues
	 * @param string $outputType
	 */
	protected function writeActionTest($serviceName, $actionName, $action,$testParams, $testValues, $outputType, $isBase = false, $testReturnedType = null, $validateValues = null)
	{
		$this->write("	 */", $isBase);
		$this->write("	public function test{$actionName}($testParams)", $isBase);
		$this->write("	{", $isBase);
		$this->write("		\$resultObject = \$this->client->{$serviceName}->{$action}($testValues);", $isBase);

		if($outputType) //If we have an output then we check it
		{
			$this->write("		if(method_exists(\$this, 'assertInstanceOf'))", $isBase);
			$this->write("			\$this->assertInstanceOf('$outputType', \$resultObject);", $isBase);
			$this->write("		else", $isBase);
			$this->write("			\$this->assertType('$outputType', \$resultObject);", $isBase);

			//TODO: create an ignore field array to be populated dynamically (maybe from the service reflector)
			$ignoreFields = array("createdAt", "updatedAt", "id", "thumbnailUrl",
								  "downloadUrl", "rootEntryId", "operationAttributes",
								  "deletedAt", "statusUpdatedAt", "widgetHTML", "totalCount", "objects", 
								  "cropDimensions", "dataUrl", "requiredPermissions", "confFilePath", "feedUrl");
				
			$ignoreFieldsLine = implode("', '", $ignoreFields);
				
			$this->write("		\$this->assertAPIObjects(\$reference, \$resultObject, array('$ignoreFieldsLine'));", $isBase);
		}

		if(!$isBase) //If regular test
			$this->write("		// TODO - add here your own validations", $isBase);

		if($testReturnedType) //Adds assert to returned value (for dependency)
			$this->write("		\$this->assertNotNull(\$resultObject->id);", $isBase);
			
		if($outputType)
			$this->write("		\$this->validate{$actionName}(\$resultObject);", $isBase);

		if($testReturnedType)
		{
			$this->write("		", $isBase);
			$this->write("		return \$resultObject->id;", $isBase);
		}
			
		$this->write("	}", $isBase);
		$this->write("", $isBase);
	}

	/**
	 *
	 * Writes to the base test file
	 * @param string $txt
	 */
	private function writeBase($txt = "")
	{
		$this->_txtBase .= $txt ."\n";
	}

	/**
	 *
	 * Writes to the test file
	 * @param string $txt
	 */
	private function writeTest($txt = "")
	{
		$this->_txtTest .= $txt ."\n";
	}

	/**
	 *
	 * Writes to the test or base file depends on the given boolean
	 * @param string $txt
	 * @param boolean $isBase
	 */
	private function write($txt = "", $isBase)
	{
		if($isBase)
		{
			$this->_txtBase .= $txt ."\n";
		}
		else
		{
			$this->_txtTest .= $txt ."\n";
		}
	}

	/**
	 *
	 * Writes data to the ini var
	 * @param string $txt
	 */
	private function writeIni($txt = "")
	{
		$this->_txtIni .= $txt ."\n";
	}

	/**
	 *
	 * Writes data to the xml var
	 * @param string $txt
	 */
	private function writeXml($txt = "")
	{
		$this->_txtXml .= $txt ."\n";
	}

	/**
	 *
	 * Writes data to the xml var
	 * @param string $txt
	 */
	private function writeXmlSource($txt = "")
	{
		$this->_txtXmlSource .= $txt ."\n";
	}

	/**
	 *
	 * Writes a given string into a given file (creates if non exists)
	 * @param string $fileName
	 * @param string $contents
	 * @param bool $overwrite
	 */
	private function writeToFile($fileName, $contents, $overwrite = true)
	{
		if(file_exists($fileName) && !$overwrite)
		{
			//KalturaLog::info("File [$fileName] already exists not writing data");
			return;
		}
			
		$dirname = dirname($fileName);
		if(!file_exists($dirname))
		mkdir($dirname, 0777, true);
			
		$handle = fopen($fileName, "w");
		fwrite($handle, $contents);
		fclose($handle);
	}
}
