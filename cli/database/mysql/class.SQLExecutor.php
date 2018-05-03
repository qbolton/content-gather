<?php
/**
 * Define base database functionality for adhoc sql statements
 *
 * Defines the base SQLExecutor class and SQLExecutorException
 *
 * @package DooFramework
 * @subpackage database
 */

/**
 * SQLExecutor base class
 * @package DooFramework
 * @subpackage database
 */
class SQLExecutor {
	/**
	 * @access protected
	 * @var string String that contains the sql "command"
	 */
	protected $command = NULL;

	/**
	 * @access protected
	 * @var DBConnect Contains the database connection handle
	 */
	protected $conn = NULL;

	/**
	 * @access protected
	 * @var resource Contains the handle with which to access result sets and SQL execution information
	 */
	protected $result = NULL;

	/**
	 * @access protected
	 * @var DataStore Contains the list of labels associated with statements prepared with an instance of SQLExecutor
	 */
	protected $labels = NULL;

	/**
	 * Contructor for SQLExecutor class
	 *
	  @access public
	 * @param DBConnection $connection Valid instance of the DBConnection class.  This parameter is optional
	 * @return void
	 */
	public function __construct($connection=null) { 
		$this->conn = $connection;

		$this->labels = new DataStore();

		// Get current class name
		$this->class = get_class($this);
	}

	/**
	 * Sets the sql to be executed.  This method can also handle preparing statements for later execution
	 *
	 * @access public
	 * @param string $sql The SQL statement to execute
	 * @param string $label Label used to identify a prepared statement [optional] 
	 * @param boolean $prepare If true, the sql statement will be prepared using pg_prepare provided a string value
   *                         was provided for the $label argument.  Defaults to false.
	 * @return SQLExecutor
	 */
	public function sql($sql=null,$label=null,$prepare=false) { 
		if (!$sql) { throw new SQLExecutorException("Missing SQL string argument in {$this->class}::sql()"); }
		if (!is_string($sql)) {  throw new SQLExecutorException("Invalid argument type passed into {$this->class}::sql()"); }
		if ( ($prepare) && (!is_string($label)) ) { throw new SQLExecutorException("You must pass a valid $label argument in order to have a statement prepared"); }
		// trim the sql statement of extraneous spaces
		$this->command = trim($sql);

		// prepare the sql string to be processed
		if ( (is_string($label)) && ($prepare) ) {
			$stmt = mysqli_prepare($this->conn->handle,$this->command);
			$this->labels->set( strtolower(trim($label)), $stmt );
		}
			
		return $this; 
	}

  public function export() {
    return $this->command;
  }

	/**
	 * Executes the assembled SQL statement
	 *
	 * @param string $label Label used to identify a prepared statement [optional] 
	 * @param array $params Array of parameter values to be substituted for the $1, $2, etc. placeholders in the original
	 *                      prepared query string.  The number of elements in the array must match the number of 
	 *                      placeholders.
	 * @param boolean $return_objects  If true, this will force each result row to be returned as an object. Defaults to false.
	 * @access public
	 * @return DBResult
	 */ 
	public function run($return_objects=false,$label=null,$params=null) { 
		if (!$label) {
			// Execute the sql
			$this->result = mysqli_query($this->conn->handle,$this->command);
		}
		else {
			$clean_label = strtolower(trim($label));
			if ($this->labels->exists($clean_label)) {
				$this->result = mysqli_stmt_execute($this->labels->get($clean_label));	
			}
			else {
				throw new SQLExecutorException("The statement label supplied " . $label . " does not exist");
			}
		}

		if ($this->result == false) {
			throw new SQLExecutorException( mysqli_error($this->conn->handle) );
		}

		// Eventually this will return an instance of DBResult
		return ( new DBResult($this->conn->handle,$this->result,$return_objects) );
	}

	/**
	 * Executes the assembled multiple SQL statements
	 *
	 * @param boolean $return_objects  If true, this will force each result row to be returned as an object. Defaults to false.
	 * @access public
	 * @return DBResult
	 */ 
	public function run_multi($return_objects=false) { 
	  // Execute the sql
		$this->result = mysqli_multi_query($this->conn->handle,$this->command);

		if ($this->result == false) {
			throw new SQLExecutorException( mysqli_error($this->conn->handle) );
		}

		// Eventually this will return an instance of DBResult
		return ( new DBResult($this->conn->handle,$this->result,$return_objects) );
  }

	public function escape_string($str) {
		if ($str !== null) {
			$str = str_replace(array('\\','\''),array('\\\\','\\\''),$str);
			$str = "'".$str."'";
		} else {
			$str = "null";
		}
		return $str;
	}
}

/**
 * SQLExecutor Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class SQLExecutorException extends Exception {
	public function __construct($msg,$code=0) {
		parent::__construct($msg,$code);
	}
}
?>
