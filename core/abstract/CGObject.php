<?php
class CGObject {
public $vars = NULL;

public function __construct() {
$this->vars = new StdClass();
}

public function set($key,$value) {
   $this->vars->$key = $value; 
}

public function get($key) {
  if (isset($this->vars->$key)) {
    return $this->vars->$key;
  }
  else {
    $trace = debug_backtrace();
    trigger_error('Undefined property via __get(): ' . $name .
                  ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
   return NULL;
  }
}

public function __destruct() {
  unset($this->vars); $this->vars = NULL;
}
}
