<?php
class cLogger {
  // Message Levels
  const DEBUG='DEBUG';
  const NOTICE='NOTICE';
  const WARNING='WARNING';
  const INFO='INFO';
  const ERROR='ERROR';
  const FATAL='FATAL';

  // Report Levels
  const QUIET = 'QUIET';
  const SILENT = 'QUIET';
  const VERBOSE = 'VERBOSE';
  const NORMAL = 'NORMAL';

	const TABLE_PRIMARY_KEY = "plid";
	// internal class properties
	public $vars = null;
	// database connection item
	private $conn = null; 

	public function __construct($data = null) {
		$cli = $GLOBALS['cli'];

		// get the database connection
		$this->conn = $cli->dbconn;

		// if data the create datastore
		if (!is_null($data)) {
			$this->vars = new DataStore($data);
		}
		else {
			$this->vars = new DataStore();
		}

    // report levels: quiet, verbose, normal
    if (!$this->vars->exists('report_level')) {
      $this->vars->set('report_level','normal');
    }

    if (!$this->vars->exists('out_to_screen')) {
      $this->vars->set('out_to_screen',FALSE);
    }

    if (!$this->vars->exists('message_level')) {
      $this->vars->set('message_level',self::INFO);
    }

    if (!$this->vars->exists('show_process_name')) {
      $this->vars->set('show_process_name',TRUE);
    }
	}

	public function save() {
		$retval = true;

		try {
			// if an integer id is present then we need to do an update
			if ($this->vars->exists(self::TABLE_PRIMARY_KEY)) {
				$this->update();
			}
			else { // otherwise do the insert
				$this->insert();
			}
		}
		catch (AppException $e) {
			$retval = false;
		}
		return $retval;
	}

	private function insert() {
		// get the properties
		$data = $this->vars->get();
		// create table instance
		$table = new cLoggerTable($this->conn);
		try {
			// chain insert
			$result = $table->insert($data)->run(); 
			// set insert id
			$this->vars->set(self::TABLE_PRIMARY_KEY,$result->getInsertId());
		}
		catch (DBTableException $e) {
			$this->vars->set(self::TABLE_PRIMARY_KEY,null);
			throw new AppException($e->getMessage(),"cLogger");
		}
	}

	private function update() {
		// get the properties
		$data = $this->vars->get();

		// get the primary key value
		$id = $this->vars->get(self::TABLE_PRIMARY_KEY);

		// create table instance
		$table = new cLoggerTable($this->conn);

		try {
			// chain update
			$table->update($data)->where(self::TABLE_PRIMARY_KEY."=$id")->run();
		}
		catch (DBTableException $e) {
			throw new AppException($e->getMessage(),"cLogger");
		}
	}

	public static function truncate($process_name=NULL) {
		$cli = $GLOBALS['cli'];
		// get the database connection
		$conn = $cli->dbconn;
		$trunc = new SQLExecutor($conn);
    if (is_null($process_name)) {
		  $trunc->sql("TRUNCATE TABLE clog")->run();
    }
    else {
		  $trunc->sql("DELETE FROM clog WHERE process_name='" . $process_name . "'")->run();
    }
	}

	public function log($message,$level=NULL,$screen=NULL) {
    flush();
    $save_to_database = $this->vars->get('save_to_database');

    // handle screen output
    if ( $this->vars->exists('out_to_screen') && (is_null($screen)) ) {
      $out_to_screen = $this->vars->get('out_to_screen'); 
    }
    else if ( is_bool($screen) ) {
      $out_to_screen = $screen;
    }
    else {
      $out_to_screen = FALSE;
    }

    if (is_null($level)) {
      $error_level = $this->vars->get('message_level');
    }
    else {
      $error_level = strtoupper($level);
    }

    if ($this->vars->get('show_process_name') == TRUE) {
      $show_process_name = TRUE;
    }
    else {
      $show_process_name = FALSE;
    }

    if (strcasecmp($this->vars->get('report_level'),self::NORMAL) == 0) {
      $elvls = array('ERROR','FATAL','INFO');
    }
    else if (strcasecmp($this->vars->get('report_level'),self::VERBOSE) == 0) {
      $elvls = array('NOTICE','DEBUG','WARNING','ERROR','FATAL','INFO');
    }
    else if (strcasecmp($this->vars->get('report_level'),self::SILENT) == 0) {
      $elvls = array('ERROR','FATAL');
    }

    // create new logger
    if (in_array(strtoupper($error_level),$elvls)) {
      $this->vars->set('message',trim($message));
      // if out to screen is true...
      if ($out_to_screen) {
        if ($show_process_name == TRUE) {
          printf("(%s) <%s::%s> %s\n",
            trim($error_level),$this->vars->get('process_name'),$this->vars->get('function'),trim($message)
          );
        }
        else {
          printf("(%s) <%s> %s\n",
            trim($error_level),$this->vars->get('function'),trim($message)
          );
        }
        flush();
      }
      // save to database if requested
      if ($save_to_database) {
        // save the message
        $log = new cLogger( $this->vars->get() );
        $log->save(); $log = NULL; 
      }
    }
	}

  // sets the current execution location
  public function register($func) {
    $this->vars->set('function',$func);
  }

	public static function getLogBySeed($csid) {
		$item = cLogger::getLogRecords($csid);
		if (count($item) > 0) {
			return $item[0];
		}
		else {
			return $item;
		}
	}

	public static function getLogRecords($id = 0) {
		$cli = $GLOBALS['cli'];
		$config = null;
		$results = array();

		// get the database connection
		$conn = $cli->dbconn;

		// create query to get all of the items
		$table = new cLoggerTable($conn);

		// grab the data from the table
		$table->select();
		if ($id > 0) {
			$table->where("csid = " . $id);
		}
		// fire the query
		$result = $table->run();

		// if rows
		if ($result->count() > 0) {
			foreach ($result as $row) {
				$results[] = new cLogger($row);
			}
		}
		return $results;
	}
}
?>
