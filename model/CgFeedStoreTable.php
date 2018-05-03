<?php
/**
 * Define base database functionality for the cg_feed_store table 
 *
 * Defines the base CgFeedStore class 
 *
 */

/**
 * CgFeedStoreTable base class
 */
class CgFeedStoreTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_feed_store";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'site_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'fid','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'feed_body','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'checksum','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'last_update','primary_key'=>FALSE,'data_type'=>'timestamp','max_length'=>'','numeric_precision'=>'','default'=>'CURRENT_TIMESTAMP','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = NULL;	

	/**
	 * Contructor for CgFeedStoreTable class
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
