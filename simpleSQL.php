<?php

/**
 * SimpleSQL
 *
 * @category  Database Access
 * @author    Christopher Hutchison <chrishutchison.dev@gmail.com>
 * @copyright Copyright (c) 2010-2017
 * @link      https://github.com/Cjhutch27/PHP-MySqli-WrapperClass
 * @version   1.0
 */

class simpleSQL
{

   /**
     * Static instance of self
     * @var simpleSQL
     */
    protected static $_instance;

   /**
     * An optional string to help identify each connection in a SimpleSQL_Manager object
     * @var string
     */
    protected $name;

   /**
    * Database host name
    * @var string
    */
	private $host;

   /**
    * Database name
    * @var string
    */
	private $dbname;

   /**
    * Database user name
    * @var string
    */
	private $dbuser;

   /**
    * Database password
    * @var string
    */
	private $dbpass;

   /**
    * Object lock settings (0 = unlocked, 1= read only, 2= locked)
    * @var int
    */
	private $lock = 0;

   /**
    * Optional port for connecting to database
    * @var string
    */
	private $port;

   /**
    * String variable for holding a where clause to be executed
    * @var string
    */
	private $whereClause;

   /**
    * String variable for holding a Order By clause to be executed
    * @var string
    */
	private $orderByClause;

   /**
    * String variable for holding a Group By clause to be executed
    * @var string
    */
	private $groupClause;

   /**
    * String variable for holding a Join  clause to be executed
    * @var string
    */
	private $joinClause;

   /**
    * String variable for holding a Having clause to be executed
    * @var string
    */
	private $having;

   /**
    * bool that enables automatically escaping variables
    * @var bool
    */
	private $enableEscapeStrings = true;

   /**
    * Array that holds any extra escape strings if specified when enableEscapeStrings is enabled
    * @var array
    */
	private $extraEscapeStrings = array();

   /**
    * Holds last error of SQL query
    * @var string
    */
	private $lastError;

   /**
    * Stores last SQL error number
    * @var int
    */
	private $lastErrorNo;

   /**
    * Variable that determines if errors should automatically be displayed
    * @var bool
    */
	private $displayErrors = true;

   /**
    * Limit for how many queries to log if enabled
    * @var int
    */
	private $logLimit = 20;

   /**
    *  A log meant to store each query performed, and the status of each query
    * @var array
    */
	private $log = array();

   /**
    * Enables or disables query logging
    * @var bool
    */
	private $logQueries = true;

   /**
    * The default table to manipulate if not specified, or the most recent table used
    * @var string
    */
	private $currentTable;

   /**
    * The current database connection
    * @var 
    */
	private $dbConnection;

   /**
    * Optional ability to reconnect automatically if database connection drops
    * @var bool
    */
	private $autoReConnect = false;

   /**
    * The current query being built through member functions
    * @var string
    */
	private $currQuery;

   /**
    * an optional subquery to be appended to the current query
    * @var string
    */
	private $subQuery;

   /**
    * String variable holding the last query executed
    * @var string
    */
	private $lastQuery;

   /**
    * Optional ability to return all query data as JSON
    * @var bool
    */
	public  $returnAsJson = false;

   /**
    * Allows limits to be appended to al SQL statements as long as set limit is > 0
    * @var bool
    */
	private $autoLimit = false;

   /**
    * Default limit to append if auto limit is enabled. If specified, automatically replaced with method parameter
    * @var int
    */
	private $limit = 0;

   /**
    * Current database connection status.
    * @var bool
    */
	protected $connected = false;
	
	/**
     * @param string $host database host name
     * @param string $user database username
     * @param string $pass database password
     * @param string $dbname database name
     * @param int $port optional port to connect to 
     * @param string $charset charset method to set when connecting
     * @param bool $connect option to automatically connect upon construction 
     */
	public function __construct($host,$user = null,$pass = null,$dbname = null,$connect = true,$port = null,$charset = 'utf8')
	{
		if($host == null)
			return;

		if(is_object($host)){
			$this->dbConnection = $host;
			$this->connected = mysqli_ping($host);
			return;
		}


       $this->host = $host;
       $this->dbname = $dbname;
       $this->dbuser = $user;
       $this->dbpass = $pass;
       if ($connect) 
       		$this->connect();
       self::$_instance = $this;
    }

  /**
   * This method connects the current settings from constructor to the database if not currently connected
   */
   public function connect()
   {
   if($this->connected)
   		return;
   	$this->dbConnection = mysqli_connect($this->host,$this->dbuser,$this->dbpass,$this->dbname);
   	if($this->dbConnection){
   		$this->connected = true;
   	}else{
 		die('Could not connect to Database' . mysqli_connect_error());
   	}
	
   }

  /**
   * This method disconnects the current established connection if one exists
   */
   public function disconnect()
   {
   	if(!$this->connected)
   		return;
   	mysqli_close($this->dbConnection);
   	$this->connected = false;
   }

