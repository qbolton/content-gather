<?php
/**
 * Define base database functionality for the cg_sites table 
 *
 * Defines the base CgSites class 
 *
 */

/**
 * CgSitesTable base class
 */
class CgSitesTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "cg_sites";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'site_id','primary_key'=>TRUE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'version','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'12','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'purge_interval','primary_key'=>FALSE,'data_type'=>'bigint','max_length'=>'','numeric_precision'=>'19','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'purge_type','primary_key'=>FALSE,'data_type'=>'enum','max_length'=>'4','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'user_agent','primary_key'=>FALSE,'data_type'=>'text','max_length'=>'65535','numeric_precision'=>'','default'=>'','is_nullable'=>'YES')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "site_id";	

	/**
	 * Contructor for CgSitesTable class
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
