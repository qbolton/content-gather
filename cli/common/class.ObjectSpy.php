<?php
/**
 * Object dumper class instance display class
 *
 * @package DooFramework
 * @subpackage common.util
 */

/**
 * Object dumper class providing functionality to display information about an instance of a class
 *
 * @package DooFramework
 * @subpackage common.util
 */
class ObjectSpy
{
	
	/**
	 * Property to reference the class instance to be inspected
	 * @access private
	 * @var object This can be an instance of any defined class
	 */
	private $object = null;

	/**
	 * Property to reference the class name of the instance
	 * @access private
	 * @var string The class name of the instance
	 */
	private $object_class_name = null;

	/**
	 * Property to reference the parent class name of the instance
	 * @access private
	 * @var string The parent class name of the instance
	 */
	private $object_parent_name = null;

	/**
	 * Property to reference the method array for the instance
	 * @access private
	 * @var array The array of method names
	 */
	private $object_methods = array();

	/**
	 * Property to reference the default properties array for the instance
	 * @access private
	 * @var array The associative array of default properties
	 */
	private $object_default_vars = array();

	/**
	 * Property to reference the properties array for the instance
	 * @access private
	 * @var array The associative array of properties
	 */
	private $object_vars = array();

	/**
	 * ObjectSpy Constructor
	 *
	 * Initialize a new ObjectSpy
	 * @access public
	 * @param object Valid instance of any defined class

	 */
	public function __construct($object=null) {
		if ( !is_object($object) ) { throw new ObjectSpyException("Constructor parameter must be an object"); }
		// set object property
		$this->object = $object;
		// setup class name property
		$this->object_class_name = get_class($this->object);
		// setup parent class name property
		$this->object_parent_name = get_parent_class($this->object);
		// set up class methods property
		$this->object_methods = get_class_methods($this->object_class_name);
		// set up default properties of instance
		$this->object_default_vars = get_class_vars($this->object_class_name);
		// set up default properties of instance
		$this->object_vars = get_object_vars($this->object);
	}

	/**
	 * Retrieve the class name of the object
	 *
	 * Returns the class name of the object instance being inspected by ObjectSpy
	 *
	 * @access public
	 * @return string Returns the class name as a string value
	 */
	public function getClassName()
	{
		return $this->object_class_name;
	}

	/**
	 * Retrieve the parent class name of the object
	 *
	 * Returns the parent class name of the object instance being inspected by ObjectSpy
	 *
	 * @access public
	 * @return string Returns the parent class name as a string value
	 */
	public function getParentName()
	{
		return $this->object_parent_name;
	}

	/**
	 * Retrieve the methods in the object
	 *
	 * Returns all defined methods of the object instance being inspected by ObjectSpy
	 *
	 * @access public
	 * @return array Returns the array of method names
	 */
	public function getMethods()
	{
		return $this->object_methods;
	}

	/**
	 * Check to see if a key exists in the data store
	 *
	 * Check for the existance of a specific key in the data store
	 * @access public
	 * @param  bool $defaults If true, then the the method will return the default class properties, otherwise current class properties and values will be returned
	 * @return array Associative array of object properties
	 */
	public function getProperties($defaults=false)
	{
		if ( !is_bool($defaults) ) { throw new ObjectSpyException("getProperties defaults parameter must be a boolean"); }
		if ($defaults) { return $this->object_default_vars; }
		else { return $this->object_vars; }
	}

	/**
	 * Determines if the given method name exists in the object
	 *
	 * @access public
	 * @param  string $method_name 
	 * @return bool True if the method name exists in the instance, otherwise false
	 */
	public function isMethod($method_name) {
		$retval = false;
		if ( !is_string($method_name) ) { throw new ObjectSpyException("isMethod method_name parameter must be a string"); }
		if (method_exists($this->object,$method_name)) {
			$retval = true;
		}
		return $retval;
	}

	/**
	 * Determines if the given property name exists in the object
	 *
	 * @access public
	 * @param  string $property_name 
	 * @return bool True if the property name exists in the instance, otherwise false
	 */
	public function isProperty($property_name) {
		$retval = false;
		if ( !is_string($property_name) ) { throw new ObjectSpyException("isProperty property_name parameter must be a string"); }
		if (property_exists($this->object,$property_name)) {
			$retval = true;
		}
		return $retval;
	}
}

/**
 * ObjectSpy Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class ObjectSpyException extends Exception {
	public function __construct($msg,$code=0) {
		parent::__construct($msg,$code);
	}
}
?>
