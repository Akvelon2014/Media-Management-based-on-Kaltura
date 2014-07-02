<?php

require_once(dirname(__FILE__) . '/../general/infraRequestUtils.class.php');
require_once(dirname(__FILE__) . '/kCacheManager.php');
require_once(dirname(__FILE__) . '/../../alpha/apps/kaltura/lib/webservices/kSessionBase.class.php');

/**
 * @package infra
 * @subpackage cache
 */
class kApiCache
{
	// extra cache fields
	const ECF_REFERRER = 'referrer';
	const ECF_USER_AGENT = 'userAgent';
	const ECF_COUNTRY = 'country';
	const ECF_IP = 'ip';

	// extra cache fields conditions
	// 	the conditions will be applied on the extra fields when generating the cache key
	//	for example, when using country restriction of US allowed, we can take country==US
	//	in the cache key instead of taking the whole country (2 possible cache key values for
	//	the entry, instead of 200)
	const COND_NONE = '';
	const COND_MATCH = 'match';					// used by kCountryCondition
	const COND_REGEX = 'regex';					// used by kUserAgentCondition
	const COND_SITE_MATCH = 'siteMatch';		// used by kSiteCondition
	const COND_IP_RANGE = 'ipRange';			// used by kIpAddressCondition
	
	const EXTRA_KEYS_PREFIX = 'extra-keys-';	// apc cache key prefix

	// cache statuses
	const CACHE_STATUS_ACTIVE = 0;				// cache was not explicitly disabled
	const CACHE_STATUS_ANONYMOUS_ONLY = 1;		// conditional cache was explicitly disabled by calling DisableConditionalCache (e.g. a database query that is not handled by the query cache was issued)
	const CACHE_STATUS_DISABLED = 2;			// cache was explicitly disabled by calling DisableCache (e.g. getContentData for an entry with access control)
	
	const CONDITIONAL_CACHE_EXPIRY = 86400;		// 1 day, must not be greater than the expiry of the query cache keys
	
	// cache instances
	protected $_instanceId = 0;
	protected static $_activeInstances = array();		// active class instances: instanceId => instanceObject
	protected static $_nextInstanceId = 0;

	// cache key
	protected $_params = array();	
	protected $_cacheKey = "";					// a hash of _params used as the key for caching
	protected $_cacheKeyPrefix = '';			// the prefix of _cacheKey, the cache key is generated by concatenating this prefix with the hash of the params
	protected $_cacheKeyDirty = true;			// true if _params was changed since _cacheKey was calculated
	protected $_originalCacheKey = null;		// the value of the cache key before any extra fields were added to it

	// ks
	protected $_ks = "";
	protected $_ksObj = null;
	protected $_ksPartnerId = null;
	
	// status
	protected $_expiry = 600;
	protected $_cacheStatus = self::CACHE_STATUS_DISABLED;	// enabled after the KalturaResponseCacher initializes
	
	// conditional cache fields
	protected $_conditionalCacheExpiry = 0;				// the expiry used for conditional caching, if 0 CONDITIONAL_CACHE_EXPIRY will be used 
	protected $_invalidationKeys = array();				// the list of query cache invalidation keys for the current request
	protected $_invalidationTime = 0;					// the last invalidation time of the invalidation keys

	// extra fields
	protected $_extraFields = array();
	protected $_referrers = array();				// a request can theoritically have more than one referrer, in case of several baseEntry.getContextData calls in a single multirequest
	protected static $_country = null;				// caches the country of the user issuing this request
	protected static $_ip = null;					// caches the ip of the user issuing this request
	protected static $_usesHttpReferrer = false;	// enabled if the request is dependent on the http referrer field (opposed to an API parameter referrer)
	protected static $_hasExtraFields = false;		// set to true if the response depends on http headers and should not return caching headers to the user / cdn
	
	protected function __construct()
	{
		$this->_instanceId = self::$_nextInstanceId;  
		self::$_nextInstanceId++;
	}

