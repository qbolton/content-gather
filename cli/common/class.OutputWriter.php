<?php
/**
 * OutputWriter class defines all methods and functionality for converting an array or object to 
 * a given character class, encoding or output type.  Supported output is JSON, XML, Serialized PHP, CSV
 *
 * @package DooFramework
 * @subpackage common.util
 */

/**
 * OutputWriter class
 *
 * @package DooFramework
 * @subpackage common.util
 */
class OutputWriter
{
	
	/**
	 * @access public
	 * @var DataStore Stores the class attributes
	 */
	public $vars = null;

	/**
	 * @access private
	 * @var array List of supported output types
	 */
	private $output_types = array('xml','csv','json','php');

	/**
	 * OutputWriter Constructor
	 *
	 * Initialize a new OutputWriter
	 *
	 * @access public
	 * @param mixed The data to output.  Can be a string, array or object
	 * @param string The type of output you want.  
	 */
	public function __construct($data,$output_type="xml") {
		if ( is_null($data) ) { throw new OutputWriterException("Data parameter is invalid"); }

		// create new datastore instance 
		$this->vars = new DataStore(); 
		// initialize raw data property
		$this->vars->set('raw_data',null);
		// initialize options property
		$this->vars->set('options',0);
		// initialize generated data property
		$this->vars->set('output',null);
		
		// set the output type
		if (in_array(strtolower($output_type),$this->output_types)) {
			$this->vars->set('output_type',strtolower($output_type));
		}
		else {
			throw new OutputWriterException($output_type . " is not a supported output type");
		}
	
		if (is_object($data)) { 
			// set this to maintain copy of original object
			$this->vars->set('raw_data',$data); 
			$this->vars->set('data',get_object_vars($data));
		} 
		else { 
			// set raw data as property
			$this->vars->set('data',$data);
		};	
	}

	/**
	 * setOptions
	 *
	 * Sets the bitmask execution options for the return type specified.
	 *
	 * @access public
	 * @param bitmask Execution option(s) 
	 * @return string Returns the class name as a string value
	 */
	public function setOptions($options=null) {
		$this->vars->set('options',$options);
	}

	/**
	 * generate
	 *
	 * Generates the specified output from the input data
	 *
	 * @access public
	 * @return void
	 */
	public function generate() {

		// holds method return value
		$retval = null;

		// retrieve the output type
		$output_type = $this->vars->get('output_type');

		// retrieve the output options
		$options = $this->vars->get('options');

		// retrieve the data
		$data = $this->vars->get('data');

		// XML
		if (strcasecmp($output_type,'xml') == 0) {
			$xml = new XmlDocument('1.0','utf-8');
			$xml->fromMixed($data);
			$retval = $xml->saveXML();	
		}
		// JSON
		else if (strcasecmp($output_type,'json') == 0) {
			$retval = json_encode($data);	
		}
		// PHP
		else if (strcasecmp($output_type,'php') == 0) {
			$raw_data = $this->vars->get('raw_data');
			// if it is null the serialize the raw data
			if (!is_null($raw_data)) {
				$retval = serialize($raw_data);
			}
			else {
				$retval = serialize($data);
			}
		}
		else {
			throw new OutputWriterException($output_type . " is not a supported output type");
		}
		// set the generated data as property
		$this->vars->set('output',$retval);
	}

	/**
	 * write
	 *
	 * Outputs the specified output string
	 *
	 * @access public
	 * @return void 
	 */
	public function write()
	{
		// check to see if any output has been created
		if (is_null($this->vars->get('output'))) {
			// call the generate method
			$this->generate();
		}
		// output the generated data
		print ($this->vars->get('output'));
	}
}

/**
 * OutputWriter Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class OutputWriterException extends Exception {
	public function __construct($msg,$code=0) {
		parent::__construct($msg,$code);
	}
}
?>
