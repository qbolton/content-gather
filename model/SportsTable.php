<?php
/**
 * Define base database functionality for the sports table 
 *
 * Defines the base Sports class 
 *
 */

/**
 * SportsTable base class
 */
class SportsTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "sports";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'sport_id','primary_key'=>TRUE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_profile_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_desc','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'sport_id','primary_key'=>TRUE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_profile_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_desc','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "sport_id";	

	/**
	 * Contructor for SportsTable class
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
