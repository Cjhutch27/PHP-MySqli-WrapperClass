<?php

require_once ('simpleSQL.php');

class simpleSQL_Manager extends simpleSQL{

	protected static $managed_instance;

	private $objects = array();


	public function __construct($host = null,$user = null,$pass = null,$dbname = null,$connect = true,$port = null,$charset = 'utf8')
	{
		if($host == null)
			return;
		else if ($host!=null && $user != null && $pass != null && $dbname != null)
			array_push($this->objects, new simpleSQL($host,$user,$pass,$dbname,$connect));
        else 
            return;

    }

    public function closeConnection($con)
    {
    	$count = 0;
    	if(!is_string($con)){
			foreach ($this->objects as $key => $value) {
    			if($value->name == $con->name)
    				unset($this->objects[$count]);
    			$count+=1;
    		}
    	}else{
    		foreach ($this->objects as $key => $value) {
    			if($value->name == $con)
    				unset($this->objects[$count]);
    				$count+=1;
    		}
    	}
    }

    public function closeAllConnections()
    {
		foreach ($this->objects as $key => $value) {
    		if($value->connected && $value->name != null)
    			$value->disconnect();
    	}
    }

    public function addConnection($con)
    {
    	if($con->name == null)
    		$con->name = $con->getName();
    	foreach ($this->objects as $key => $value) {
    		if($value->name == $con->name)
    			return "Connection with that name already exists";
    	}

    	array_push($this->objects, $con);
    }

    public function addMultipleConnections($con)
    {
    	if(!is_array($con))
    		return;
		foreach ($con as $key => $value) {
    		if($value->$connected && $value->name != null)
    			array_push($this->objects, $value);
    	}
    }

    public function viewConnection($name,$display = false)
    {
		foreach ($this->objects as $key => $value) {
    		if($value->connected && $value->name == $name)
    			return $value->getAllSettings($display);
    	}
    	return "No connection found with the name " . $name;
    }

    public function viewAllConnections($display = false)
    {
    	$all = array();
		foreach ($this->objects as $key => $value) {
    		if($value->connected)
    			array_push($all, $value->getAllSettings());
    	}
    	if($display)
    		var_dump($all);
    	else
    		return $all;
    }

    public function masterQuery($SQL_Statement)
    {
    	foreach ($this->objects as $key => $value) {
    		if($value->$connected)
    			$value->queryStatement($SQL_Statement);
    	}
    }

    public function viewAllLogs($displayOnCall = false)
    {
    	$logs = array();
    	foreach ($this->objects as $key => $value) {
    		if($value->getLogSettings())
    			array_push($logs, $value->getLog());
    	}
    	if($displayOnCall)
    		var_dump($logs);
    	else
    		return $logs;
    }

    public function setAllLogLimits($Limit)
    {
    	foreach ($this->objects as $key => $value) {
    			$value->setLogLimit($Limit);
    	}
    }

    public function clearAllLogs()
    {
    	foreach ($this->objects as $key => $value) {
    			$value->clearLog();
    	}
    }

    public function setAllEscapeStrings($List)
    {
    	if(!is_array($List))
    		return;
    	foreach ($this->objects as $key => $value) {
    			$value->setEscapeStrings($List);
    	}
    }

    public function setAllLockMethods($var)
    {
    	if(is_integer($var))	{
			foreach ($this->objects as $key => $value) {
    			$value->setLockSeting($var);
    		}
    	}
    }


    function __destruct() 
    {
     $this->closeAllConnections();
    }

}


?>