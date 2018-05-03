<?php
/**
 * Define base database functionality for the cg_tags table 
 *
 * Defines the base CgTags class 
 *
 */

/**
 * CgTagsTable base class
 */
class CgTagsTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_tags";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'tagid','primary_key'=>TRUE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'site_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'count','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'percentage','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'tag_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'tag_slug','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "tagid";	

	/**
	 * Contructor for CgTagsTable class
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
