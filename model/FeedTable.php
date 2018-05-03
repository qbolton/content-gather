<?php
/**
 * Define base database functionality for the feed table 
 *
 * Defines the base Feed class 
 *
 */

/**
 * FeedTable base class
 */
class FeedTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "feed";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'fid','primary_key'=>TRUE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'site_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'feed_url','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'directives','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'status','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'7','numeric_precision'=>'','default'=>'test','is_nullable'=>'NO'),
	          array('name'=>'handler_class','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'fetch_method','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'YQLExecutor','is_nullable'=>'NO'),
	          array('name'=>'fetch_interval','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'1','is_nullable'=>'NO'),
	          array('name'=>'article_count','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'feed_rank','primary_key'=>FALSE,'data_type'=>'double','max_length'=>'','numeric_precision'=>'22','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'fetch_status','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'n/a','is_nullable'=>'NO'),
	          array('name'=>'fetch_date','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'last_update','primary_key'=>FALSE,'data_type'=>'timestamp','max_length'=>'','numeric_precision'=>'','default'=>'CURRENT_TIMESTAMP','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "fid";	

	/**
	 * Contructor for FeedTable class
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
