<?php
/**
 * Define base database functionality for the cg_taxonomy table 
 *
 * Defines the base CgTaxonomy class 
 *
 */

/**
 * CgTaxonomyTable base class
 */
class CgTaxonomyTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_taxonomy";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'site_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'pid','primary_key'=>FALSE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'tagid','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = NULL;	

	/**
	 * Contructor for CgTaxonomyTable class
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
