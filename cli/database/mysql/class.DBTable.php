<?php
/**
 * Define base database functionality for getloaded data tables 
 *
 * Defines the base DBTable class and DBTableException
 *
 * @package DooFramework
 * @subpackage database
 */

/**
 * DBTable base class
 * @package DooFramework
 * @subpackage database
 */
class DBTable {
	/**
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = NULL;

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = NULL;

	/** 
	 * @access protected
	 * @var DataStore Object that contains the table column information
	 */
	protected $columns = NULL;

	/**
	 * @access protected
	 * @var DataStore Object that contains the "command" (select, insert, delete) along with column
	 * names portion of the query
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
	 * @var boolean Boolean value determining whether or not 'NULL' is used in update or insert statements
	 */
	protected $allowNullAsValue = FALSE;

	/* ===============================================
	 *  TABLE INFORMATION METHODS
	 * =============================================== 
	 */

	/**
	 * Adds table column information to the internal columns DataStore object
	 *
	 * @access protected
	 * @param array $column_array An array of table column information.
	 * @return void

	/**
	 * Contructor for DBTable class
	 *
	 * @access public
	 * @param DBConnection $connection Valid instance of the DBConnection class.  This parameter is optional
	 * @return void
	 */
	public function __construct($connection=null,$allowNullAsValue=FALSE) { 
		if (!$this->TABLENAME) { throw new DBTableException("Instance not bound to a table name"); }

		$this->conn = $connection;

		if ($this->COLUMNINFO) {
			$this->addColumns($this->COLUMNINFO);
		}

		// Get current class name
		$this->class = get_class($this);

    // create command datastore
    $this->command = new DataStore();

    $this->nullAsValue($allowNullAsValue);
	}

	/* ===============================================
	 *  TABLE INFORMATION METHODS
	 * =============================================== 
	 */

	/**
	 * Adds table column information to the internal columns DataStore object
	 *
	 * @access protected
	 * @param array $column_array An array of table column information.
	 * @return void
	 */
	protected function addColumns($column_array) {
		$this->columns = new DataStore();
		// loop over array
		foreach($column_array as $column) {
			$this->columns->set(strtolower($column['name']),$column);
		}
	}

  /** 
   * Returns the value of the PRIMARYKEY property
   *
   * @access public
   * @return null|string Returns either the value of the PRIMARYKEY property or null if no PRIMARYKEY
   */
  public function getPrimaryKey() {
    return $this->PRIMARYKEY;
  }

	/**
	 * Returns an array of column information for the requested table column
	 *
	 * @access public
	 * @param string $name A column name
	 * @return array
	 */
	public function getColumnInfo($name=null) {
		$retval = NULL;
		if (!$name) {
			$retval = $this->columns->get();
		}
		else {
			if ($this->columns->exists( strtolower($name) )) {
				$retval = $this->columns->get( strtolower($name) );
			}
		}
		return $retval;
	}

	/* ===============================================
	 *  DATA TABLE SQL BUILDER METHODS
	 * =============================================== 
	 */

	/**
	 * Builds the 'select' command portion of the query
	 *
	 * @access public
	 * @param array $cols An array of column names.  This can be subscripted array or an associative array.
	 * @param bool $assoc If true, then this method will assume $cols is an associative array and will use the array keys
	 *                    as aliases for the array values in the SQL statement.
	 * @return DBTable
	 */
	public function select($cols=array(), $assoc=false) { 
		if (!is_array($cols)) {  throw new DBTableException("Invalid 'columns' argument passed into {$this->class}::select()"); }
		if (!is_bool($assoc)) {  throw new DBTableException("Invalid 'assoc' argument passed into {$this->class}::select()"); }

		// hold string list of columns
		$column_list = "";

		// if no items were passed into the array then default to '*'
		if (count($cols) == 0) {
			$column_list = '*';
		}
		else {
			// loop over columns array
			foreach ($cols as $key => $value) {
				if ($assoc) { 
					$column_list .= "$key AS '".$value."',";
				}
				else {
					$column_list .= "$value,";
				}
			}
			// remove any trailing comma 
			$column_list = rtrim($column_list,","); 
		} 

    // set the select command
    $this->command->set(
      'SELECT',
      "SELECT $column_list FROM `" . $this->TABLENAME . "`"
    );

		return $this; 
	}

