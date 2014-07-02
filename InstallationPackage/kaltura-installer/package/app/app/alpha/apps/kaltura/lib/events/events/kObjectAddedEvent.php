<?php
/**
 * Applicative event that raised implicitly by the developer
 * @package Core
 * @subpackage events
 */
class kObjectAddedEvent extends kApplicativeEvent
{
	const EVENT_CONSUMER = 'kObjectAddedEventConsumer';
	
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}
	
	/**
	 * @param kObjectAddedEventConsumer $consumer
	 * @return bool true if should continue to the next consumer
	 */
	protected function doConsume(KalturaEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeAddedEvent($this->object))
			return true;
			
		$additionalLog = '';
		if(method_exists($this->object, 'getId'))
			$additionalLog .= 'id [' . $this->object->getId() . ']';
			
		KalturaLog::debug(get_class($this) . ' event consumed by ' . get_class($consumer) . ' object type [' . get_class($this->object) . '] ' . $additionalLog);
		return $consumer->objectAdded($this->object, $this->raisedJob);
	}

}