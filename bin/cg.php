#!/usr/bin/php
<?php
//=================================================
// Pathing and file locale constants
//=================================================
define("SCRIPTS_PATH",dirname(__FILE__));
define("CURRENT_SCRIPT_PATH",dirname(__FILE__));
define("ROOT_PLUGIN_PATH",dirname(SCRIPTS_PATH));
define("LOGFILE_PATH",dirname(SCRIPTS_PATH) . "/logs");

//=================================================
// include configuration files
//=================================================
require(ROOT_PLUGIN_PATH . "/config.php");

// source in simple_html_dom
// require(ROOT_PLUGIN_PATH . "/packages/simple_html_dom/simple_html_dom.php");

class CGExec {

	private $debug = false;
	private $conn = null;
  private $cli = null;

	public function main() {
		$cli = $GLOBALS['cli'];
		
		// set script usage string
		$script_usage = "\nUSAGE: " . $cli->script->name . " --site=<string> --job=<string> --feed=<id(s)>\n" . 
		                "\nOPTIONS:" . 
		                "\n--site\tThe site to grab data for\n" .
		                "\n--job\tThe job to run\n" .
		                "\n--fid\tThe feed id or comma separated list of feeds\n" .
		                "\nFLAGS:" . 
		                "\n-d\t\tThis flag puts the script execution into 'debug' mode\n";
		                "\n-h\t\tThis flag will output the script usage\n";

		// get command line arguments
		if ($cli->script->flagExists('h')) {
			// print the usage string
			StringOutput::printqn($script_usage);	
			// exit the script execution
			exit(0);
		}

    // Test for logging verbosity flag
    $this->report_level = \Teacup\Log::INFO;
    if ($cli->script->flagExists('v')) { $this->report_level = \Teacup\Log::DEBUG; }
    if ($cli->script->flagExists('s')) { $this->report_level = \Teacup\Log::ERROR; }

    // get the site name
		if ($cli->script->optionExists('site')) {
			$site = $cli->script->option('site')->asString();
		}
		else {
			StringOutput::printqn("No Site Name Specified");	
      exit(1);
		}

    // get the job name
		if ($cli->script->optionExists('job')) {
			$job = $cli->script->option('job')->asString();
		}
		else {
			StringOutput::printqn("No Job Name Specified");	
      exit(1);
		}

		// check for debug flag
		if ($cli->script->flagExists('d')) {
			$this->debug = true;
		}

    // ########################################
    // Let's load up the log
    // ########################################

		// grab site info
		$this->site = Site::getByName($site);
    // no matching site found then get out with message
    if (empty($this->site)) {
			StringOutput::printqn("No Site Available By That Name ({$site})");	
      exit(2);
    }

		// grab the site specific job info
		$this->job = Job::getByName($job);
    // no matching site found then get out with message
    if (empty($this->job)) {
			StringOutput::printqn("No Job Available By That Name ({$job})");	
      exit(3);
    }

    // ########################################
    // valid site has been located
    // ########################################
    try {
      print_r($this->job);

      if ( strlen($this->job[0]->job_logfile) > 0) {
        // create the logger
        $this->logger = new \Teacup\Log(LOGFILE_PATH,$this->job[0]->job_logfile,$this->report_level);
        // set the log message format
        $this->logger->setLogFormat('[%2$s|%1$s] %3$s');
      }
      else {
        // no logger object
        $this->logger = NULL;
      }

      // =====================================================================
      // EXECUTE THE JOB
      // =====================================================================

      // grab the job name
      $job_name = $this->job[0]->job_name;

      $jobmgr = new $job_name ( $this->job[0], $this->logger );

      $feed_id = NULL;
      $options = array();

      // loop over job parameters and set them as options
      if (strlen($this->job[0]->job_params) > 0) {
        $job_params = json_decode($this->job[0]->job_params,TRUE);
        foreach($job_params as $key => $value) {
          $options[$key] = $value;
        }
      }

      // add in any command line arguments
      if ($cli->script->optionExists('fid')) {
        $options['run_fid'] = $cli->script->option('fid')->asString();
      }

      // pass in job arguments
      $jobmgr->start( $options );

      try {
        $jobmgr->run();
      }
      catch (Exception $e) {
        print_r($e);
        $jobmgr->stop();
      }

      $jobmgr->finish();

		}
		catch (Exception $e) {
			print_r($e->getMessage());

			if ($this->debug) {
				var_dump($e);
			}

			exit( $e->getCode() );
		}

		// exit the program
		exit(0);
	}

  // Get the feed ids that have not been updated within the specified time interval
  private function getFeeds($conn,$interval,$num_feeds=10) {
		$feeds = new SQLExecutor($conn);
    $results = $feeds->sql("SELECT fid FROM feed WHERE status = 'active'" .
                           " AND fetch_date < DATE_SUB(NOW(), INTERVAL " . $interval . ")" . 
                           " ORDER BY fetch_date ASC" .
                           " LIMIT " . $num_feeds
                          )->run();
    return ($results);
  }
}

$command = new CGExec();
$command->main();
?>
