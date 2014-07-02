<?php
/**
 * @package infra
 * @subpackage log
 */
class KalturaLog
{
	private static $_logger;
	private static $_initialized = false;
	private static $_instance = null;
	private static $_enableTests = false;
	
    const EMERG   = Zend_Log::EMERG;
    const ALERT   = Zend_Log::ALERT;
    const CRIT    = Zend_Log::CRIT;
    const ERR     = Zend_Log::ERR;
    const WARN    = Zend_Log::WARN;
    const NOTICE  = Zend_Log::NOTICE;
    const INFO    = Zend_Log::INFO;
    const DEBUG   = Zend_Log::DEBUG;
    
    const LOG_TYPE_ANALYTICS = 'LOG_TYPE_ANALYTICS';
	
	public static function getInstance ()
	{
		 if (!self::$_instance) 
		 	self::$_instance = new KalturaLog();
		 	
		 return self::$_instance;
	}
	
	public static function initLog(Zend_Config $config = null)
	{
		if (self::$_initialized)
			return;
		
		self::$_enableTests = isset($config->enableTests) ? $config->enableTests : false;
		
		self::$_logger = KalturaLogFactory::getLogger($config);
		self::$_initialized = true;
	}
	
	public static function setLogger($logger)
	{
		self::$_logger = $logger;
		self::$_initialized = true;
	}
	
	static function log($message, $priority = self::NOTICE)
	{
		self::initLog();
		self::$_logger->log($message, $priority);
	}
	
	static function alert($message)
	{
		self::initLog();
		self::$_logger->log($message, self::ALERT);
	}

	static function crit($message)
	{
		self::initLog();
		self::$_logger->log($message, self::CRIT);
	}

	static function err($message)
	{
		self::initLog();
		self::$_logger->log($message, self::ERR);
	}	

	static function warning($message)
	{
		self::initLog();
		self::$_logger->log($message, self::WARN);
	}

	static function notice($message)
	{
		self::initLog();
		self::$_logger->log($message, self::NOTICE);
	}	

	static function info($message)
	{
		self::initLog();
		self::$_logger->log($message, self::INFO);
	}

	static function debug($message)
	{
		self::initLog();
		self::$_logger->log($message, self::DEBUG);
	}

	static function analytics(array $data)
	{
		self::logByType(implode(',', $data), self::LOG_TYPE_ANALYTICS, self::NOTICE);
	}
	
	static function logByType($message, $type, $priority = self::DEBUG)
	{
		self::initLog();
		
		//check if this is a zend log (and not a sfLogger)
		if (get_class(self::$_logger) == 'Zend_Log')		
			self::$_logger->setEventItem("type", $type);
			
		self::$_logger->log($message, $priority);
		
		if (get_class(self::$_logger) == 'Zend_Log')
			self::$_logger->setEventItem("type", '');
	}
	
	static function setContext($context)
	{
		self::initLog();
		self::$_logger->setEventItem("context", $context);
	}
	
	static function getEnableTests()
	{
		return self::$_enableTests;
	}
}

/**
 * @package infra
 * @subpackage log
 */
class KalturaStdoutLogger
{
	public function log($message, $priority = KalturaLog::NOTICE)
	{
		echo "[" . date('Y-m-d H:i:s') . "]$message\n";
	}
}

/**
 * @package infra
 * @subpackage log
 */
class LogTime 
{
	public function __toString()
	{
		return date('Y-m-d H:i:s');
	}
}

/**
 * @package infra
 * @subpackage log
 */
class UniqueId
{
	static $_uniqueId = null;
	public function __toString()
	{
		if (self::$_uniqueId === null)
		{
			self::$_uniqueId = (string)rand();
			// add a the unique id to Apache's internal variable so we can later log it using the %{KalturaLog_UniqueId}n placeholder
			// within the LogFormat apache directive. This way each access_log record can be matched with its kaltura log lines.
			// before setting the apache note name and value, a condition checks if function exists,
			// due to fact that running from command line will not define this function
			if (function_exists('apache_note'))
				apache_note("KalturaLog_UniqueId", self::$_uniqueId);
		}
			
		return self::$_uniqueId;
	}
}

/**
 * @package infra
 * @subpackage log
 */
class LogMethod
{
	public function __toString()
	{
		$backtraceIndex = 3;
		$backtrace = debug_backtrace();
		
		while(
			$backtraceIndex < count($backtrace)
			&&
			(
//				$backtrace[$backtraceIndex]["file"] == __FILE__
//				||
				(isset($backtrace[$backtraceIndex]["class"]) && is_int(strpos($backtrace[$backtraceIndex]["class"], 'Log')))
				||
				(isset($backtrace[$backtraceIndex]["function"]) && $backtrace[$backtraceIndex]["function"] == 'log')
			)
		)
			$backtraceIndex++;
			
		if (isset($backtrace[$backtraceIndex]))
		{
			if (isset($backtrace[$backtraceIndex]["class"]))
				return $backtrace[$backtraceIndex]["class"].$backtrace[$backtraceIndex]["type"].$backtrace[$backtraceIndex]["function"];
			else
				return $backtrace[$backtraceIndex]["function"];
		}
		else 
		{
			return "global";
		}
	}
}

/**
 * @package infra
 * @subpackage log
 */
class LogIp
{
	static $_ip = null;
	public function __toString()
	{
		if (self::$_ip === null)
		{
			try {
				self::$_ip = (string)infraRequestUtils::getRemoteAddress();
			}
			catch (Exception $ex)
			{
				self::$_ip = '';
			}
		}
			
		return self::$_ip;
	}
}

/**
 * @package infra
 * @subpackage log
 */
class LogDuration
{
	static $_lastMicroTime = null;
	public function __toString()
	{
		$curTime = microtime(true);
		
		if (self::$_lastMicroTime === null)
		{
			if (isset($GLOBALS["start"]))
				self::$_lastMicroTime = $GLOBALS["start"];
			else
				self::$_lastMicroTime = $curTime;
    	}
		$result = sprintf("%.6f", $curTime - self::$_lastMicroTime);
			
		self::$_lastMicroTime = $curTime;
		
		return $result;
	}
}