  /**
   * Accessor function that returns current connection state
   */
   function checkConnection()
   {
   	return $connected;
   }

   /**
   * Sets name for current object
   *
   * @param string $name
   */
   function setName($name)
   {
   	$this->name = $name;
   }

   /**
   * returns name associated with object (unecessary unless managed by manager class)
   *
   */
   function getName()
   {
   	if($this->name == null)
   		return $this->dbname;
   	else 
   		return $this->name;
   }

   /**
   * Method to set limit used for auto limit settings
   *
   * @param int; $limit a value to set for the current query limit. 
   */
   function setLimit($var)
   {
   	if(is_numeric($var))
   		$this->limit = $var;
   }

   /**
   * Accessor function returning limit set for auto limit.
   */
   function getLimit()
   {
   	return $this->limit;
   }

   /**
   * Method to alter current auto limiting settings for queries
   *
   * @param bool $bool a boolean representing desired state of auto limit setting.
   */
   function setAutoLimit($var)
   {
   	if(is_bool($var))
   		$this->autoLimit = $var;
   }

   /**
   * Returns or displays last query
   *
   * @param bool $show if false returns query result otherwise displays last query
   */
   function getLastQuery($show = false)
   {
   	if($show)
   		echo $this->lastQuery;
   	else
   		return $this->lastQuery;
   }

   /**
   * Accessor function returning the auto limit settings set
   */
   function getAutoLimitSettings()
   {
   	return $this->autoLimit;
   }

   /**
   * Method to set the default table to be manipulated if not specified in member function params
   *
   * @param string $tableName a string to set the desired default table in queries
   */
   function setTable($tableName)
   {
   	$this->currentTable = $tableName;
   }

   /**
   * Method to enable or disable escape characters
   *
   * @param bool $var
   */
   function setEscapeSettings($var)
   {
   	if(is_bool($var))
		$this->enableEscapeStrings = $var;
   }

   /**
   * Returns current lock settings
   *
   */
   function getLockSetting()
   {
   	return $this->lock;
   }

   /**
   * Method to set current lock settings for this object
   *
   * @param int $var
   */
   function setLockSeting($var)
   {
   	if(!is_numeric($var))
   		return;
   	if($var <= 2 && $var >= 0)
   		$this->lock = $var;
   }

   /**
   * Returns current ecscape string settings
   *
   */
   function getEscapeSettings()
   {
   	return $this->enableEscapeStrings;
   }

   /**
   * Adds a custom escape string to check for if enabled
   *
   * @param string $var escape string to add
   */
   function addEscapeString($var)
   {
   	array_push($this->extraEscapeStrings, $var);
   }

   /**
   * Method to set extra escape strings
   *
   * @param array $strings a list of escape strings
   */
   public function setEscapeStrings($strings)
   {
   	if(is_array($strings))
   		$this->extraEscapeStrings = $strings;
   }

   /**
   * returns current array of extra escape strings
   *
   */
   function getEscapeStrings()
   {
   return $this->extraEscapeStrings;
   }

   /**
   * Method to enable or disable automatic error display
   *
   * @param bool $var
   */
   function setErrorDisplay($var)
   {
   	if(is_bool($var))
   		$this->displayErrors = $var;
   }

   /**
   * Returns error display settings
   *
   */
   function getErrorDisplay()
   {
   	return $this->displayErrors;
   }

   /**
   * Returns last error number of failed query
   *
   */
   function getLastErrorNo()
   {
   	return $this->lastErrorNo;
   }
   /**
   * Returns last error message of failed query
   *
   */
   function getLastErrorMessage()
   {
   	return $this->lastError;
   }

   function getLastError()
   {
   	if($this->lastError == null || $this->lastErrorNo == null)
   		return "No errors recorded, Results may have been empty";
   	else
   		return $this->lastErrorNo . " : " . $this->lastError;
   }

   /**
   * Method to set the limit of log records 
   *
   * @param int $var
   */
   function setLogLimit($var)
   {
   	if(is_numeric($var))
   		$this->logLimit = $var;
   }

   /**
   * Returns logging limit
   *
   */
   function getLogLimit()
   {
   	return $this->logLimit;
   }

   /**
   * Method to enable or disable logging of queries
   *
   * @param bool $var
   */
   function setLogging($var)
   {
   	if(is_bool($var))
   		$this->logQueries = $var;
   }

   /**
   * Returns current logging settings
   *
   */
   function getLogSettings()
   {
   	return $this->logQueries;
   }

   /**
   * Method to return or display this objects query log
   *
   * @param bool $show if true prints log else returns log array
   */
   function getLog($show = false)
   {
   	if($show)
   		var_dump($this->log);
   	else
   		return $this->log;
   }
   /**
   * Method to clear current log of all history
   *
   */
   function clearLog()
   {
   	$this->log = array();
   }

