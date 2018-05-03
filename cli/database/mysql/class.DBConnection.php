<?php
/**
 * Database Connection Class
 *
 * @package DooFramework
 * @subpackage database
 */

/**
 * Database connection class providing an interface to a database connection
 *
 * @package DooFramework
 * @subpackage database
 */
class DBConnection
{
	
	/**
	 * Static array of all database connections
	 * @access private
	 * @static
	 */
	private static $allhandles = array();

	/**
	 * The specific connection for a class instance
	 * @access public
	 * @var resource Database connection handle
	 */
	public $handle;

	/**
	 * The connection key for a class instance connection
	 */
	private $conn_key;
	
	/**
	 * Is this a persistent connection?
	 */
	private $persist;
	

	/**
	 * DBConnection Constructor
	 *
	 * Initialize a new DBConnection
	 * @access public
	 * @param array $params Parameters for db connection.  Array must contain keys the following keys:  'host' = hostname of db server; 'name' = name of the database; 'user' = username of the database user; 'pass' = password for the user.
	 * @param bool $persist Flag for whether or not this connection should be persistent
	 */
	public function __construct($params=array(),$persist=true) {
		if ( !is_array($params) ) {
			throw new DBConnectionException("Invalid database connection parameter format");
		}

		$host = $params['host'];
		$name = $params['name'];
		$user = $params['user'];
		$pass = $params['pass'];

		if ( !($host && $name && $user && $pass) ) {
			throw new DBConnectionException("Missing database connection parameters");
		}

		if ( !(is_string($host) && is_string($name) && is_string($user) && is_string($pass)) ) {
			throw new DBConnectionException("Invalid database connection parameters");
		}

		// generate connection key
		$key = md5($host.$name.$user.$pass);

		// see if we already have a connection
		if ( !isset(self::$allhandles[$key]) ) {
			if ( $persist ) {
				$dbh = @mysqli_connect($host,$user,$pass,$name);
			}
			else {
				$dbh = @mysqli_connect($host,$user,$pass,$name);
			}
			if ( !$dbh ) {
				throw new DBConnectionException( "Connect Error (" . mysqli_connect_errno() . ") " . mysqli_connect_error() );
			}
			self::$allhandles[$key] = $dbh;
		} 

		// set the handle and key in this instance
		$this->handle = self::$allhandles[$key];
		$this->conn_key = $key;
		$this->persist = $persist;
	}

	public function close() {
		if ( $this->persist ) return;

		if ( is_resource($this->handle) ) {
			if ( mysqli_close($this->handle) ) {
				unset(self::$allhandles[$this->conn_key]);
				$this->conn_key = null;
			}
			else {
				throw new DBConnectionException("Failed to close database connection");
			}
		}
		else {
			unset(self::$allhandles[$this->conn_key]);
			$this->conn_key = null;
		}
		
		return(true);
	}
}

/**
 * DBConnection Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class DBConnectionException extends Exception {
	public function __construct($msg,$code=0) {
		parent::__construct($msg,$code);
	}
}
?>
