<?php


class KalturaApiTestCase extends KalturaTestCaseApiBase implements IKalturaLogger
{
	/**
	 * @var KalturaClient
	 */
	protected $client;
	
	/**
	 * @var KalturaClient
	 */
	protected $clientStaging;
	
	/**
	 * 
	 * Creates a new Kaltura API Test Case
	 * @param unknown_type $name
	 * @param array $data
	 * @param unknown_type $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		KalturaLog::debug("KalturaApiTestCase::__construct name [$name], data [" . print_r($data, true). "], dataName [$dataName]\n");

		parent::__construct($name, $data, $dataName);
				
		$testConfig = $this->config->get('config');
		$needSave = false;

		//TODO: add support for getting the values from the global data
		if(!$testConfig->serviceUrl)
		{
			$testConfig->serviceUrl = '@SERVICE_URL@';
			$needSave = true;
		}
		
		if(!$testConfig->partnerId)
		{
			$testConfig->partnerId = "@TEST_PARTNER_ID@";
			$needSave = true;
		}
		
		if(!$testConfig->clientTag)
		{
			$testConfig->clientTag = 'unitTest';
			$needSave = true;
		}
		
		if(!$testConfig->curlTimeout)
		{
			$testConfig->curlTimeout = 90;
			$needSave = true;
		}	
		
		if(!isset($testConfig->startSession))
		{
			$testConfig->startSession = false;
			$needSave = true;
		}		
		
		if($testConfig->startSession)
		{
			if(!$testConfig->secret)
			{
				$testConfig->secret = 'PARTNER_SECRET';
				$needSave = true;
			}
			if(!$testConfig->userSecret)
			{
				$testConfig->secret = 'PARTNER_USER_SECRET';
				$needSave = true;
			}
			if(!$testConfig->userId)
			{
				$testConfig->userId = '';
			}
			if(!$testConfig->sessionType)
			{
				$testConfig->sessionType = 2;
				$needSave = true;
			}
			if(!$testConfig->expiry)
			{
				$testConfig->expiry = 60 * 60 * 24;
				$needSave = true;
			}
			if(!$testConfig->privileges)
			{
				$testConfig->privileges = '';
			}
		}
		
		if($needSave)
			$this->config->saveToIniFile();
		
		$kalturaConfiguration = new KalturaConfiguration($testConfig->partnerId);
		$kalturaConfiguration->serviceUrl = $testConfig->serviceUrl;
		$kalturaConfiguration->clientTag = $testConfig->clientTag;
		$kalturaConfiguration->curlTimeout = $testConfig->curlTimeout;
		$kalturaConfiguration->setLogger($this);
		
		$this->client = new KalturaClient($kalturaConfiguration);
		
		if($testConfig->startSession)
		{
			$this->startSession($this->client, $testConfig->sessionType, $testConfig->userId);
		}
		
		if($testConfig->serviceUrlStaging)
		{
			$kalturaConfigurationStaging = $kalturaConfiguration;
			$kalturaConfigurationStaging->serviceUrl = $testConfig->serviceUrlStaging;
			
			$this->clientStaging = new KalturaClient($kalturaConfigurationStaging);
		
			if($testConfig->startSession)
			{
				$this->startSession($this->clientStaging, $testConfig->sessionType, $testConfig->userId);
			}
		}
	}
	
	/**
	 * Logs a given message
	 * @see IKalturaLogger::log()
	 */
	public function log($msg)
	{
		KalturaLog::log($msg);
	}

	/**
	 * 
	 * Compares two API objects and notify the PHPUnit and kaltura listeners 
	 * @param KalturaObjectBase $object1
	 * @param KalturaObjectBase $object2
	 */
	public function assertAPIObjects(KalturaObjectBase $expectedObject, KalturaObjectBase $actualObject, $skip = array(), $objectName = '')
	{
		$objectName .= get_class($actualObject);
		if(property_exists($actualObject, 'id'))
			$objectName .= " id [{$actualObject->id}]";
			
		KalturaLog::debug("Comparing expected object [" . get_class($expectedObject) . "] to actual $objectName");
		
		//Use reflection to compare the objects
		$outputReferenceReflector = new ReflectionClass($expectedObject);
		$properties = $outputReferenceReflector->getProperties(ReflectionProperty::IS_PUBLIC);
		
		$newErrors = array();
		
		foreach ($properties as $property)
		{
			$propertyName = $property->getName();
			
			//If the field is in the valid failure list then we skip him 
			if(in_array($propertyName, $skip))
				continue;
			
			$expectedValue = $property->getValue($expectedObject);
			$expectedType = gettype($expectedValue) == 'object' ? get_class($expectedValue) : gettype($expectedValue);
			if(is_null($expectedValue))
				continue;
				
			$actualValue = $property->getValue($actualObject);
			$actualType = gettype($actualValue) == 'object' ? get_class($actualValue) : gettype($actualValue);
			
			KalturaLog::debug("Comparing attribute [$propertyName] expected type [$expectedType] actual type [$actualType] on $objectName");
			
			if($expectedValue instanceof KalturaObjectBase)
			{
				if(method_exists($this, 'assertInstanceOf'))
					$this->assertInstanceOf(get_class($expectedValue), $actualValue, "$objectName attribute [$propertyName] is not expected type [" . get_class($expectedValue) . "]");
				else
					$this->assertType(get_class($expectedValue), $actualValue, "$objectName attribute [$propertyName] is not expected type [" . get_class($expectedValue) . "]");
					
				$this->assertAPIObjects($expectedValue, $actualValue, array(), "$objectName attribute [$propertyName] ");
				continue;
			}
			
			if(is_array($expectedValue))
			{
				$this->assertType('array', $actualValue, "$objectName attribute [$propertyName] is not array");
				foreach($expectedValue as $key => $expectedKeyValue)
				{
					$message = "$objectName attribute [$propertyName] missing array key [$key]";
					$this->assertArrayHasKey($key, $actualValue, $message);
					
					$actualKeyValue = $actualValue[$key];
				
					if($expectedKeyValue instanceof KalturaObjectBase)
					{
						if(method_exists($this, 'assertInstanceOf'))
							$this->assertInstanceOf(get_class($expectedKeyValue), $actualKeyValue, "$objectName attribute [$propertyName] key [$key] is not expected type [" . get_class($expectedKeyValue) . "]");
						else
							$this->assertType(get_class($expectedKeyValue), $actualKeyValue, "$objectName attribute [$propertyName] key [$key] is not expected type [" . get_class($expectedKeyValue) . "]");
							
						$this->assertAPIObjects($expectedKeyValue, $actualKeyValue, array(), "$objectName attribute [$propertyName] key [$key] ");
						continue;
					}
					
					if($actualKeyValue instanceof KalturaObjectBase)
						$this->fail("$objectName attribute [$propertyName] array key [$key] expected type [" . gettype($expectedKeyValue) . "] and actual type [" . get_class($actualKeyValue) . "]");
					
					$message = "$objectName attribute [$propertyName] array key [$key] expected value [$expectedKeyValue] and actual value [$actualKeyValue]";
					$this->assertEquals($expectedKeyValue, $actualKeyValue, $message);
				}
				
				continue;
			}
			
			$expectedValue = strval($expectedValue);
			$actualValue = strval($actualValue);
			
			$message = "$objectName attribute [$propertyName] expected value [$expectedValue] and actual value [$actualValue]";
			$this->assertEquals($expectedValue, $actualValue, $message);
		}
	
		return $newErrors;
	}
	