	protected function addKSData($ks)
	{
		$this->_ks = $ks;
		$this->_ksObj = kSessionBase::getKSObject($ks);
		$this->_ksPartnerId = ($this->_ksObj ? $this->_ksObj->partner_id : null);
		$this->_params["___cache___partnerId"] =  $this->_ksPartnerId;
		$this->_params["___cache___ksType"] = 	  ($this->_ksObj ? $this->_ksObj->type		 : null);
		$this->_params["___cache___userId"] =     ($this->_ksObj ? $this->_ksObj->user		 : null);
		$this->_params["___cache___privileges"] = ($this->_ksObj ? $this->_ksObj->privileges : null);
	}
	
	// enable / disable functions
	protected function enableCache()
	{
		self::$_activeInstances[$this->_instanceId] = $this;
		$this->_cacheStatus = self::CACHE_STATUS_ACTIVE;
	}
	
	public static function disableCache()
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			$curInstance->_cacheStatus = self::CACHE_STATUS_DISABLED;
		}
		self::$_activeInstances = array();
	}

	public static function disableConditionalCache()
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			// no need to check for CACHE_STATUS_DISABLED, since the instances are removed from the list when they get this status
			$curInstance->_cacheStatus = self::CACHE_STATUS_ANONYMOUS_ONLY;
		}
	}
	
	protected function removeFromActiveList()
	{
		unset(self::$_activeInstances[$this->_instanceId]);
	}
	
	// expiry control functions
	public static function setExpiry($expiry)
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			if ($curInstance->_expiry && $curInstance->_expiry < $expiry)
				continue;
			$curInstance->_expiry = $expiry;
		}
	}
	
	public static function setConditionalCacheExpiry($expiry)
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			if ($curInstance->_conditionalCacheExpiry && $curInstance->_conditionalCacheExpiry < $expiry)
				continue;
			$curInstance->_conditionalCacheExpiry = $expiry;
		}
	}
	
	// conditional cache
	public static function addInvalidationKeys($invalidationKeys, $invalidationTime)
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			$curInstance->_invalidationKeys = array_merge($curInstance->_invalidationKeys, $invalidationKeys);
			$curInstance->_invalidationTime = max($curInstance->_invalidationTime, $invalidationTime);
		}
	}
	
	// extra fields functions
	static public function hasExtraFields()
	{
		return self::$_hasExtraFields;
	}
	
	static public function addExtraField($extraField, $condition = self::COND_NONE, $refValue = null)
	{
		foreach (self::$_activeInstances as $curInstance)
		{
			$curInstance->addExtraFieldInternal($extraField, $condition, $refValue);
		}

		// the following code is required since there are no active cache instances in thumbnail action
		// and we need _hasExtraFields to be correct
		if ($extraField != self::ECF_REFERRER || self::$_usesHttpReferrer)
			self::$_hasExtraFields = true;
	}

	static protected function getCountry()
	{
		if (is_null(self::$_country))
		{
			require_once(dirname(__FILE__) . '/../../alpha/apps/kaltura/lib/myIPGeocoder.class.php');
			$ipAddress = infraRequestUtils::getRemoteAddress();
			$geoCoder = new myIPGeocoder();
			self::$_country = $geoCoder->getCountry($ipAddress);
		}
		return self::$_country;
	}

	static protected function getIp()
	{
		if (is_null(self::$_ip))
		{
			require_once(dirname(__FILE__) . '/../../alpha/apps/kaltura/lib/myIPGeocoder.class.php');
			self::$_ip = infraRequestUtils::getRemoteAddress();
		}
		return self::$_ip;
	}

	static public function getHttpReferrer()
	{
		self::$_usesHttpReferrer = true;
		return isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
	}
	
	protected function getFieldValues($extraField)
	{
		switch ($extraField)
		{
		case self::ECF_REFERRER:
			$values = array();
			// a request can theoritically have more than one referrer, in case of several baseEntry.getContextData calls in a single multirequest
			foreach ($this->_referrers as $referrer)
			{
				$values[] = infraRequestUtils::parseUrlHost($referrer);
			}
			return $values;

		case self::ECF_USER_AGENT:
			if (isset($_SERVER['HTTP_USER_AGENT']))
				return array($_SERVER['HTTP_USER_AGENT']);
			break;
		
		case self::ECF_COUNTRY:
			return array(self::getCountry());

		case self::ECF_IP:
			return array(self::getIp());
		}
		
		return array();
	}
	
	protected function applyCondition($fieldValue, $condition, $refValue)
	{
		switch ($condition)
		{			
		case self::COND_MATCH:
			if (!count($refValue))
				return null;
			return in_array($fieldValue, $refValue);
			
		case self::COND_REGEX:
			if (!count($refValue))
				return null;
			foreach($refValue as $curRefValue)
			{
				if ($fieldValue === $curRefValue || 
					preg_match("/$curRefValue/i", $fieldValue))
					return true;
			}
			return false;	

		case self::COND_SITE_MATCH:
			if (!count($refValue))
				return null;
			foreach($refValue as $curRefValue)
			{
				if ($fieldValue === $curRefValue || 
					strpos($fieldValue, "." . $curRefValue) !== false)
					return true;
			}
			return false;

		case self::COND_IP_RANGE:
			if (!count($refValue))
				return null;
			require_once(dirname(__FILE__) . '/../../infra/utils/kIpAddressUtils.php');
			foreach($refValue as $curRefValue)
			{
				if (kIpAddressUtils::isIpInRange($fieldValue, $curRefValue))
					return true;
			}
			return false;

		}
		return $fieldValue;
	}
	
	protected function getConditionKey($condition, $refValue)
	{
		switch ($condition)
		{			
		case self::COND_REGEX:
		case self::COND_MATCH:
		case self::COND_SITE_MATCH:
			return "_{$condition}_" . implode(',', $refValue);
		case self::COND_IP_RANGE:
			return "_{$condition}_" . implode(',', str_replace('/', '_', $refValue)); // ip range can contain slashes
		}
		return '';
	}
	
	protected function addExtraFieldInternal($extraField, $condition, $refValue)
	{
		$extraFieldParams = array($extraField, $condition, $refValue);
		if (in_array($extraFieldParams, $this->_extraFields))
			return;			// already added
		$this->_extraFields[] = $extraFieldParams;
		if ($extraField != self::ECF_REFERRER || self::$_usesHttpReferrer)
			self::$_hasExtraFields = true;
		
		foreach ($this->getFieldValues($extraField) as $valueIndex => $fieldValue)
		{
			$conditionResult = $this->applyCondition($fieldValue, $condition, $refValue);
			$key = "___cache___{$extraField}_{$valueIndex}" . $this->getConditionKey($condition, $refValue);
			$this->_params[$key] = $conditionResult;
		}
		
		$this->_cacheKeyDirty = true;
	}

	protected function addExtraFields()
	{
		$extraFieldsCache = kCacheManager::getCache(kCacheManager::APC);
		if (!$extraFieldsCache)
			return;
		
		$extraFields = $extraFieldsCache->get(self::EXTRA_KEYS_PREFIX . $this->_cacheKey);
		if (!$extraFields)
			return;
		
		foreach ($extraFields as $extraFieldParams)
		{
			call_user_func_array(array('kApiCache', 'addExtraField'), $extraFieldParams);
			call_user_func_array(array($this, 'addExtraFieldInternal'), $extraFieldParams);			// the current instance may have not been activated yet
		}
		
		$this->finalizeCacheKey();
	}
	
	protected function storeExtraFields()
	{
		if (!$this->_cacheKeyDirty)
			return;			// no extra fields were added to the cache

		$extraFieldsCache = kCacheManager::getCache(kCacheManager::APC);
		if (!$extraFieldsCache)
		{
			self::disableCache();
			return;
		}
		
		if ($extraFieldsCache->set(self::EXTRA_KEYS_PREFIX . $this->_originalCacheKey, $this->_extraFields, self::CONDITIONAL_CACHE_EXPIRY) === false)
		{
			self::disableCache();
			return;
		}
		
		$this->finalizeCacheKey();			// update the cache key to include the extra fields
	}
	
	protected function finalizeCacheKey()
	{
		if (!$this->_cacheKeyDirty)
			return;
		$this->_cacheKeyDirty = false;
	
		ksort($this->_params);
		$this->_cacheKey = $this->_cacheKeyPrefix . md5( http_build_query($this->_params, '', '&') );		// we have to explicitly set the separator since symfony changes it to '&amp;'
		if (is_null($this->_originalCacheKey))
			$this->_originalCacheKey = $this->_cacheKey;
	}

	/**
	 * @return int
	 */
	public static function getTime()
	{
		self::setConditionalCacheExpiry(600);
		return time();
	}
}