   /**
   * Method to unlock current object allowing all queries
   *
   */
   function unlock()
   {
   	$this->lock = 0;
   }

   /**
   * Method to return current table settings
   *
   * @return string; A string value representing the default table, or last table manipulated
   */
   function getCurrentTable()
   {
   	return $this->currentTable;
   }

   /**
   * Method to return current instance settings
   *
   * @return array an Array representing all current setting values of this instance.
   */
   public function getAllSettings($display = false)
   {
   	$settings =  array('Name'=>$this->name,
   		'Auto Limit'=>$this->autoLimit,
   		'Limit'=>$this->limit,
   		'Return as JSON'=>$this->returnAsJson,
   		'Connected'=>$this->connected,
   		'Auto Re-Connect'=>$this->autoReConnect,
   		'Auto Escape Strings'=>$this->enableEscapeStrings,
   		'Auto Display Errors'=>$this->displayErrors,
   		'Query Logging'=>$this->logQueries,
   		'Query Logging Limit'=>$this->logLimit,
   		'Lock Settings'=>$this->showLockSetting(),
   		'Current Table'=>$this->currentTable);
   	if($display)
   		var_dump($settings);
   	else
   		return $settings;
   }

   /**
    * A simple SELECT * function to get all rows and columns from a table.
    *
    * @param string $tableName The name of the database table to manipulate.
    * @param int $limit the limit of rows to return if not already set through auto limit
    * 
    * @return  an array containing each row from the database
    */
   public function getAll($tableName = null,$limit = 0) 
   {
   	if(!$this->checkLock(1))
			return "Database locked, cannot perform action";
  	$this->checkIfTableExists($tableName);
   	$this->currQuery = "SELECT * FROM " . $this->currentTable;
   	if(!$this->checkJoinClause(true) || !$this->checkWhereClause(true) || !$this->checkGroupBy(true) || !$this->checkHaving(true) || !$this->checkOrderBy(true))
   		return false;
   	$this->checkLimitSettings($limit);
 	return $this->getSelectResults($this->finalQuery());
   }

   /**
    * A simple SELECT function to grab the specified columns from a table
    *
    * @param string $columnName The name of the column(s) to be manipulated
    * @param string $tableName The name of the database table to manipulate.
    * @param int $limit the limit of rows to return if not already set through auto limit
    * 
    * @return  an array containing each specified column from the database
    */
   public function get($tableName = null,$columnName = "*",$limit=0) 
   {
   	if(!$this->checkLock(1))
			return "Database locked, cannot perform action";
   	   	$this->currQuery = "SELECT ";
   	   	if (is_array($columnName)) {
   	   		 foreach ($columnName as $key => $val) {
   	   		 	$this->checkEscapeString($val);
                if($key == end(array_keys($columnName))){
                	$this->currQuery .= $val;
                }else{
                	$this->currQuery .= $val . ", ";
                }
            }
   	   	}else{
   	   		$this->currQuery.= $columnName;
   	   	}
   	   	$this->currQuery.= " FROM ";
   	   	$this->checkIfTableExists($tableName);
   		$this->currQuery.= $this->currentTable;
   		if(!$this->checkJoinClause(true) || !$this->checkWhereClause(true) || !$this->checkGroupBy(true) || !$this->checkHaving(true) || !$this->checkOrderBy(true))
   			return false;
   	   	$this->checkLimitSettings($limit);
 		return $this->getSelectResults($this->finalQuery());
   }



    /**
    * An INSERT INTO function to insert data into database
    *
    * @param string $tableName the name of the table, defaults to last used table
    * @param array $values an array of values to pass in
    * @param bool $specifyColumnNames true if array specifies column names
    * 
    * @return  a bool indicating query failure or success
    */
   public function insert($tableName = null,$values = null,$specifyColumnNames = false)
   {
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
    $this->checkIfTableExists($tableName);
    if($values == null)
    	return;
   	$this->currQuery = "INSERT INTO " . $tableName;
   	$endvals = "";
   	if($specifyColumnNames)
   		$this->currQuery.= "(";
   	foreach ($values as $key => $value) {
   		$this->checkEscapeString($value);
   		 if($key == end(array_keys($values))){
   		 			if($specifyColumnNames)
                			$this->currQuery .= $key . ")";
                	$endvals .= "'" . $value . "')";
                }else{
                	if($specifyColumnNames)
                			$this->currQuery .= $key . ", ";
                	$endvals .= "'" . $value . "', ";
          }
   	}
   	$this->currQuery .= " VALUES (" . $endvals;
   	return $this->finalQuery();
   }


