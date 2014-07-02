<?php

class APICall
{
	private $id;
	private $fields;

	const REQUEST_START_REGEX = '/[^ ]+ [^ ]+ \[[^\]]+\] \[([^\]]+)\]: request_start,[^,]*,\"[^\"]*\",[^,]*,\"([^\"]*)\",[^,]*,([^,]*),([^,]*),(\d+),[01].*/';
        const REQUEST_START_SESSION_ID_INDEX = 0;
        const REQUEST_START_CLIENT_TAG_INDEX = 1;
        const REQUEST_START_SERVICE_INDEX = 2;
        const REQUEST_START_ACTION_INDEX = 3;
        const REQUEST_START_REQUEST_INDEX_INDEX = 4;

        const REQUEST_END_REGEX = "/[^ ]+ [^ ]+ \[[^\]]+\] \[([^\]]+)\]: request_end,([^,]*),([^,]*),[^,]*,([01]),\"[^\"]*\",(\d+\.\d+),[01],([^,]*),(\d+).*/";
        const REQUEST_END_SESSION_ID_INDEX = 0;
        const REQUEST_END_PARTNER_ID_INDEX = 1;
        const REQUEST_END_PARENT_PARTNER_ID_INDEX = 2;
        const REQUEST_END_IS_ADMIN_INDEX = 3;
        const REQUEST_END_DURATION_INDEX = 4;
        const REQUEST_END_ERROR_CODE_INDEX = 5;
        const REQUEST_END_REQUEST_INDEX_INDEX = 6;

        public static function validLine($line)
        {
		if (!self::ignoredLine($line))
		{
			if (strpos($line, 'request_end')>-1)
			{
				$call = self::CreateAPICallByLine($line);
				$partner_id_str = 'PARTNER_ID';
				return $call->$partner_id_str != '';
			}
			return true;
		}
                return false;
        }
	
	public static function ignoredLine($line)
	{
		if ((strpos($line, 'request_start')>-1 && (preg_match(self::REQUEST_START_REGEX, $line)>0)) || 
                	(strpos($line, 'request_end')>-1 && (preg_match(self::REQUEST_END_REGEX, $line)>0)))
		{
			return false;
		}
		return true;
	}

	public static function getLineID($line)
	{
		if (strpos($line, 'request_start')>-1 && (preg_match(self::REQUEST_START_REGEX, $line, $matches)>0))
		{
			return self::generateID($matches[self::REQUEST_START_SESSION_ID_INDEX+1], $matches[self::REQUEST_START_REQUEST_INDEX_INDEX+1]);
		} 
                else if (strpos($line, 'request_end')>-1 && (preg_match(self::REQUEST_END_REGEX, $line, $matches)>0))
		{
			return self::generateID($matches[self::REQUEST_END_SESSION_ID_INDEX+1], $matches[self::REQUEST_END_REQUEST_INDEX_INDEX+1]);
		}
	}

	public function getID()
	{
		return $this->id;
	}
	
	private static function generateID($sessionID, $requestIndex)
	{
		return $sessionID."_".$requestIndex;
	}

	private function __construct($id)
        {
		$this->id = $id;
        }
	
	public static function CreateAPICallByID($sessionID, $requestIndex)
	{
		$id = self::generateID($sessionID, $requestIndex);
		$instance = new self($id);	
		return $instance;
	}
	
	public static function CreateAPICallByLine($line)
	{
		$id = self::getLineID($line);
		$instance = new self($id);	
                $instance->populateFields($line);
		return $instance;
	}

	public function update($line)
	{
		$this->populateFields($line);
	}

	private function populateFields($line)
	{
		$dict=array();
		if (strpos($line, 'request_start')>-1)
                {
                        preg_match(self::REQUEST_START_REGEX, $line, $matches);
                        $dict["CLIENT_TAG"] = $matches[self::REQUEST_START_CLIENT_TAG_INDEX+1];
                        $dict["SERVICE"] = $matches[self::REQUEST_START_SERVICE_INDEX+1];
                        $dict["ACTION"] = $matches[self::REQUEST_START_ACTION_INDEX+1];
                }
                else
                {
                        preg_match(self::REQUEST_END_REGEX, $line, $matches);
			$str_partner_id = $matches[self::REQUEST_END_PARTNER_ID_INDEX+1];
                        $dict["PARTNER_ID"]=is_numeric($str_partner_id) ? $str_partner_id : $matches[self::REQUEST_END_PARENT_PARTNER_ID_INDEX+1];
                        $dict["IS_ADMIN"]=$matches[self::REQUEST_END_IS_ADMIN_INDEX+1];
                        $dict["DURATION"]=floor($matches[self::REQUEST_END_DURATION_INDEX+1]*1000);
                        $dict["ERROR_CODE"]=$matches[self::REQUEST_END_ERROR_CODE_INDEX+1];
                }

		foreach ($dict as $key => $val)
                {
                        $this->fields[$key] = $val;
                }
	}

	function __get($id)
	{
		return $this->fields[$id];
	}	
}

?>
