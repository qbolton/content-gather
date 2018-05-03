<?php
/**
 * Define base database functionality for the cg_feeds table 
 *
 * Defines the base CgFeeds class 
 *
 */

/**
 * CgFeedsTable base class
 */
class CgFeedsTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_feeds";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'fid','primary_key'=>TRUE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'site_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'post_count','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'fetch_cap','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'fetch_interval','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'YES'),
	          array('name'=>'feed_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'feed_class','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'feed_title','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'web_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'feed_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'feed_type','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'6','numeric_precision'=>'','default'=>'rss','is_nullable'=>'NO'),
	          array('name'=>'feed_rank','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'feed_desc','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'feed_image','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'feed_status','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'7','numeric_precision'=>'','default'=>'test','is_nullable'=>'NO'),
	          array('name'=>'fetch_status','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'fetch_date','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "fid";	

	/**
	 * Contructor for CgFeedsTable class
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
