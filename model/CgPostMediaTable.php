<?php
/**
 * Define base database functionality for the cg_post_media table 
 *
 * Defines the base CgPostMedia class 
 *
 */

/**
 * CgPostMediaTable base class
 */
class CgPostMediaTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_post_media";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'pmid','primary_key'=>FALSE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'pid','primary_key'=>TRUE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'site_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'media_type','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'5','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'media_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'embed_type','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'6','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'media_src','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'media_attributes','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'media_html','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'media_status','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'7','numeric_precision'=>'','default'=>'active','is_nullable'=>'NO'),
	          array('name'=>'media_width','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'media_height','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'local_path','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "pid";	

	/**
	 * Contructor for CgPostMediaTable class
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