   public function insertMultiple($tableName = null,$values = null,$columnNames = null)
   {
   if(!$this->checkLock(0))
		return $this->setErrorMessage(0,"Database locked, cannot perform action");
    $this->checkIfTableExists($tableName);
    $keynameStr = "";
    if($columnNames != null && is_array($columnNames)){
    	foreach ($columnNames as $key => $value) {
    		$this->checkEscapeString($value);
    		if($key == end(array_keys($columnNames)))
    			$keynameStr .= $value . ")";
    		else
    			$keynameStr .= $value . ",";
    	}
    }else if ($columnNames != null){
    	trigger_error("Error: Key values cannot be single string");
    	return;
    }

   	foreach ($values as $key => $value) {
   		$this->currQuery = "INSERT INTO " . $this->currentTable;
   		$endvals = "";
   	   	$this->currQuery.=" (";
   		foreach ($value as $key2 => $value2) {
   			 if($key2 == end(array_keys($value))){
   			 		if($columnNames == null)
                		$this->currQuery .= $key2 . ")";
                	$endvals .= "'" . $value2 . "')";
   			 }else{
   			 		if($columnNames == null)
   			 			$this->currQuery .= $key2 . ", ";
                	$endvals .= "'" . $value2 . "', ";
   			 }
   		}
   	  if($columnNames != null)
   	  		$this->currQuery .= $keynameStr;
   	  $this->currQuery .= " VALUES (" . $endvals;
      return $this->finalQuery();
   	}

   }

   /**
    * An Update function to update any values in the database
    *
    * @param string $tableName The name of the table manipulated
    * @param array $values an array of values to update 
    * @param int $limit the limit of rows to effect
    * 
    * @return  a boolean indicating query success or failure
    */
   public function update($tableName = null,$values = null,$limit = 0)
   {
   	if($values == null)
   		return $this->setErrorMessage(0,"Null value for 2nd parameter");
   if(!$this->checkLock(0))
		return $this->setErrorMessage(0,"Database locked, cannot perform action");
   	$this->checkIfTableExists($tableName);
   	$this->currQuery = "UPDATE " . $tableName . " SET ";

   	foreach ($values as $key => $value) {
   		if(is_integer($key))
   			return $this->setErrorMessage(0,"Missing column name");
   		if($key == end(array_keys($values)))
   			$this->currQuery .= $key . " = '".$value."'";
   		else
   			$this->currQuery .= $key . " = '".$value."', ";
   	}

   	if(!$this->checkWhereClause(true))
   		return false;
   	$this->checkLimitSettings($limit);
   	return $this->finalQuery();
   }

   /**
    * A function to include a where clause to each statament
    *
    * @param string $name the name of the column
    * @param string $value the value to compare from the column
    * @param string $comparison the comparison operator to use
    * 
    * @return  a boolean indicating if the where clause is valid
    */
   public function where($name,$value = null,$comparison = " = ") 
   {
   	if(!is_array($name) && $value == null && !is_int($value)){
   		$this->whereClause = "n";
   		return $this->setErrorMessage(0,"Invalid where clause");
   	}
   	if($value == null && is_array($name))
   		$this->includeWhereArray($name);
   	else
   		$this->whereClause = " WHERE " .$name . $comparison . "'".$value."'"; 

   	return true;
   }

   /**
    * A function to include a where NOT clause to each statament
    *
    * @param string $name the name of the column
    * @param string $value the value to compare from the column
    * @param string $comparison the comparison operator to use
    * 
    * @return  a boolean indicating if the where clause is valid
    */
   public function whereNot($name,$value = null , $comparison = " = ") 
   {
   	$this->whereClause = null;
   	   	if(!is_array($name) && $value == null && !is_int($value))
   	   	{
   	   		$this->whereClause = "n";
   			return false;
   	   	}

   	if($value == null && is_array($name))
   		$this->includeWhereArray($name);
   	else
   		$this->whereClause = " WHERE NOT " .$name . $comparison . "'".$value."'"; 

   	return true;
   }

   /**
    * A function to include multiple where clauses to current query
    *
    * @param array $values the column name and values to compare to of the column
    * @param string $andor the comparison inclusion AND OR
    * @param string $comparison the comparison operator to use
    * 
    * @return  a boolean indicating if the where clause is valid
    */
   public function whereMultiple($values,$andor = "AND", $comparison = " = ")
   {
   	$this->whereClause = null;
   	if(!is_array($values) || $values == null){
   		   	$this->whereClause = "n";
   			return false;
   }else{
   		$this->whereClause = " WHERE "; 
   		foreach ($values as $key => $value) {
   			$this->whereClause .= $key . $comparison . "'".$value."' ";
   			    		if($key != end(array_keys($values)))
   			    			$this->whereClause .= " ".$andor." ";
   		}
   	}
   	return true;
   }