	/**
	 * Builds the 'insert' command portion of the statement
	 *
	 * This method assumes that data escaping and filtering has already been handled by the
	 * calling process.
	 *
	 * @access public
	 * @param array $cols_and_values An associative array using column names as keys for the values
	 * @return DBTable
	 */ 
	public function insert($cols_and_values=NULL,$use_now=false) { 
		if (!$cols_and_values) { throw new DBTableException("Missing argument in call to {$this->class}::insert()"); }
		if (!is_array($cols_and_values)) { throw new DBTableException("Invalid argument in call to {$this->class}::insert()"); }

		// Create datastore object for storing sql commands segments
		$this->command = new DataStore();	
		// hold string list of columns
		$column_list = "";
		// hold string list of values
		$value_list = "";
		// prepare data for use in SQL
    $columns = $this->getColumnInfo(); 
	
    // loop over input
    foreach ($cols_and_values as $col => $value) {
      $col = strtolower($col);
      // check to see if column exists
      if (array_key_exists($col,$columns)) {
        $column_list .= $col . ",";
        // check the type
        if ( (strcasecmp($columns[$col]['data_type'],"varchar") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"char") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"enum") == 0) ||
						 (strcasecmp($columns[$col]['data_type'],"blob") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"longtext") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"tinytext") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"text") == 0)) { 
          $value_list .= "'" . mysqli_real_escape_string($this->conn->handle,$value) . "',";
        }
        else if ( (strcasecmp($columns[$col]['data_type'],"datetime") == 0) ||
                  (strcasecmp($columns[$col]['data_type'],"timestamp") == 0) || 
                  (strcasecmp($columns[$col]['data_type'],"date") == 0)) { 
          if ($use_now) {
            $value_list .= mysqli_real_escape_string($this->conn->handle,$value) . ",";
          }     
          else {
            $value_list .= "'" . mysqli_real_escape_string($this->conn->handle,$value) . "',";
          }     
        }     
        else {
          $value_list .= $value . ",";
        }     
      }     
    }

		// remove any trailing commas
		$column_list = rtrim($column_list,",");
		$value_list = rtrim($value_list,",");

		$this->command->set('INSERT',"INSERT INTO `" . $this->TABLENAME . "` ($column_list) VALUES ($value_list) ");

		// Debug statement using pg_insert with DML_STRING flag
		//print pg_insert($this->conn->handle,$this->TABLENAME,$cols_and_values,PGSQL_DML_STRING);

		return $this; 
	}

	/**
	 * Builds the 'update' command portion of the statement
	 *
	 * @access public
	 * @param array $cols_and_values An associative array using column names as keys for the values
	 * @return DBTable
	 */ 
	public function update($cols_and_values=NULL,$use_now=false) { 
		if (!$cols_and_values) { throw new DBTableException("Missing argument in call to {$this->class}::update()"); }
		if (!is_array($cols_and_values)) { throw new DBTableException("Invalid argument in call to {$this->class}::update()"); }

		$update_list = "";
    $columns = $this->getColumnInfo(); 

    // loop over input
    foreach ($cols_and_values as $col => $value) {
      $col = strtolower($col);
      // check to see if column exists
      if (array_key_exists($col,$columns)) {
        // check if this connection should use NULLs
        if ( ($this->nullValue($value)) && ($this->allowNullAsValue == TRUE) && (strcasecmp($columns[$col]['is_nullable'],"YES") == 0) ) {
          $update_list .= $col . "=NULL,";
        }
        else if ( ($this->nullValue($value)) && ($this->allowNullAsValue == TRUE) && (strcasecmp($columns[$col]['is_nullable'],"NO") == 0) ) {
          // do not include column in update list
          continue;
        }
        // check the type
        else if ( (strcasecmp($columns[$col]['data_type'],"varchar") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"char") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"enum") == 0) ||
             (strcasecmp($columns[$col]['data_type'],"text") == 0)) {
          $update_list .= $col . "='" . mysqli_real_escape_string($this->conn->handle,$value) . "',";
        }
        else if ( (strcasecmp($columns[$col]['data_type'],"datetime") == 0) ||
                  (strcasecmp($columns[$col]['data_type'],"timestamp") == 0) || 
                  (strcasecmp($columns[$col]['data_type'],"date") == 0)) { 
          if ($use_now) {
            $update_list .= $col . "=" . mysqli_real_escape_string($this->conn->handle,$value) . ",";
          }     
          else {
            $update_list .= $col . "='" . mysqli_real_escape_string($this->conn->handle,$value) . "',";
          }     
        }     
        else {
          $update_list .= $col . "=" . $value . ",";
        }     
      }     
    }

		// remove any trailing comma
		$update_list = rtrim($update_list,",");

		$this->command = new DataStore( array('UPDATE' => "UPDATE `" . $this->TABLENAME . "` SET $update_list") );

		return $this; 
	}

	/**
	 * Builds the 'delete' command portion of the statement
	 *
	 * @access public
	 * @return DBTable
	 */ 
	public function delete() { 
		$this->command = new DataStore( array('DELETE' => "DELETE FROM `" . $this->TABLENAME . "`") );	
		return $this; 
	}

	/**
	 * Assembles the 'where' portion of the SQL statement
	 *
	 * @access public
	 * @param string $cond The conditional expression that defines the clause
	 * @param string $conj The conjunction that should precede the passed condition
	 * @return DBTable
	 */ 
	public function where($cond,$conj=NULL) { 
		if (!is_string($cond)) { throw new DBTableException("Invalid conditional expression argument in {$this->class}::where"); }

		$where_clause = "WHERE";
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}

		// if there is no conjunction
		if (!$conj) { 
			$this->command->set('WHERE',trim($where_clause) . " " . trim($cond));
		}
		else {
			$this->command->set('WHERE',trim($where_clause) . " " . trim(strtoupper($conj)) . " ". trim($cond));
		}

		return $this; 
	}

	/**
	 * Assembles the join clause(s) of the SQL statement
	 *
	 * @access public
	 * @param DBTable $dt DBTable object of the table to join with.
	 * @param string $type The type of join.  This defaults to "INNER"
	 * @return DBTable
	 */
	public function join($dt,$type=null) { 
		if (! ($dt instanceof DBTable) ) { throw new DBTableException("Invalid DBTable argument in {$this->class}::join"); }
		if (!($dt->command->get('JOIN_COND'))) { throw new DBTableException("Missing conditional expression argument in {$this->class}::join"); }
		if (!$type || !is_string($type)) {
			 throw new DBTableException("Invalid join type argument in {$this->class}::join");
		}
		else {
			$type = strtoupper(trim($type));
			if (! in_array($type,array('LEFT','RIGHT','OUTER','INNER','LEFT OUTER','RIGHT OUTER'),TRUE)) {	
				throw new DBTableException("Unrecognized join type argument in {$this->class}::join");
			}	
		}

		$join_new = $type.'JOIN `'.$dt->TABLENAME.'` ON '.$dt->command->get('JOIN_COND');

		if ($this->command->exists( 'JOIN' )) {
			$join_clause = $this->command->get('JOIN');
		}
			
		$this->command->set('JOIN',trim($join_clause) . "\n" . trim($join_new));

		return $this; 
	}

	/**
	 * Supplies a join conditional statement
	 *
	 * @access public
	 * @param string $cond The join condition
	 * @return DBTable
	 */ 
	public function joinCond($cond) {
		if (!is_string($cond)) { throw new DBTableException("Invalid conditional expression argument in {$this->class}::joinCond"); }
		$this->command = new DataStore();	
		$this->command->set('JOIN_COND',trim($cond));
		return $this;
	}

	/**
	 * Applies a list of columns to the Group By clause
	 *
	 * @access public
	 * @param array $cols An array of column names
	 * @return DBTable
	 */ 
	public function groupBy($cols) { 
		if (!$cols) { throw new DBTableException("Missing 'cols' in {$this->class}::groupBy()"); }
		if (!is_array($cols)) { throw new DBTableException("Invalid 'cols' argument passed into {$this->class}::groupBy()"); }
		$group_by_list = "";
		foreach($cols as $column_name) {
			$group_by_list .= $column_name . ",";
		}

		// remove any trailing comma
		$group_by_list = rtrim($group_by_list,",");
		$this->command->set('GROUPBY',"GROUP BY " . trim($group_by_list));

		return $this; 
	}

	/**
	 * Applies a list of columns to the Order By clause
	 *
	 * @access public
	 * @param array $cols An array of column names
	 * @param string $sort The order by clause direction ASC or DESC.  If not passed $sort defaults to 'ASC'
	 * @return DBTable
	 */ 
	public function orderBy($cols,$sort="ASC") { 
		if (!$cols) { throw new DBTableException("Missing 'cols' in {$this->class}::orderBy()"); }
		if (!is_array($cols)) { throw new DBTableException("Invalid 'cols' argument passed into {$this->class}::orderBy()"); }
		$order_by_list = "";
		foreach($cols as $column_name) {
			$order_by_list .= $column_name . " " . $sort . ",";
		}

		// remove any trailing comma
		$order_by_list = rtrim($order_by_list,",");
		$this->command->set('ORDERBY',"ORDER BY " . trim($order_by_list));

		return $this; 
	}

	/**
	 * Applies a 'Having' clause to the SQL statement
	 *
	 * @access public
	 * @param string $cond The conditional expression that defines the clause
	 * @return DBTable
	 */ 
	public function having($cond) { return $this; }

	/**
	 * Sets the maximum number of rows that the SQL statement will return
	 *
	 * @access public
	 * @param int $num 
	 * @return DBTable
	 */ 
	public function limit($num,$max=0) { 
    if ($max == 0) {
		  $this->command->set('LIMIT',"LIMIT {$num}");
    }
    else {
		  $this->command->set('LIMIT',"LIMIT {$num},{$max}");
    }
		return $this; 
	}
