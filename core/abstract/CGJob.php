<?php
abstract class CGJob extends CGObject {
  protected $logger = NULL;
  protected $job = NULL;
  protected $modules = NULL;
  protected $options = NULL;

  public function __construct($job,$logger=NULL) {
    $this->job = $job;
    $this->logger = $logger;
  }

  public function start($options=NULL) {
    // put the options into the data store
    if ( (!is_null($options)) && (is_array($options)) ) {
      $this->options = new DataStore( $options );
    }

    // get the modules out if there are any
    if (strlen($this->job->job_modules) > 0) {
      $this->modules = explode(',',$this->job->job_modules);
    }
  }


  //public function run_modules(); 

  public function __destruct() {
    parent::__destruct();
    unset($this->job);
    unset($this->logger);
    unset($this->options);
    unset($this->modules);
  }
  
  abstract public function run();
  abstract public function finish();
}
?>