   /**
    * A function to include a custom where clause
    *
    * @param string $whereClause the where clause statement
    * 
    * @return  a boolean indicating if the where clause is valid
    */
   public function whereCustom($whereClause)
   {   	
   		if(is_string($whereClause)){
   			   	$this->whereClause = $whereClause;
   			   	return true;
   		}
   		return false;
   }

   /**
    * Sets having condition for final query
    *
    * @param string $condition 
    * @param string $value the value to be compared to
    * @param string $operator comparison operator
    * 
    */
   public function having($condition,$value,$operator = " = ")
   {
   	$this->having = " HAVING " . $condition . $operator . "'" . $value . "'";
   }

   /**
	Same as having function but for having count 
    */
   public function havingCount($condition,$value,$operator = " = ")
   {
   	$this->having = " HAVING COUNT(" . $condition . ")". $operator. $value;
   }   

   /**
    * Sets order by clause for query
    *
    * @param string $name 
    * @param string $method 
    * 
    */
   public function orderBy($name,$method = null)
   {
   	$this->orderByClause = " ORDER BY ";
   	if($name == "RAND ()" && $name != null){
   		$this->orderByClause .= " RAND ()";
   		return;
   	}
   	if($method == null && !is_array($name)){
   		$this->orderByClause.= $name;
   		return;
   	}else if ($method != null && is_array($name)){
   		foreach ($name as $key => $value) {
   			if($key == end(array_keys($name)))
   				$this->orderByClause .= $value;
   			else
   				$this->orderByClause .= $value . ", ";
   		}
   		return;
   	}else if (!is_array($name)){
   		$this->orderByClause .= $name . " " . strtoupper($method);
   		return;
   	}else{
   		foreach ($name as $key => $value) {
   			$option;
   			if(is_integer($key))
   				$option = "";
   			else
   				$option = $key;
   			if($key == end(array_keys($name)))
   				$this->orderByClause .= $value . " " . strtoupper($option). "";
   			else
   				$this->orderByClause .= $value . " " . strtoupper($option). ", ";
   		}
   		return;
   	}
   	
   }

   /**
    * Allows custom oder by clause
    *
    * @param string $orderByClause the full sql order by clause
    */
   public function orderByCustom($orderByClause)
   {
   		if(is_string($orderByClause))
   			   	$this->orderByClause = $orderByClause;
   }

   /**
    * Adds a group by clause to next query
    *
    * @param string $name 
    */
   public function groupBy($name){
   		$this->groupClause .= " GROUP BY ". $name;
   }

   /**
    * Allows custom sql group by clause
    *
    * @param string $groupClause 
    */
   public function groupByCustom($groupClause)
   {
   		if(is_string($groupClause))
   			   	$this->groupClause = $groupClause;
   }

   /**
    * Adds join command to next query
    *
    * @param string $tableName 
    * @param string $condition 
    * @param string $method the type of join to occur  
    */
   public function join($tableName,$condition,$method = "")
   {
   	$acceptable = array("","INNER","LEFT","RIGHT","FULL");
   	foreach ($acceptable as $key => $value) {
   		if(strtoupper($method) == $value){
   			$this->joinClause = " ".$method. " JOIN " . $tableName. " ON " . $condition;
   			return true;
   		}
   	}
   	$this->joinClause = "n";
   	return $this->setErrorMessage(0,"Invalid JOIN method");

   }

   /**
    * Allows custom sql join clause
    *
    * @param string $joinCommand 
    */
   public function joinCustom($joinCommand)
   {
   	$this->joinClause = $joinCommand;
   }

   /**
    * Allows count,avg,sum sql query
    *
    * @param string $columnName The name of the column
    * @param string $tableName 
    * @param string $option 
    * 
    * @return  the restult of count,avg,or sum
    */
   public function count($columnName,$tableName = null,$option = "COUNT")
   {
   if(!$this->checkLock(1))
		return "Database locked, cannot perform action";
   	$this->checkIfTableExists($tableName);
   	$this->currQuery = "SELECT ".$option."(".$columnName.") FROM " . $tableName;
	$this->checkWhereClause();
   	$final = $this->finalQuery();
   	if(!$final)
   		return false;
   	else {
   		if($option == "COUNT")
   			return intval($final->fetch_row()[0]);
   		else if ($option == "AVG")
   			return doubleval(mysqli_fetch_assoc($final)['AVG('.$columnName.')']);
   		else if($option == "SUM")
   			return intval(mysqli_fetch_assoc($final)['SUM('.$columnName.')']);
   		else 
   			return false;
   	}
   }

   /**
    * returns avg
    *
    * @param same as count function
    * 
    * @return  result from query
    */
   public function avg($columnName,$tableName = null)
   {
   	return $this->count($columnName,$tableName,"AVG");
   }

   /**
    * returns sum
    * @param same as count function
    * 
    * @return  result from query
    */
   public function sum($columnName,$tableName = null)
   {
   	return $this->count($columnName,$tableName,"SUM");
   }

