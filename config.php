<?php
ini_set('display_errors', 1);
error_reporting(-1);

// set default timezone
date_default_timezone_set("America/New_York");

//=================================================
// Pathing and file locale constants
//=================================================
define("PATH",dirname(__FILE__)); 
define("CLI_PATH",PATH . "/cli");
define("BIN_PATH",PATH . "/bin");
define("CORE_PATH",PATH . "/core");
define("MODEL_PATH",PATH . "/model");

//=================================================
// Content Gather Specific Directories
//=================================================

// load base components
require("cli/common/class.DataStore.php");
require("cli/common/class.Parameter.php");
require("cli/common/class.ObjectSpy.php");
// base database components
require("cli/database/mysql/class.DBConnection.php");
require("cli/database/mysql/class.DBResult.php");
require("cli/database/mysql/class.DBTable.php");
require("cli/database/mysql/class.SQLExecutor.php");
// base string components
require("cli/string/class.StringOutput.php");
require("cli/string/class.StringConvert.php");
// base command line helper components
require("cli/class.ScriptArgs.php");
require("cli/class.Script.php");
// Utility components
require("cli/common/class.OutputWriter.php");
require("cli/xml/class.XmlDocument.php");

require("cli/http/class.Http.php");
require("cli/logging/class.cLogger.php");

$GLOBALS['cli'] = new StdClass();

// Database Details
$GLOBALS['cli']->dbinfo = array(
  'name' => 'onblogs',
  'host' => 'localhost', 
  'user' => 'dbuser', 
  'pass' => 'Venus8799$'
);

if (isset($argv) && (!empty($argv))) {
  $GLOBALS['cli']->script = new Script($argv);
}
else {
  $GLOBALS['cli']->script = null;
}

// Database Connection
$GLOBALS['cli']->dbconn = new DBConnection($GLOBALS['cli']->dbinfo);

// DO NOT REMOVE
include_once(PATH . "/import.php");
// DO NOT REMOVE AUTOLOAD INCLUDE
@include_once(PATH . "/autoload.php");
// COMPOSER PSR AUTOLOAD
@include_once(PATH . "/vendor/autoload.php");
//require 'vendor/autoload.php';
