<?php
/**
 * Define base database functionality for the positions table 
 *
 * Defines the base Positions class 
 *
 */

/**
 * PositionsTable base class
 */
class PositionsTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "positions";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'pos_id','primary_key'=>TRUE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_abbrev','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'12','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_alts','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_notes','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'pos_id','primary_key'=>TRUE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_abbrev','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'12','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_alts','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'position_notes','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "pos_id";	

	/**
	 * Contructor for PositionsTable class
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