   /**
    * Max sql query
    *
    * @param string $columnName
    * @param string $tableName The name of the database table to manipulate.
    * @param bool $inverse runs min instead of max if true
    * 
    * @return  returns results from query
    */
   public function max($columnName,$tableName = null,$inverse = false)
   {
 	if(!$this->checkLock(1))
		return "Database locked, cannot perform action";
   	$this->checkIfTableExists($tableName);
   	$str = "DESC";
   	if($inverse)
   		$str = "ASC";

   	$this->currQuery = "SELECT ".$columnName." FROM ".$tableName." ORDER BY " . $columnName . " ".$str." LIMIT 1";
   	$this->checkWhereClause();
   	$final = $this->finalQuery();
   	if(!$final)
   		return false;
   	else {
   		$res = mysqli_fetch_assoc($final);
   		foreach ($res as $key => $value) {
   			return $value;
   		}
   	}
   }

   /**
    * Same as max, can use max inverse = true instead
    */
   public function min($columnName,$tableName = null)
   {
   	return $this->max($columnName,$tableName,true);
   }

   /**
    * Copies a table into another
    *
    * @param string $tableName The name of the database table to copy
    * @param string $target the target table to recieve results
    * 
    */
   public function copyFullTable($source,$target){
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->checkEscapeString($source);
   	$this->checkEscapeString($target);
   	$this->currQuery .= "INSERT INTO " . $target . " SELECT * FROM ". $source;
   	if($this->finalQuery())
   		return ($source . " Successfully copied to " . $target);
   	else
   		return false;
   }

   /**
    * Copies a table into another
    *
    * @param string $tableName The name of the database table to copy
    * @param string $target the target table to recieve results
    * @param sVals the source values to copy
    * @param tVals the values to be set from sVals
    * 
    */
   public function copyTable($source,$target,$sVals,$tVals){
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
    $this->checkEscapeString($source);
    $this->checkEscapeString($target);
	$this->currQuery .= "INSERT INTO " . $target . " (";
	foreach ($sVals as $key => $value) {
		$this->checkEscapeString($value);
		if($key == end(array_keys($sVals)))
   				$this->currQuery .= $value;
   			else
   				$this->currQuery .= $value . ", ";
	}
	$this->currQuery.= ") SELECT ";
		foreach ($tVals as $key => $value) {
		$this->checkEscapeString($value);
		if($key == end(array_keys($tVals)))
   				$this->currQuery .= $value;
   			else
   				$this->currQuery .= $value . ", ";
	}
	$this->currQuery.= " FROM " . $target;
   	if($this->finalQuery())
   		return ($source . " Successfully copied to " . $target);
   	else
   		return false;
   }

   /**
    * Checks if a table exists with such name
    *
    * @param string $tableName The name of the database table
    * 
    * @return bool true if exists false if not
    */
   public function tableExists($tableName)
   {
   	if(!$this->checkLock(1))
		return "Database locked, cannot perform action";
   	$statement = "SELECT * FROM " . $tableName . " LIMIT 1";
   	if(!mysqli_query($this->dbConnection, $statement))
   		return false;
   	else 
   		return true;
   }

   /**
    * used to print results from a function call
    *
    * @param string $res The results of a function call to copy 
    * 
    */
   public function printResult($res)
   {
   	if(!is_array($res))
   		echo $res;
   	else
   		var_dump($res);
   }



  /**
   * A DELETE * function to clear a table of specified records
   *
   * @param string  $tableName The name of the database table to manipulate.
   * @param string  $determ the determinant for what column to effect.
   * @param   ?      $val Any value for which the determinant is compared to.
   * 
   */
   public function delete($tableName=null)
   {
   if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->checkIfTableExists($tableName);
   	$this->currQuery = "DELETE FROM " . $this->currentTable;
   	if(!$this->checkWhereClause())
   		return false;
   	
   	if(!$this->finalQuery())
   		return false;
   	else 
   		return true;

   }

  /**
   * A DELETE * function to clear a table of all records
   *
   * @param string  $tableName The name of the database table to manipulate.
   * 
   */
   public function deleteAll($tableName=null)
   {
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->checkIfTableExists($tableName);
   	$this->currQuery = "DELETE FROM " . $this->currentTable;
   	if(!$this->finalQuery())
   		return false;
   	else 
   		return true;
   }

   /**
    * creates a new table 
    *
    * @param string $tableName The name of the database table to create
    * @param array/string $values values to set for table columns
    * 
    */
   public function createTable($tableName,$values = null) 
   {
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->currQuery = "CREATE TABLE " . $tableName . " (";
   	if(!is_array($values))
   		return;

   	foreach ($values as $key => $value) {
   		if($key == end(array_keys($values)))
			$this->currQuery .= $key . " " . $value . ")";
   		else
   			$this->currQuery .= $key . " " . $value . ",";
   	}

   	if($this->finalQuery())
   		return "Table created";
   	else 
   		return false;
   }