/* =============================================== *  DATA TABLE WRAPPER METHODS
	 * =============================================== 
	 */

	/**
	 * Adds a left join to the SQL statement
	 *
	 * @access public
	 * @param string $cond The conditional expression that defines the clause
	 * @return DBTable
	 */ 
	public function leftJoin($cond) { return $this; }

	/**
	 * Adds an 'AND' condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $cond The conditional expression that defines the clause
	 * @return DBTable
	 */ 
	public function andWhere($cond) {
		
		if (!is_string($cond)) { throw new DBTableException("Invalid conditional expression argument in {$this->class}::andWhere"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andWhere");
			
		}
		
		$this->command->set('WHERE',trim($where_clause) . " AND " . trim($cond));
				
		return $this;
	}

	/**
	 * Adds an 'OR' condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $cond The conditional expression that defines the clause
	 * @return DBTable
	 */ 
	public function orWhere($cond) {
		
		if (!is_string($cond)) { throw new DBTableException("Invalid conditional expression argument in {$this->class}::orWhere"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::orWhere");
			
		}
		
		$this->command->set('WHERE',trim($where_clause) . " OR " . trim($cond));
				
		return $this;
		
	}

	/**
	 * Adds an 'AND' $col equals $val condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @param string $val The value 
	 * @return DBTable
	 */ 
	public function andWhereEqual($col,$val) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andWhereEqual"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andWhereEqual");
			
		}
		
		if(is_null($val)){
			throw new DBTableException("2nd parameter can not be NULL in {$this->class}::andWhereEqual, try using :{$this->class}:andIsNull");
		}
		elseif(ctype_digit($val)){
			$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " = " . trim($val));
		}
		else{
			$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " = '" . trim($val) . "'");
			
		}
		
		return $this;
	
	}//end method andWhereEqual
	
	/**
	 * Adds an 'AND' $col NOT equals $val condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @param string $val The value 
	 * @return DBTable
	 */ 
	
	public function andNotEqual($col,$val) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andNotEqual"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andNotEqual");
			
		}
		
		if(is_null($val)){
			throw new DBTableException("2nd parameter can not be NULL in {$this->class}::andNotEqual, try using :{$this->class}:andIsNotNull");
		}
		elseif(ctype_digit($val)){
			$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " <> " . trim($val));
		}
		else{
			$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " <> '" . trim($val) . "'");
			
		}
		
		
		return $this;
	
	}//end method andNotEqual
	
	
	/**
	 * Adds an 'AND' $col is null condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @param string $val The value 
	 * @return DBTable
	 */
	
	public function andIsNull($col) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andIsNull"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andIsNull");
			
		}
		
		
		$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " is null");
		
		
		return $this;
	
	}//end method andIsNull
	
	/**
	 * Adds an 'AND' $col is not null condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @param string $val The value 
	 * @return DBTable
	 */
	
	public function andIsNotNull($col) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andIsNull"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andIsNotNull");
			
		}
		
		$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " is not null");
		
		return $this;
	
	}//end method andIsNotNull
	
	
	/**
	 * Adds an 'AND' $col > $val condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @param string $val The value, must be numeric ie. 1, 2, 3 or 0.1, 0.0002
	 * @return DBTable
	 */
	
	public function andGt($col,$val) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andGt"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andGt");
			
		}
		
		if(is_null($val)){
			throw new DBTableException("2nd parameter can not be NULL in {$this->class}::andGt");
		}
		else{
			$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " > " . $val);
			
		}
		
		
		return $this;
	
	}//end method andGT
	
	/**
	 * Adds an 'AND' $col < $val condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @param string $val The value, must be numeric ie. 1, 2, 3 or 0.1, 0.0002
	 * @return DBTable
	 */
	
	public function andLt($col,$val) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andLt"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andLt");
			
		}
		
		if(is_null($val)){
			throw new DBTableException("2nd parameter can not be NULL in {$this->class}::andLt");
		}
		else{
			$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) . " < " . $val);
			
		}
		
		return $this;
	}//end method andLt
	
	
	/**
	 * Adds an 'AND' $col = true condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @return DBTable
	 */
	
	public function andIsTrue($col) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andisTrue"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andIsTrue");	
		}
		
		$this->command->set('WHERE',trim($where_clause) . " AND " . trim($col) );
			
		
		return $this;
	}//end method andIsTrue
	
	/**
	 * Adds an 'AND' $col = false condition to an existing 'WHERE' clause
	 *
	 * @access public
	 * @param string $col The column name
	 * @return DBTable
	 */
	
	public function andIsFalse($col) {
		
		if (!is_string($col)) { throw new DBTableException("1st parameter must be a string column name in {$this->class}::andisFalse"); }
		
		$where_clause = null;
		if ($this->command->exists( 'WHERE' )) {
			$where_clause = $this->command->get('WHERE');
		}
		else{
			throw new DBTableException("Must set {$this->class}::where before appending {$this->class}::andIsFalse");	
		}
		
		$this->command->set('WHERE',trim($where_clause) . " AND NOT " . trim($col) );
		
		return $this;
	}

	/* ===============================================
	 *  DATA TABLE ACTION METHODS
	 * =============================================== 
	 */

	/**
	 * Executes the assembled SQL statement
	 *
	 * @access public
	 * @param $return_objects If true, then results will be returned as php stdClass objects.
	 * @return DBResult
	 */ 
	public function run($return_objects=false) { 
		
		// Execute the sql
		$this->result = mysqli_query($this->conn->handle,$this->export());
		if ($this->result == false) {
			throw new DBTableException( mysqli_error($this->conn->handle) );
		}
    
		// Eventually this will return an instance of DBResult
		return ( new DBResult($this->conn->handle,$this->result,$return_objects) );
	}

	/**
	 * Returns the assembled SQL statement as a string
	 *
	 * @access public
	 * @param string $part The portion of the query to return.  Valid parts are ('command','where','join','all').  Defaults to 'all'
	 * @return string
	 */ 
	public function export($part=NULL) { 
		$export = "";
		if (!$part) {
			if ($this->command->exists('SELECT')) {
				// Build the select string
				$export = sprintf("%s %s %s %s %s %s",
				        $this->command->get('SELECT'),
				        $this->command->get('JOIN'),
				        $this->command->get('WHERE'),
				        $this->command->get('GROUPBY'),
				        $this->command->get('ORDERBY'),
				        $this->command->get('LIMIT')
			  );
			}
			else if ($this->command->exists('INSERT')) {
				// Build the insert string
				$export = sprintf("%s",$this->command->get('INSERT'));
			}
			else if ($this->command->exists('UPDATE')) {
				// Build the update string
				$export = sprintf("%s %s",$this->command->get('UPDATE'),$this->command->get('WHERE'));
			}
			else {
				// This works because PHP iterates over the array in FIFO queue fashion...
				foreach ($this->command->get() as $key => $value) {
					$export .= trim($value) . " "; 
				}
			}
		}
		else {
			// build string for specific section
			$export = $this->command->get( strtoupper($part) ); 
		}
		return trim($export);
	}

	/**
	 * Sets the allowNullAsValue flag for the insert and update database methods. Default is FALSE.
	 *
	 * @access public
	 * @param boolean $b The true or false value for the allowNullAsValue flag
	 * @return boolean
	 */ 
  public function nullAsValue($b=NULL) { 
    if ( (is_bool($b)) && (!is_null($b)) ) { 
      $this->allowNullAsValue = $b; 
    }
    return $this->allowNullAsValue;
  }

  public function nullValue($value) {
    $retval = FALSE;
    if ( (is_null($value)) || (strcasecmp($value,"\0")==0) || (strcasecmp($value,"NULL")==0) ) {
      $retval = TRUE;
    }
    return $retval;
  }

	/**
	 * Executes a SQL union with the SQL defined within the instance and the DBTable object passed in
	 *
	 * @access public
	 * @param DBTable $dt The DBTable object containing another valid query
	 * @return DBResult
	 */ 
	public function union($dt) { return; }

	/**
	 * Executes a SQL intersection with the SQL defined within the instance and the DBTable object passed in
	 *
	 * @access public
	 * @param DBTable $dt The DBTable object containing another valid query
	 * @return DBResult
	 */ 
	public function intersect($dt) { return; }
}

/**
 * DBTable Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class DBTableException extends Exception {
	public function __construct($msg,$code=0) {
		parent::__construct($msg,$code);
	}
}
?>
