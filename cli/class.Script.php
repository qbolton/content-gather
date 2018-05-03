<?php
/**
 * Define Script class
 *
 * Defines the Script class to provide methods to handle and access script arguments 
 * and common command line script operations
 * @package DooFramework
 * @subpackage common.cli
 */

/**
 * Script class
 * @package DooFramework
 * @subpackage common.cli
 */
class Script {

  /**
   * @access public
   * @var String Name of the script
   */
  public $name;

  /**
   * @access public
   * @var String path of the script
   */
  public $path;

  /**
   * @access private
   * @var DataStore Data store for command line options
   */
  private $options;

  /**
   * @access private
   * @var DataStore Data store for command line solo arguments
   */
  private $arguments;

  /**
   * @access private
   * @var DataStore Data store for command line flag switches
   */
  private $flags;

  /**
   * @access private
   * @var DataStore Data buffer containing data from the standard input file handle
   */
  private $stdin = null;

  /**
   * @access private
   * @var DataStore Count of arguments
   */
  private $argument_count = 0;

  /**
   * Constructor for Script object
   */
  public function __construct($arguments = null) {
    if (!is_null($arguments) && is_array($arguments) && !empty($arguments)) {

      // argument count
      $this->argument_count = count($arguments);

      // get script name
      $this->name = basename($arguments[0]);
      $this->path = dirname($arguments[0]);

      $args = ScriptArgs::arguments($arguments);
      $arg_count = 0;
      
      $this->flags = new DataStore();
      $this->arguments = new DataStore();

      // handle the options
      $this->options = new DataStore($args['options']);

      // loop over the flags
      foreach ($args['flags'] as $flag) {
        $this->flags->set($flag,true);  
      }

      // determine which argument group to use
      if (count($args['commands']) > 0) {
        $args_list = $args['commands'];
      }
      else {
        $args_list = $args['arguments'];
      }

      // loop over the arguments
      foreach ($args_list as $argument) {
        // increment arg_count
        $arg_count++;
        $this->arguments->set("arg" . $arg_count,new Parameter($argument));  
      }
    }

  }

  /*************************/
  /*** PUBLIC PROPERTIES ***/
  /*************************/

  /**
   * Gets an option from the command line
   *
   * Retrieves a command line option and it's value
   *
   * @access public
   * @param string $name The name of the option
   * @param mixed $default An optional default value to be returned if the named option
   * does not exist.
   * @return Parameter The parameter object
   * @throws ScriptException
   */
  public function option($name,$default=null)
  {
    if ( !$name || !is_string($name) ) {
      throw new ScriptException("Option name is missing or invalid in call to Script::option()");
    }

    $option = null;
    if ( $this->optionExists($name) ) {
      $option = new Parameter($this->options->get($name));
    }
    else {
      $option = new Parameter($default);
    }
    return $option;
  }

  /**
   * Check for the existance of a command line option
   *
   * See if a given command line option exists and returns true/false.
   *
   * @access public
   * @param string $name The name of the option to check
   * @return bool Returns true if the option exists, false otherwise
   * @throws ScriptException
   */
  public function optionExists($name)
  {
    if ( !$name || !is_string($name) ) {
      throw new ScriptException("Parameter name is missing or invalid in call to HttpRequest::param()");
    }

    return $this->options->exists($name);
  }

  /**
   * Check for the existance of a flag
   *
   * See if a given flag exists and returns true/false.
   *
   * @access public
   * @param string $name The name of the flag to check
   * @return bool Returns true if the cookie exists, false otherwise
   * @throws ScriptException
   */
  public function flagExists($name)
  {
    if ( !$name || !is_string($name) ) {
      throw new ScriptException("Flag name is missing or invalid in call to Script::flagExists()");
    }

    return $this->flags->exists($name);
  }

  /**
   * Gets an argument value
   *
   * Retrieves a command line solo argument
   *
   * @access public
   * @param string $name The name of the argument (usually arg1, arg2, arg3)
   * @param mixed $default An optional default value to be returned if the named argument
   * does not exist.
   * @return Parameter The parameter object
   * @throws ScriptException
   */
  public function arg($name,$default=null)
  {
    if ( !$name || !is_string($name) ) {
      throw new ScriptException("Argument name is missing or invalid in call to Script::arg()");
    }

    $arg = null;
    if ( $this->argExists($name) ) {
      $arg = $this->arguments->get($name);
    }
    else {
      $arg = new Parameter($default);
    }

    return $arg;
  }

  public function flags()
  {
    return $this->flags->get();
  }

  public function options()
  {
    return $this->options->get();
  }

  /**
   * Get all existing arguments
   *
   * Returns an array of solo command line argument names and values
   *
   * @access public
   * @return array Associative Array of Parameter objects
   */
  public function args()
  {
    return $this->arguments->get();
  }

  /**
   * Check for the existence of a particular argument
   *
   * See if a given argument exists and returns true/false.
   *
   * @access public
   * @param string $name The name of the argument to check
   * @return bool Returns true if the argument exists, false otherwise
   * @throws ScriptException
   */
  public function argExists($name)
  {
    if ( !$name || !is_string($name) ) {
      throw new ScriptException("Argument name is missing or invalid in call to Script::argExists()");
    }

    return $this->arguments->exists($name);
  }

  /**
   * Get standard input
   *
   * If there is data, this will return the entire standard input buffer
   *
   * @access public
   * @params $flags See the following url for available flags: http://us3.php.net/manual/en/function.file-get-contents.php
   * @return string|null Returns standard input data
   */
  public function stdin($flags=null)
  {
    // pull in stdin if there is anything
    if (!is_null($flags)) {
      $this->stdin = file_get_contents("php://stdin");
    }
    else {
      $this->stdin = file_get_contents("php://stdin",$flags);
    }
    return $this->stdin;
  }

  /**
   * Count
   *
   * Return the count of the arguments passed on command line
   *
   * @access public
   * @return integer Returns 0 or the number of arguments
   */
  public function count()
  {
    return $this->argument_count;
  }
}


/**
 * Script Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class ScriptException extends Exception
{
  public function __construct($msg,$code=0)
  {
    parent::__construct($msg,$code);
  }
}
?>
