#!/usr/bin/php
<?php
// ##################################################
// script constants
// ##################################################
define("ROOT_PATH",dirname(dirname(__FILE__)));
define("BIN",ROOT_PATH . "/bin");
define("LIB",ROOT_PATH . "/lib");

// ##################################################
// Function writeClass
// ##################################################
function writeClass($db_conn,$dbclass_template,$output_path,$table_name=null) {
	global $cli;
  $primary_key_count = 0;
	$column_string = '';
	$file_name = '';
	$output_buffer = '';
	$file_handle = null;
	// generate the class name
	$class_name = genClassName($table_name);

	// get instance of free form sql executor
	$table_sql = new SQLExecutor($db_conn);
	// set the query
	$table_sql->sql("SELECT DISTINCT * FROM information_schema.columns WHERE table_name = '$table_name'");
	// execute the query
	$table_results = $table_sql->run();

	// loop over results to generate the array elements
	foreach($table_results as $row) {
		//print_r($row);
		if (strcasecmp($row['COLUMN_KEY'],'PRI') == 0) {
			$row['primary_key'] = "TRUE"; 
      $primary_key = '"' . $row['COLUMN_NAME'] . '"';
      $primary_key_count = 1;
		}
		else {
			$row['primary_key'] = "FALSE";
		}

		if (!array_key_exists("CHARACTER_MAXIMUM_LENGTH",$row)) {
			$row['CHARACTER_MAXIMUM_LENGTH'] = "NULL";
		}

		if (!array_key_exists("NUMERIC_PRECISION",$row)) {
			$row['NUMERIC_PRECISION'] = "NULL";
		}

		$column_string .= sprintf(
		                          "\t          array('name'=>'%s','primary_key'=>%s,'data_type'=>'%s','max_length'=>'%s','numeric_precision'=>'%s','default'=>'%s','is_nullable'=>'%s'),\n",
		                          $row['COLUMN_NAME'],
		                          $row['primary_key'],
		                          $row['DATA_TYPE'],
		                          $row['CHARACTER_MAXIMUM_LENGTH'],
		                          $row['NUMERIC_PRECISION'],
		                          StringOutput::qq( str_replace("'",'',$row['COLUMN_DEFAULT']) ),
		                          $row['IS_NULLABLE']
		                         ); 
	}

  // check to see if there was a primary key at all
  if ($primary_key_count == 0) {
    $primary_key = "NULL";
  }

	// strip last comma
	$column_string = rtrim($column_string,",\n");
	// output the class string
	$output_buffer = sprintf($dbclass_template,
	       $table_name,
	       $class_name,
	       $class_name,
	       $class_name,
	       $table_name,
	       "array(\n" .  $column_string . "\n\t)",
	       $primary_key,
	       $class_name
	); 
	// write the file
	$file_name = $output_path . "/" . ucfirst($class_name) . "Table.php";
	
	if (count($table_results) > 0) {
		print "...generating $file_name\n";
		$file_handle = fopen($file_name,"w");

		// strip last comma
		$output_buffer = ltrim($output_buffer,"\n");

		fwrite($file_handle,$output_buffer);
		fclose($file_handle);
	}
	else {
		print "[ERROR] Can't generate $file_name because '$table_name' does not exists in this database\n";
	}
}

// ##################################################
// Function getFileName
// ##################################################
function genClassName($table_name) {
	$class_name = '';
	// break file name up by underscore
	$parts = explode('_',$table_name);
	if (is_array($parts)) {
		foreach ($parts as $word) {
			$class_name .= ucfirst( trim($word) );
		}
	}
	else {
			$class_name .= ucfirst( trim($parts) );
	}
	return $class_name;
}

// ##################################################
// set script variables
// ##################################################
$db_conn = NULL;
$db_version = NULL;
$table_name = NULL;
$table_sql = NULL;
$table_results = NULL;
$column_string = NULL;

// get the table name from the arguments structure
$site_path = trim($argv[1]);
$output_path = trim($site_path . "/model");
//$output_path = trim($site_path . "/sql");
$config_path = trim($site_path . "/config.php");
$table_name = "all";

require($config_path);
  
$cli = $GLOBALS['cli'];

// ##################################################
// set the script usage message
// ##################################################
$script_usage = "USAGE: $argv[0] --table=<TABLENAME> --output=<PATH> --config=<PATH>\n\n" . 
"OPTIONS:\n\n--table          Name of the table class will be generated for\n\n" .
"--output         Path to where the generated files will be stored\n";

$filename_template = "class.%sTable.php";

$dbclass_template = StringOutput::qq("
<?php
/**
 * Define base database functionality for the %s table 
 *
 * Defines the base %s class 
 *
 */

/**
 * %sTable base class
 */
class %sTable extends DBTable {
\t/** 
\t * @access protected
\t * @var string String that contains the table name unto which the SQL operations will be applied
\t */
\tprotected \$TABLENAME = \"%s\";	

\t/** 
\t * @access protected
\t * @var array Associative array data structure containing table column specific information
\t */
\tprotected \$COLUMNINFO = %s;	

\t/** 
\t * @access protected
\t * @var string Primary key for the table
\t */
\tprotected \$PRIMARYKEY = %s;	

\t/**
\t * Contructor for %sTable class
\t *
\t * @access public
\t * @param DBConnection \$connection Valid instance of the DBConnection class.  This parameter is optional
\t * @return void
\t */
\tpublic function __construct(\$connection=null) { 
\t\tparent::__construct(\$connection);
\t}
}
?>
");

try {
	// get database connection
	$db_conn = $GLOBALS['cli']->dbconn;

	// get the postgres version data structure
	print "##########################################\n";
	print "DOO DATA TABLE ACCESS CLASS GENERATOR\n";
	print "##########################################\n";
  print "MySQL Client Version: " . mysqli_get_client_version()  . "\n";
  print "MySQL Server Version: " . mysqli_get_server_version($db_conn->handle) . "\n";
  print "MySQL Protocol Version: " . mysqli_get_proto_info($db_conn->handle) . "\n\n";

	// ##################################################
	// get one row from the specified table
	// ##################################################

	// the table name equals all then build classes for all tables
	if (strcasecmp($table_name,"all") == 0) {
		// get instance of free form sql executor
		$table_sql = new SQLExecutor($db_conn);
		// set the query
		$test = "SELECT DISTINCT * FROM information_schema.tables WHERE table_schema = '" .
    $cli->dbinfo['name']. "'"; 
		$table_sql->sql("SELECT DISTINCT * FROM information_schema.tables WHERE table_schema = '" .
    $cli->dbinfo['name'] . "'");
		// execute the query
		$table_results = $table_sql->run();
		// loop over results to generate the array elements
		
		foreach($table_results as $row) { $table_name = $row['TABLE_NAME']; writeClass($db_conn,$dbclass_template,$output_path,$table_name); }
	}
	else { writeClass($db_conn,$dbclass_template,$output_path,$table_name); }

	print "DONE\n\n";
}
catch( SQLExecutorException $e ) {
	print $e->getMessage();
}
catch( Exception $e ) {
	print $e->getMessage();
}
?>
