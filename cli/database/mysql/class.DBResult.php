<?php
/**
 * Define base functionality for database result sets
 *
 * Defines the base DBResult class and DBResultException
 *
 * @package DooFramework
 * @subpackage database
 */

/**
 * DBResult base class
 * @package DooFramework
 * @subpackage database
 */
class DBResult implements ArrayAccess, Iterator, Countable {
	/**
	 * @access protected
	 * @var integer index of the active row
	 */
	protected $current_index = NULL;
	/**
	 * @access protected
	 * @var database resource to be used to retrieve the results
	 */
	protected $result_handle = NULL;
	/**
	 * @access protected
	 * @var boolean determines whether or not result rows are returned as associative arrays or StdClass objects
	 */
	protected $return_objects = NULL;
	/**
	 * @access protected
	 * @var string contains the name of the database fetch function used in retrieving result rows
	 */
	protected $fetch_method = NULL;
	/**
	 * @access protected
	 * @var DataStore stores the columns and their information
	 */
	protected $columns_info = NULL;
	/**
	 * @access protected
	 * @var mysqli contains the database connection object
	 */
	protected $conn = NULL;

	/**
	 * Contructor for DBResult class
	 *
	 * @access public
	 * @param resource $result_handle A query result resource
	 * @param boolean $return_objects If true, this will force each result row to be returned as an object.
	 * this defaults to false.
	 * @return void
	 */
	public function __construct($connection_handle,$result_handle,$return_objects=false) { 
		if (!$result_handle) { throw new DBResultException("Missing query results handle"); }
		$this->current_index = 0;
		$this->result_handle = $result_handle;
		$this->return_objects = $return_objects;
		$this->conn = $connection_handle;

		if ($this->return_objects) {
			$this->fetch_method = "mysqli_fetch_object";
		}
		else {
			$this->fetch_method = "mysqli_fetch_assoc";
		}

		// create column datastore
		$this->column_info = new DataStore();

		// grab resulting column info
		if (!is_bool($this->result_handle)) {
			$field_info = mysqli_fetch_fields($this->result_handle);
		}
		else {
			$field_info = array();
		}
	
		// set column data into datastore	
		foreach($field_info as $info) {
			$this->column_info->set($info->name,$info);
		}			
	}

	/**
	 * Returns the original result resource handle 
	 *
	 * @access public
	 * @return resource
	 */
	public function getResource() {
		return $this->result_handle;
	}

	/**
	 * Returns the number of columns in the result set
	 *
	 * @access public
	 * @return integer
	 */
	public function getNumColumns() {
		return count($this->column_info);
	}

	/**
	 * Returns a data structure containing information about the specified column name
	 *
	 * @access public
	 * @param string $column_name The name of the column 
	 * @return array
	 */
	public function columnInfoByName($column_name) {
		return $this->column_info->get($column_name);
	}

	public function getInsertId() {
		return mysqli_insert_id($this->conn);
	}

	/** =====================================================
			ArrayAccess Interface Methods
	 ** =====================================================*/

	/**
	 * Returns the entire result set as an associative array
	 *
	 * @access public
	 * @return array
	 */
	public function asArray() {
    $result_array = array();
    while ($row = mysqli_fetch_array($this->result_handle,MYSQLI_ASSOC)) {
      if ($this->return_objects) {
        $result_array[] = (object) $row;
      }
      else {
        $result_array[] = $row;
      }
    }
		return $result_array;
	}

	/**
	 * Returns the entire result set as instances of the given class name
	 *
	 * @access public
   * @param string $class_name The name of the class
	 * @return array
	 */
	public function asObjects($class_name) {
    $object_array = array();
    while ($row = mysqli_fetch_array($this->result_handle,MYSQLI_ASSOC)) {
      $object_array[] = @new $class_name ($row);
    }
		return $object_array;
	}