   /**
    * Drops a specified table from database
    *
    * @param string $tableName The name of the database table to copy
    * 
    */
   public function dropTable($tableName) 
   {
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->currQuery = "DROP TABLE " . $tableName;
   	if($this->finalQuery())
   		return "Table deleted";
   	else 
   		return false;
   }

   /**
    * Adds a column to specified table
    *
    * @param string $tableName The name of the database table 
    * @param string $columnName The name of the column to add
    * @param string $desiredType The desired type of the column
    * 
    */
   public function addColumn($tableName,$columnName,$desiredType)
   {
   if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   $this->currQuery = "ALTER TABLE " . $tableName . " ADD " . $columnName . " " . $desiredType;
     	if($this->finalQuery())
   		return "Column " . $columnName . "Added of type " . $desiredType;
   	else 
   		return false;
   }

   /**
    * Modifies a column in specified table
    *
    * @param string $tableName The name of the database table 
    * @param string $columnName The name of the column to modify
    * @param string $desiredType The desired type of the column
    * 
    */
   public function modifyColumnType($tableName,$columnName,$desiredType)
   {
   if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   $this->currQuery = "ALTER TABLE " . $tableName . " MODIFY " . $columnName . " " . $desiredType;
   if($this->finalQuery())
   		return "Column modified";
   	else 
   		return false;
   }

   /**
    * Drops a table column
    *
    * @param string $tableName The name of the database table 
    * @param string $columnName The name of the column to drop
    * 
    */
   public function dropColumn($tableName,$columnName) 
   {
   	if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->currQuery = "ALTER TABLE " . $tableName . " DROP COLUMN " . $columnName;
   	if($this->finalQuery())
   		return "Dropped ".$columnName." from ".$tableName;
   	else 
   		return false;
   }

   /**
    * Truncates/clears a specified table of all data
    *
    * @param string $tableName The name of the database table 
    * 
    */
   public function truncateTable($tableName) 
   {
   if(!$this->checkLock(0))
		return "Database locked, cannot perform action";
   	$this->currQuery = "TRUNCATE TABLE " . $tableName;
   	if($this->finalQuery())
   		return "Table truncated";
   	else 
   		return false;
   }

   /**
    * Backs up all table values to a file
    *
    * @param string $tableName The name of the database table 
    * @param string $outputFileName The name of the file to create or overwrite
    * 
    */
   public function backupTable($tableName,$outputFileName)
   {
   	$file = fopen($outputFileName, 'w') or die('Cannot open file:  '.$outputFileName);
	fwrite($file, json_encode($this->getAll($tableName)));
   }

   /**
    * Prints log to specified file
    *
    * @param string $filename The name of the file to create or overwrite 
    * 
    */
   public function logToFile($filename = "log.txt")
   {
   	$file = fopen($filename, 'w') or die('Cannot open file:  '.$filename);
	fwrite($file, json_encode($this->getLog()));
   }

  /**
   * Checks if the table name being queried is not null
   *
   * @param string  $tableName The name of the table being manipulated in current query
   * 
   */
   private function checkIfTableExists(&$tableName)
   {
   	if($tableName == null)
   		$tableName = $this->currentTable;
   	else
   		$this->currentTable = $tableName;

   	if($tableName == null){
   		trigger_error("Error: Table name cannot be null");
   	return;
   	}
   }

  /**
   * This method checks and sets the current limit settings on a query
   *
   * @param int  $limit The limit to be set or added to the pending query
   * 
   */
   private function checkLimitSettings($limit = 0)
   {
   	if($this->autoLimit && $limit <= 0 && $this->limit != 0){
   		$this->currQuery .= " LIMIT " . $this->limit;
   	}else if($limit > 0){
   		$this->limit = $limit;
   		$this->currQuery .= " LIMIT " . $this->limit;
   	}
   	return;
   }

   private function checkWhereClause($optional = false)
   {
   	if($optional && $this->whereClause == null)
		  return true;
   	if($optional && $this->whereClause == "n")
   		return $this->setErrorMessage(0,"Invalid WHERE clause");

   	if (!isset($this->whereClause) || $this->whereClause == null) 
   			return false;
   	else
   		$this->currQuery .= $this->whereClause;

   	return true;
   }


   private function checkHaving($optional = false)
   {
   	if($optional && $this->having == null)
		  return true;
   	if($optional && $this->having == "n")
   		return $this->setErrorMessage(0,"Invalid HAVING clause");

   	if (!isset($this->having) || $this->having == null) 
   			return false;
   	else
   		$this->currQuery .= $this->having;

   	return true;
   }

