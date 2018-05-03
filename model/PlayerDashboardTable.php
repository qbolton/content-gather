<?php
/**
 * Define base database functionality for the player_dashboard table 
 *
 * Defines the base PlayerDashboard class 
 *
 */

/**
 * PlayerDashboardTable base class
 */
class PlayerDashboardTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "player_dashboard";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'player_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_id','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_id','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_id','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = NULL;	

	/**
	 * Contructor for PlayerDashboardTable class
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
