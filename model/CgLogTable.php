<?php
/**
 * Define base database functionality for the cg_log table 
 *
 * Defines the base CgLog class 
 *
 */

/**
 * CgLogTable base class
 */
class CgLogTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_log";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'logid','primary_key'=>TRUE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'jobid','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'job_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'job_message','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'job_status','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'8','numeric_precision'=>'','default'=>'ERROR','is_nullable'=>'NO'),
	          array('name'=>'job_date','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "logid";	

	/**
	 * Contructor for CgLogTable class
	 *
	 * @access public
	 * @param DBConnection $connection Valid instance of the DBConnection class.  This parameter is optional
	 * @return void
	 */
	public function __construct($connection=null) { 
		parent::__construct($connection);
	}
}
?>
