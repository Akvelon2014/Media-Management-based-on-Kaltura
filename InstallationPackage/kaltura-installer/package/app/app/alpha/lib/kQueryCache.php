<?php

class kQueryCache 
{
	const INVALIDATION_TIME_MARGIN_SEC = 60;		// When comparing the invalidation key timestamp to the query timestamp, 
													// the query timestamp should be greater by this value to use the cache
	const QUERY_MASTER_TIME_MARGIN_SEC = 300;		// The time frame after a change to a row during which we should query the master
	const MAX_CACHED_OBJECT_COUNT = 250;			// Select queries that return more objects than this const will not be cached
	const CACHED_QUERIES_EXPIRY_SEC = 86400;		// The expiry of the query keys in the memcache 	
	const INVALIDATION_KEYS_EXPIRY_MARGIN = 3600;	// An extra expiry time given to invalidation keys over cached queries

	const MAX_IN_CRITERION_INVALIDATION_KEYS = 10;	// Maximum number of allowed elements in 'IN' to use the query cache
	
	const CACHE_PREFIX_QUERY = 'QCQ-';				// = Query Cache - Query
	const CACHE_PREFIX_INVALIDATION_KEY = 'QCI-';	// = Query Cache - Invalidation key
	const DONT_CACHE_KEY = 'QCC-DontCache';			// when set new queries won't be cached in the memcache
	
	const QUERY_TYPE_SELECT = 'sel-';
	const QUERY_TYPE_COUNT =  'cnt-';
	
	const QUERY_DB_UNDEFINED = 0;
	const QUERY_DB_MASTER = 1;
	const QUERY_DB_SLAVE = 2;
	
	const CACHE_VERSION = '1';
	
	protected static $s_memcacheKeys = null;
	protected static $s_memcacheQueries = null;
	protected static $s_memcacheInited = false;
	
	protected static function initGlobalMemcache()
	{
		if (self::$s_memcacheInited)
		{
			return;
		}
		
		self::$s_memcacheInited = true;
		
		self::$s_memcacheKeys = kCacheManager::getCache(kCacheManager::MC_GLOBAL_KEYS);
		if (self::$s_memcacheKeys === null)
		{
			// no reason to init the queries server, the query cache won't be used anyway
			return;
		}

		self::$s_memcacheQueries = kCacheManager::getCache(kCacheManager::MC_GLOBAL_QUERIES);
	}
	
	protected static function replaceVariable($formatString, $variableValue)
	{
		$firstVarPos = strpos($formatString, '%s');
		if ($firstVarPos === false)
		{
			return $formatString;
		}
		
		return substr_replace($formatString, $variableValue, $firstVarPos, 2);
	}
	
	protected static function getInvalidationKeysForQuery($invalidationKeyRules, Criteria $criteria)
	{
		foreach ($invalidationKeyRules as $invalidationKeyRule)
		{
			$invalidationKeys = array($invalidationKeyRule[0]);
			for ($colIndex = 1; $colIndex < count($invalidationKeyRule); $colIndex++)
			{
				$columnName = $invalidationKeyRule[$colIndex];
				$criterion = $criteria->getCriterion($columnName);
				if (!$criterion)
				{
					$invalidationKeys = null;
					break;
				}
				
				if (in_array(Criterion::ODER, $criterion->getConjunctions()))
				{
					$invalidationKeys = null;
					break;
				}
				
				if ($criterion->getComparison() == Criteria::EQUAL)
				{
					$values = array($criterion->getValue());
				}
				else if ($criterion->getComparison() == Criteria::IN && 
					count($criterion->getValue()) < self::MAX_IN_CRITERION_INVALIDATION_KEYS)
				{
					$values = $criterion->getValue();
				}
				else
				{
					$invalidationKeys = null;
					break;
				}
				
				$newInvalidationKeys = array(); 
				foreach ($invalidationKeys as $invalidationKey)
				{
					foreach ($values as $value)
					{
						$newInvalidationKeys[] = self::replaceVariable($invalidationKey, str_replace(' ', '_', $value));
					}
				}
				$invalidationKeys = $newInvalidationKeys;
			}
			
			if (!is_null($invalidationKeys))
			{
				return $invalidationKeys;
			}
		}
			
		return array();
	}

