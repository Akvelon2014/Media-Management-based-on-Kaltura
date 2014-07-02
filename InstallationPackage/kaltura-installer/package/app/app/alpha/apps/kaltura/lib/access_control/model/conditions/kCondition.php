<?php
/**
 * @package Core
 * @subpackage model.data
 * @abstract
 */
abstract class kCondition 
{
	/**
	 * @var int ConditionType
	 */
	protected $type;
	
	/**
	 * @var bool
	 */
	protected $not = false;

	public function __construct($not = false)
	{
		$this->setNot($not);
	}
	
	/**
	 * @param accessControl $accessControl
	 * @return bool
	 */
	abstract protected function internalFulfilled(accessControl $accessControl);
	
	/**
	 * @param accessControl $accessControl
	 * @return bool
	 */
	final public function fulfilled(accessControl $accessControl)
	{
		return $this->calcNot($this->internalFulfilled($accessControl));
	}
	
	/**
	 * @return int ConditionType
	 */
	public function getType() 
	{
		return $this->type;
	}

	/**
	 * @param int $type ConditionType
	 */
	protected function setType($type) 
	{
		$this->type = $type;
	}
	
	/**
	 * @return bool
	 */
	public function getNot() 
	{
		return $this->not;
	}

	/**
	 * @param bool $not
	 */
	public function setNot($not) 
	{
		$this->not = $not;
	}

	/**
	 * Calculates the NOT operator
	 * @param bool
	 * @return bool
	 */
	private function calcNot($value) 
	{
		return $this->not ? !$value : $value;
	}

	/**
	 * @param kScope $scope
	 * @return bool
	 */
	public function shouldDisableCache($scope)
	{
		return true;
	}
}
