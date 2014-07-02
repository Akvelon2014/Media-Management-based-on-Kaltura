<?php
class Configuration
{
  public static $KETTLE_HOME;
  private $configFile = '.kettle/kettle.properties';

  private $items = array();

  function __construct() { $this->parse(); }

  function __get($id) 
  { 
	
	$res = $this->items[ $id ];
	
	while (true)
	{
		$start = strpos($res, '${');
		if($start===false)
		{
			return $res;
		}
		else
		{
			$end = strpos($res, '}');
			if($end===false || $end <= $start)
			{
				return $res;				
			}
			else
			{
				$var = substr($res,$start,$end-$start+1);
				$res = str_replace($var ,$this->__get(substr($var,2,count($var)-2)), $res);
			}
		}
	}
  }

  function parse()
  {
	self::$KETTLE_HOME = getenv('KETTLE_HOME');
    $fh = fopen( self::$KETTLE_HOME.'/'.$this->configFile, 'r' );
    while( $l = fgets( $fh ) )
    {
      if ( preg_match( '/^#/', $l ) == false )
      {
        preg_match('/(?P<key>.*)=(?P<val>.*)/', $l, $found );
		if(count($found)>3)
		{
			$this->items[ trim($found[1]) ] = trim($found[2]);
		}
      }
    }
    fclose( $fh );
  }
}

$CONF = new Configuration();
?>