	public static function getCachedQueryResults(Criteria $criteria, $queryType, $peerClassName, &$cacheKey, &$queryDB)
	{
		if (!kConf::get("query_cache_enabled"))
		{
			return null;
		}
		
		// if the criteria has an empty IN, no need to go to the DB or memcache - return an empty array
		foreach ($criteria->getMap() as $criterion)
		{
			if (in_array(Criterion::ODER, $criterion->getConjunctions()))
			{
				continue;
			}
			
			if ($criterion->getComparison() == Criteria::IN && !$criterion->getValue())
			{
				KalturaLog::debug("kQueryCache: criteria has empty IN, returning empty result set, peer=$peerClassName");
				return array();
			}
		}
		
		// initialize
		$invalidationKeyRules = call_user_func(array($peerClassName, 'getCacheInvalidationKeys'));
		$invalidationKeys = self::getInvalidationKeysForQuery($invalidationKeyRules, $criteria);
		if (!$invalidationKeys)
		{
			return null;
		}
		
		self::initGlobalMemcache();
		if (self::$s_memcacheQueries === null)			// we must have both memcaches initialized
		{
			return null;
		}
		
		// build memcache query
		foreach ($invalidationKeys as $index => $invalidationKey)
		{
			$invalidationKeys[$index] = self::CACHE_PREFIX_INVALIDATION_KEY.$invalidationKey;
		}

		$keysToGet = $invalidationKeys;
		$keysToGet[] = self::DONT_CACHE_KEY;
		
		$queryStart = microtime(true);
		$cacheResult = self::$s_memcacheKeys->multiGet($keysToGet);
		KalturaLog::debug("kQueryCache: keys query took " . (microtime(true) - $queryStart) . " seconds");
		
		if ($cacheResult === false)
		{
			KalturaLog::debug("kQueryCache: failed to query keys memcache, not using query cache");
			return null;
		}
		
		// don't cache the result if the 'dont cache' flag is enabled
		$cacheQuery = true;
		if (array_key_exists(self::DONT_CACHE_KEY, $cacheResult))
		{
			if ($cacheResult[self::DONT_CACHE_KEY])
			{
				KalturaLog::debug("kQueryCache: dontCache key is set -> not caching the result");
				if (class_exists('KalturaResponseCacher'))
					KalturaResponseCacher::disableConditionalCache();
				$cacheQuery = false;
			}
			unset($cacheResult[self::DONT_CACHE_KEY]);
		}
		
		// check whether we should query the master
		$queryDB = self::QUERY_DB_SLAVE;
		$currentTime = time();
		foreach ($cacheResult as $invalidationKey => $invalidationTime)
		{			
			if ($currentTime < $invalidationTime + self::QUERY_MASTER_TIME_MARGIN_SEC)
			{
				KalturaLog::debug("kQueryCache: changed recently -> query master, peer=$peerClassName, invkey=$invalidationKey querytime=$currentTime invtime=$invalidationTime");
				$queryDB = self::QUERY_DB_MASTER;
				if ($currentTime < $invalidationTime + self::INVALIDATION_TIME_MARGIN_SEC)
				{
					return null;			// The query won't be cached since cacheKey is null, it's ok cause it won't be used anyway
				}
			}
		}
		
		if (class_exists('KalturaResponseCacher'))
		{
			$invalidationTime = 0;
			if ($cacheResult)
				$invalidationTime = max($cacheResult);
			KalturaResponseCacher::addInvalidationKeys($invalidationKeys, $invalidationTime);
		}
		
		// check whether we have a valid cached query
		$origCacheKey = self::CACHE_PREFIX_QUERY.$queryType.md5(serialize($criteria) . self::CACHE_VERSION);
		if ($cacheQuery)
		{
			$cacheKey = $origCacheKey; 
		}
		
		$queryStart = microtime(true);
		$queryResult = self::$s_memcacheQueries->get($origCacheKey);
		KalturaLog::debug("kQueryCache: query took " . (microtime(true) - $queryStart) . " seconds");
		
		if (!$queryResult)
		{	
			KalturaLog::debug("kQueryCache: cache miss, peer=$peerClassName, key=$origCacheKey");
			return null;
		}
		
		list($queryResult, $queryTime, $debugInfo) = $queryResult;
		
		$existingInvKeys = array();
		foreach ($cacheResult as $invalidationKey => $invalidationTime)
		{
			$existingInvKeys[] = "$invalidationKey:$invalidationTime";
			
			if ($queryTime < $invalidationTime + self::INVALIDATION_TIME_MARGIN_SEC)
			{
				KalturaLog::debug("kQueryCache: cached query invalid, peer=$peerClassName, key=$origCacheKey, invkey=$invalidationKey querytime=$queryTime debugInfo=$debugInfo invtime=$invalidationTime");
				return null;
			}
		}
		
		// return from memcache
		$existingInvKeys = implode(',', $existingInvKeys);
		
		KalturaLog::debug("kQueryCache: returning from memcache, peer=$peerClassName, key=$origCacheKey queryTime=$queryTime debugInfo=$debugInfo invkeys=[$existingInvKeys]");
		return $queryResult;
	}
	
	public static function cacheQueryResults($cacheKey, $queryResult)
	{
		if (self::$s_memcacheQueries === null || $cacheKey === null || 
			(is_array($queryResult) && count($queryResult) > self::MAX_CACHED_OBJECT_COUNT))
		{
			return;
		}

		$uniqueId = new UniqueId();
		$debugInfo = (isset($_SERVER["HOSTNAME"]) ? $_SERVER["HOSTNAME"] : '');
		$debugInfo .= "[$uniqueId]";
		
		$queryTime = time();
		KalturaLog::debug("kQueryCache: Updating memcache, key=$cacheKey queryTime=$queryTime");
		self::$s_memcacheQueries->set($cacheKey, array($queryResult, $queryTime, $debugInfo), self::CACHED_QUERIES_EXPIRY_SEC);
	}
	
	public static function invalidateQueryCache($object)
	{
		if (!kConf::get("query_cache_invalidate_on_change"))
		{
			return;
		}
		
		$invalidationKeys = $object->getCacheInvalidationKeys();
		if (!$invalidationKeys)
		{
			return;
		}
		
		self::initGlobalMemcache();
		if (self::$s_memcacheKeys === null)			// The keys memcache suffices here
		{
			return null;
		}
				
		$currentTime = time();
		foreach ($invalidationKeys as $invalidationKey)
		{
			$invalidationKey = self::CACHE_PREFIX_INVALIDATION_KEY . str_replace(' ', '_', $invalidationKey);
			KalturaLog::debug("kQueryCache: updating invalidation key, invkey=$invalidationKey");
			if (!self::$s_memcacheKeys->set($invalidationKey, $currentTime, 
				self::CACHED_QUERIES_EXPIRY_SEC + self::INVALIDATION_KEYS_EXPIRY_MARGIN))
			{
				KalturaLog::err("kQueryCache: failed to update invalidation key");
			}
		}
	}
}
