<?php
/**
 * Define base database functionality for the cg_relations table 
 *
 * Defines the base CgRelations class 
 *
 */

/**
 * CgRelationsTable base class
 */
class CgRelationsTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_relations";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'relation_id','primary_key'=>TRUE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'site_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'pid','primary_key'=>FALSE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'related_pid','primary_key'=>FALSE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'relation_type','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'15','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'relevance','primary_key'=>FALSE,'data_type'=>'double','max_length'=>'','numeric_precision'=>'22','default'=>'0','is_nullable'=>'YES')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "relation_id";	

	/**
	 * Contructor for CgRelationsTable class
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
