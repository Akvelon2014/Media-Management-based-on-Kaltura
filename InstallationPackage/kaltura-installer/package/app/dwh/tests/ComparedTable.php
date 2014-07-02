<?php

class ComparedTable
{
	private $tableName;
	private $tableKey;
	private $tableMeasure;

	public function __construct($tableKey, $tableName, $tableMeasure)
	{
		$this->tableName=$tableName;
		$this->tableKey=$tableKey;
		$this->tableMeasure=$tableMeasure;
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getTableKey()
	{
		return $this->tableKey;
	}

	public function getTableMeasure()
	{
		return $this->tableMeasure;
	}
}

?>