   private function checkOrderBy($optional = false){
   	if($optional && $this->orderByClause == null)
		  return true;
   	if($optional && $this->orderByClause == "n")
   		return !$this->setErrorMessage(0,"Invalid ORDER BY clause");


   	if (!isset($this->orderByClause) || $this->orderByClause == null) 
   		return false;
   	else
   		$this->currQuery .= $this->orderByClause;

   	return true;
   }

   private function checkJoinClause($optional = false)
   {
   	if($optional && $this->joinClause == null)
      return true;
   	if($optional && $this->joinClause == "n")
      return !$this->setErrorMessage(0,"Invalid JOIN clause");

   	if (!isset($this->joinClause) || $this->joinClause == null) 
   			return false;
   	else
   		$this->currQuery .= $this->joinClause;

   	return true;
   }

   private function checkGroupBy($optional = false){
   	if($optional && $this->groupClause == null)
		return true;
   	if($optional && $this->groupClause == "n")
   		return $this->setErrorMessage(0,"Invalid GROUP BY clause");

   	if (!isset($this->groupClause) || $this->groupClause == null) 
   		return false;
   	else
   		$this->currQuery .= $this->groupClause;

   	return true;
   }

   private function getSelectResults($result)
   {
 	$finalarr = array();
 	if(!$result)
 		return false;
   	if (mysqli_num_rows($result) > 0) {
    	while($row = mysqli_fetch_assoc($result)) {
    		array_push($finalarr, $row);
    	}
	}
	return $finalarr;
   }


  /**
   * This private method includes a where clause into the current query 
   *
   * @param array  $whereClause An array containing the value being compared
   * 
   */
   private function includeWhereArray($whereClause)
   {
   	$determ; $val;
   	foreach ($whereClause as $key => $value) {
   		$determ = $key; $val = $value;
   		break;
   	}
   	$this->whereClause.= " WHERE " . $determ ." = '" .$val ."'"; 
   }

	private function checkEscapeString(&$var)
	{
		if($this->enableEscapeStrings)
				$var = str_replace($this->extraEscapeStrings, '', $var);
		
		if ($this->connected) 
			if($this->enableEscapeStrings)
				$var = mysqli_real_escape_string($this->dbConnection,$var);
	}

	private function checkLock($required)
	{
		if($this->lock == 2)
			return false;
		if($this->lock > $required)
			return false;
		else 
			return true;
	}
	private function showLockSetting()
	{
		if($this->lock == 0)
			return "Unlocked";
		else if($this->lock == 1)
			return "Read Only";
		else
			return "Locked";
	}

	private function resetQueryParams()
	{
		$this->groupClause = null;
		$this->orderByClause = null;
		$this->having = null;
		$this->joinClause = null;
		$this->whereClause = null;
		$this->subQuery = null;
	}

	private function setErrorMessage($errno,$errmsg)
	{
		$this->lastErrorNo = "Simple SQL Error(" .$errno.")";
		$this->lastError = $errmsg;
		return false;
	}
  /**
   * This method offers the option to directly run an SQL statement from a string
   *
   * @param string  $sqlStatement The functional SQL statement to be run.
   * 
   */
   public function queryStatement($sqlStatament)
   {
   	$this->currQuery = $sqlStatament;
   	return $this->finalQuery();
   }

   protected function finalQuery($subQuery = false)
   {

   	if(!$this->connected)
   		return $this->setErrorMessage(0,"Not connected to database");

   	if($this->subQuery)
   		return false;

   	if($this->currQuery == null)
   		return false;

   	if($this->subQuery != null){
   		$this->currQuery .= "(".$this->subQuery.")";
   		$this->subQuery = null;
   	}


   	$this->resetQueryParams();

	if($this->lock == 2)
			return false;
   	if($this->autoReConnect)
   		if(!mysql_ping($this->connection))
   			$this->connect();

	if($this->returnAsJson)
		echo json_encode($this->currQuery);

	   //	echo $this->currQuery;

   	$query = mysqli_query($this->dbConnection, $this->currQuery);

   	if (!$query) {
  	if($this->logQueries){
  		   		if(count($this->log) >= $this->logLimit)
  		   				array_pop($this->log);
   		array_push($this->log, ($this->currQuery . " : Error(".mysqli_errno($this->dbConnection).")"));
  	}

  	$this->lastError = mysqli_error($this->dbConnection);
  	$this->lastErrorNo = "Error(". mysqli_errno($this->dbConnection).")";
 
   	}
  	if($this->logQueries){
  		if(count($this->log) >= $this->logLimit)
  		   				array_pop($this->log);
   		array_push($this->log, ($this->currQuery . " : Success"));
  	}
   	$this->lastQuery = $this->currQuery;
   	unset($this->currQuery);
   	return $query;
   }

  /**
   * Class destructor that automatically disconnects any current database connections
   */
   function __destruct() 
   {
     $this->disconnect();
   }





}

// END OF CLASS

?>