	/**
	 * This method is used to tell php if there is a value for the key specified by the offset.
	 *
	 * @access public
	 * @param integer $offset The result set row number
	 * @return boolean
	 */
	public function offsetExists($offset) {
		if ( (!is_numeric($offset)) ) {	throw new DBResultException("Missing or invalid offset argument in DBResult::offsetExists()"); }
		$retval = false;
		$fetch_method = $this->fetch_method;
		if (mysqli_data_seek($this->result_handle,$offset)) {
		//if ($offset < $this->count()) {
		//	if ($fetch_method($this->result_handle)) {
				$retval = true;
		//		}
		}
		return $retval;
	}

	/**
	 * Returns the value specified by the key offset
	 *
	 * @access public
	 * @param integer $offset The result set row number
	 * @return array
	 */
	public function offsetGet($offset) {
		if ( (!is_numeric($offset)) ) {	throw new DBResultException("Missing or invalid offset argument in DBResult::offsetGet()"); }
		$retval = null;
		$fetch_method = $this->fetch_method;
		mysqli_data_seek($this->result_handle,$offset);
		if ($row = $fetch_method($this->result_handle)) {
			$retval = $row;
		}
		else {
			throw new DBResultException("No result row at " . $offset . " in DBResult::offsetGet()");
		}
		return $retval;
	}

	/**
	 * Not implemented
	 *
	 * @access public
	 * @param integer $offset The result set row number
	 * @param mixed $value The value to set at the specified row number
	 * @return void
	 */
	public function offsetSet($offset,$value) {
		throw new DBResultException("This collection is read only");
	}
	
	/**
	 * Not implemented
	 *
	 * @access public
	 * @param integer $offset The result set row number
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new DBResultException("This collection is read only");
	}

	/** =====================================================
			Countable Interface Methods
	 ** =====================================================*/

	/**
	 * Returns the number of rows returned in the result
	 *
	 * Example: count(new DBResult($result_handle)); and $DBResult->count(); will return the same thing
	 *
	 * @access public
	 * @return integer
	 */
	public function count() {
		return mysqli_num_rows($this->result_handle);
	}


	/**
	 * Returns the number of rows affected by the statement
	 *
	 * Example: $DBResult->affected(); will return the same thing
	 *
	 * @access public
	 * @param mysqli $dbh The database connection link
	 * @return integer
	 */
	public function affected($dbh=null) {
		return mysqli_affected_rows($this->conn);
	}

	/** =====================================================
			Iterator Interface Methods
	 ** =====================================================*/

	/**
	 * Returns the value of the current index's key.
	 *
	 * @access public
	 * @return integer
	 */
	public function key() {
		return $this->current_index;	
	} 
	
	/**
	 * Returns the value of the current index
	 *
	 * @access public
	 * @return array
	 */
	public function current() {
		return $this->offsetGet($this->current_index);
	}

	/**
	 * Moves the internal index forward one entry
	 *
	 * @access public
	 * @return integer
	 */
	public function next() {
		return $this->current_index++;
	}

	/**
	 * Resets the internal index to the first element
	 *
	 * @access public
	 * @return void
	 */
	public function rewind() {
		$this->current_index = 0;
	}

	/**
	 * Return true or false if there is a current element. It is called after rewind() or next().
	 *
	 * @access public
	 * @return boolean
	 */
	public function valid() {
		$retval = false;
		if ($this->offsetExists($this->current_index)) {
			$retval = true;
		}
		return $retval;
	}

	/**
	 * Not implemented
	 *
	 * @access public
	 * @return void
	 */
	public function append($value) {
		throw new DBResultException("This collection is read only");
	}

	/**
	 * Returns the current iterator instance
	 *
	 * @access public
	 * @return DBResult
	 */
	public function getIterator() {
		return $this;
	}
}

/**
 * DBResult Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class DBResultException extends Exception {
	public function __construct($msg,$code=0) {
		parent::__construct($msg,$code);
	}
}
?>
