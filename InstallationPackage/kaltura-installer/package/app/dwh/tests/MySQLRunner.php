<?php
require_once 'Configuration.php';

class MySQLRunner
{
	public static function execute($sql, $params=array(), $returnResults=true)
	{
		global $CONF;
		$db = new MySQLRunner($CONF->DbHostName,$CONF->DbPort, $CONF->DbUser, $CONF->DbPassword);
		return $db->run($sql, $params, $returnResults);
	}

	public function __construct($host, $port, $user, $password)
	{
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
	}
	
	private $link = null;
	private $host = 'localhost';
	private $port = 3306;
	private $user = '';
	private $password = '';
	
	private function connect()
	{
		$this->link = new mysqli("p:".$this->host, $this->user, $this->password, null, $this->port ); 
		if ($this->link->connect_error) 
		{
			print('Could not connect: ' . $this->link->connect_error);
			exit(1);
		}
	}

	private function disconnect()
	{
		if ($this->link) 
		{
			$this->link->close();
		}
		$this->link=null;
	}
	
	public function run($sql, $params=array())
	{
		$this->connect();
		
		foreach ($params as $param)
		{
			$sql = preg_replace('/\?/', $param, $sql, 1);
		}

		$result = $this->link->multi_query("SET query_cache_type=0;".$sql);		
		if (!$result || $this->link->errno) 
		{
			print( "Could not successfully run query ($sql) from DB: " .  $this->link->error);
			$this->disconnect();
			exit(1);
		}
		
		$rows = array();
		do{		
			$result = $this->link->use_result();
			
			if($result)
			{
				while ($row = $result->fetch_assoc()) 
				{
					$rows[]=$row;
				}		
				$result->close();
			}	

		} while ($this->link->next_result());
			
		$this->disconnect();
		return $rows;
	}	
}
?>
