<?php 

class Database {
	
	public $connect = null;
	public $result;
    public static $allquery;
	
	/**
	 * Database instances container.
	 *
	 * @var    Database
	 */
	protected static $instance;
	
	function __construct($config = null)
	{
		$this->setConnection($config);
	}
	
	public function setConnection($config = null)
	{
		if(!$this->connect){
			
			$cfg = App::getConfig();
			$myServer = $cfg->dbserver;
			$myUser = $cfg->dbuser;
			$myPass = $cfg->dbpass;
			$myDB = $cfg->dbname;
			$port = $cfg->dbport;
			
			if(isset($config) && is_array($config))
			{
				if(isset($config["host"]))
				{
					$myServer = $config["host"];
				}
				
				if(isset($config["user"]))
				{
					$myUser = $config["user"];
				}
				
				if(isset($config["password"]))
				{
					$myPass = $config["password"];
				}

				if(isset($config["db"]))
				{
					$myDB = $config["db"];
				}

				$port = null;
				if(isset($config["port"]))
				{
					$port = $config["port"];
				}
			}
			
			//connection to the database
			$this->connect = new mysqli($myServer, $myUser, $myPass, $myDB, $port);

			/* check connection */
			if (mysqli_connect_errno()) {
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
		}
	}
	
	public static function getInstance()
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new Database();
		}

		return self::$instance;
	}
	
	public function setQuery($query)
	{
		$this->setConnection();
        self::$allquery[] = $query;
		$this->result = $this->connect->query($query);
	}
	
	public function getNumRows()
	{
		$hasil = 0;
		if($this->result)
		{
			$hasil = $this->result->num_rows;
		}
		return $hasil;
	}
	
	public function loadObjectList()
	{
		$hasil = array();
		if($this->result){
			while($a = $this->result->fetch_object()) {
				$hasil[] = $a;
			}
		}
		return $hasil;
	}
	
	public function loadObject()
	{
		$hasil = null;
		if($this->result){
			while($a = $this->result->fetch_object()) {
				$hasil = $a;
			}
		}
		return $hasil;
	}		
	
	public function quote($string)
	{
		if($string === null){return $string;}
		$this->setConnection();
		$string = "'".$this->connect->real_escape_string($string)."'";
		return $string;
	}
	
	public function like($string)
	{
		$this->setConnection();
		$string = "'%".$this->connect->real_escape_string($string)."%'";
		return $string;
	}	

    public function insertObject($table, &$object, $keyName = NULL, $lastid = false)
    {
        $fmtsql = 'INSERT INTO '.$this->nameQuote($table).' ( %s ) VALUES ( %s ) ';
        $fields = array();
        foreach (get_object_vars( $object ) as $k => $v) {
            if (is_array($v) or is_object($v) or $v === NULL) {
                continue;
            }
            if ($k[0] == '_') {
                continue;
            }
            $fields[] = $this->nameQuote( $k );
            $values[] = $this->quote($v);
        }
        $this->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
        if (!$this->result) {
            return false;
        }
        $id = $this->connect->insert_id;
        if ($keyName && $id) {
            $object->$keyName = $id;
        }
		
		if($lastid){
			$res[] = true;
			$res[] = $id;
			return $res;
		}else{
			return true;
		}
    }
	
	public function updateObject($table, &$object, $key, $nulls = false)
	{
		
		$fields = array();
		$where = array();

		if (is_string($key))
		{
			$key = array($key);
		}

		if (is_object($key))
		{
			$key = (array) $key;
		}

		// Create the base update statement.
		$statement = 'UPDATE ' . $this->nameQuote($table) . ' SET %s WHERE %s';
		
		// Iterate over the object variables to build the query fields/value pairs.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process scalars that are not internal fields.
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if (in_array($k, $key))
			{
				$where[] = $this->nameQuote($k) . '=' . $this->quote($v);
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($nulls)
				{
					$val = 'NULL';
				}
				// If the value is null and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			// The field is not null so we prep it for update.
			else
			{
				$val = $this->quote($v);
			}

			// Add the field to be updated.
			$fields[] = $this->nameQuote($k) . '=' . $val;
		}

		// We don't have any fields to update.
		if (empty($fields))
		{
			return true;
		}
		
		// Set the query and execute the update.
		$this->setQuery(sprintf($statement, implode(",", $fields), implode(' AND ', $where)));

		return $this->result;
	}	

    public function getAffectedRows()
    {
        return $this->connect->affected_rows;
    }

	public function nameQuote($string)
    {
        $string = '`'.$string.'`';
        return $string;
    }
	
	public function getResult()
	{
		if(count($this->connect->error_list) > 0){
			return $this->connect->error_list;
		}else{
			return $this->result;
		}
	}
}
?>