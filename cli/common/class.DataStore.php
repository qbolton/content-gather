<?php
/**
 * Data storage class
 *
 * @package DooFramework
 * @subpackage common.util
 */

/**
 * Data storage class providing functionality to get/set values
 *
 * This class will provide a convenient mechanism to create a data storage
 * object with methods to get and set values with the object.
 *
 * @package DooFramework
 * @subpackage common.util
 */
class DataStore implements Countable {
    
    /**
     * Data store for values stored in the object
     * @access private
     * @var array Internal storage for data
     */
    private $_vars = array();

    /**
     * DataStore Constructor
     *
     * Initialize a new DataStore
     * @access public
     * @param array Array of values to initialize the data store object with

     */
    public function __construct($vars = array()) {
        $this->import($vars);
    }

    /**
     * Import an array of key.value pairs into a datastore
     *
     * @access public
     * @param array Array of key/value pairs to import into this datastore instance
     */
    public function import($vars) {
        if ( !is_array($vars) ) { throw new DataStoreException("Constructor parameter must be an array"); }
        foreach ( $vars as $key => $value ) {
            $this->set($key,$value);
        }
    }

    /**
     * Retrieve a value from the object
     *
     * Gets a specific value from the object.  If no name is specified then
   * an array of all values will be returned.  If the passed name cannot be
     * found then the method will return null.
     *
     * @access public
     * @param string The key of the value to retrieve
     * @return mixed Returns the value specified, or an array of all values.  If the
     * key is not found then null will be returned.
     */
    public function get($key=null,$asObj=false)
    {
        if ( isset($key) ) {
            if ( !is_string($key) ) { throw new DataStoreException("Data key is invalid"); }
            if ( isset($this->_vars[$key]) ) {
                if ($asObj) {
                    return is_array($this->_vars[$key]) ? (object) $this->_vars[$key] : $this->_vars[$key];
                }
                else {
                    return $this->_vars[$key];
                }
            }
            else {
                return null;
            }
        }
        else {
            if ( $asObj ) {
                return (object) $this->_vars;
            }
            else {
                return $this->_vars;
            }
        }
    }


    /**
     * Set a value in the object
     *
     * Stores a specific value in the object.  If a value already exists in the
     * controller with the same name then it will be overwritten.
     *
     * @access public
     * @param string The key which will be used to access the stored value
     * @param mixed The value to be stored, or null to remove a data store entry
     * @return void
     * @throws DataStoreException
     */
    public function set($key,$val)
    {
        if ( !$key || !is_string($key) ) { throw new DataStoreException("Data key is invalid"); }
        if ( is_null($val) ) {
             unset($this->_vars[$key]);
        }
        else {
            $this->_vars[$key] = $val;
        }
    }

    
    /**
     * Check to see if a key exists in the data store
     *
     * Check for the existance of a specific key in the data store
     * @access public
     * @param string The key which will be used checked
     * @return bool True if the key exists, false otherwise
     * @throws DataStoreException
     */
    public function exists($key)
    {
        if ( !$key || !is_string($key) ) { throw new DataStoreException("Data key is invalid"); }
        return isset($this->_vars[$key]);
    }

    /**
     * Check to see if a key in the data store is empty or not
     *
     * @access public
     * @param string The key which will be used checked
     * @return bool True if the key is empty, false otherwise
     * @throws DataStoreException
     */
    public function isEmpty($key) {
        if ( !$key || !is_string($key) ) { throw new DataStoreException("Data key is invalid"); }
        return empty($this->_vars[$key]);
    }

    /** =====================================================
        Countable Interface Methods
     ** =====================================================*/

    /** 
     * Returns the number of items stored in the DataStore instance
     *
     * Example: count($datastore_object); and $datastore_object->count(); will return the same thing
     *
     * @access public
     * @return integer
     */
    public function count() {
        return count($this->_vars);
    }
}
/**
 * DataStore Exception class
 * @package DooFramework
 * @subpackage DooFramework.Exceptions
 */
class DataStoreException extends Exception {
    public function __construct($msg,$code=0) {
        parent::__construct($msg,$code);
    }
}
?>