	/**
	 * 
	 * Gets the parameters for creating a new kaltura client and returns the new client
	 * @param int $partnerId
	 * @param string $secret
	 * @param string $configServiceUrl
	 * @param int $isAdmin - 0 = no admin
	 * @return KalturaClient - a new api client 
	 */
	public function getClient($partnerId, $secret, $configServiceUrl, $isAdmin, $userId = null)
	{
		$config = new KalturaConfiguration((int)$partnerId);

		//Add the server url (into the test additional data)
		$config->serviceUrl = $configServiceUrl;
		$client = new KalturaClient($config);
		$sessionType = KalturaSessionType::USER;
		
		if($isAdmin != 0)
		{
			$sessionType =  KalturaSessionType::ADMIN;
		} 
		
		$ks = $client->session->start($secret, (string)$userId, $sessionType, (int)$partnerId, null, null);
		$client->setKs($ks);

		return $client;
	}
	
	const HTTP_USER_AGENT = "\"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6\"";
	
	/**
	 * 
	 * return the file output for a given url.
	 * used for api actions that return file.
	 * @param string $url url from which the answer is given.
	 */
	protected function getApiFileFromUrl($url)
	{
		$url = trim($url);
		$url = str_replace(array(' ', '[', ']'), array('%20', '%5B', '%5D'), $url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, self::HTTP_USER_AGENT);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_NOSIGNAL, true);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		return curl_exec($ch);
	}

	/**
	 * 
	 * save file output from a given url.
	 * used for api actions that return file.
	 * @param string $url url from which the answer is given.
	 * @param string $filePath file path to save to
	 */
	protected function saveApiFileFromUrl($url, $filePath)
	{
		$ch = curl_init($url);
		$fp = fopen($filePath, 'wb');

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, false);

		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}

	/**
	 * Starts a new session
	 * @param KalturaClient $client
	 * @param KalturaSessionType $type
	 * @param string $userId
	 */
	protected function startSession($client, $type = null, $userId = null, $privileges = null)
	{
		$testConfig = $this->config->get('config');
		
		if (is_null($privileges))
			$privileges = $testConfig->privileges;
		
		//$ks = $this->client->session->start($testConfig->secret, $testConfig->userId, $testConfig->sessionType, $testConfig->partnerId, $testConfig->expiry, $testConfig->privileges);
		$ks = $client->generateSession($testConfig->secret, $testConfig->userId, $testConfig->sessionType, $testConfig->partnerId, $testConfig->expiry, $privileges);
		if (!$ks)
			return false;
		
		$client->setKs($ks);
		KalturaLog::debug('$testConfig->secret: ' . print_r($testConfig->secret, true) . 
		' user id: ' . print_r($testConfig->userId,true) . 
		' session type: ' . print_r($testConfig->sessionType, true) . 
		' $testConfig->partnerId: ' . $testConfig->partnerId . 
		' expiry: ' . print_r($testConfig->expiry, true) . 
		' privileges: ' . print_r($privileges,true));
		
		KalturaLog::info("Session started [$ks]");
		return true;
	}
	
	/**
	 * Starts a new session
	 * @param KalturaSessionType $type
	 * @param string $userId
	 */
	protected function startSessionWithDiffe($type, $userId, $privileges = null)
	{
		$testConfig = $this->config->get('config');
		
		$secret = $testConfig->secret;
		if ($type == SessionType::ADMIN)
			$secret = $testConfig->userSecret;
			
		if(is_null($privileges))
			$privileges = $testConfig->privilege;
 			
		$ks = $this->client->generateSession($secret, $userId, $type, $testConfig->partnerId, $testConfig->expiry, $privileges);
		KalturaLog::debug('Generate session for ks with privileges: ' . print_r($privileges,true) . ' ks: ' . $ks);
		
		if (!$ks)
			return false;
		
		$this->client->setKs($ks);
		KalturaLog::info("Session started [$ks]");
		return true;
	}
}