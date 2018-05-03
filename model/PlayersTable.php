<?php
/**
 * Define base database functionality for the players table 
 *
 * Defines the base Players class 
 *
 */

/**
 * PlayersTable base class
 */
class PlayersTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "players";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'player_id','primary_key'=>TRUE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_id','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'yahoo_pid','primary_key'=>FALSE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_hash','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_position','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_height','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'8','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_weight','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_age','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_exp','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_college','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_number','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_salary','primary_key'=>FALSE,'data_type'=>'float','max_length'=>'','numeric_precision'=>'12','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_profile_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_bat','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'5','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_throw','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'5','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_pob','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_status','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'YES'),
	          array('name'=>'last_update','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_id','primary_key'=>TRUE,'data_type'=>'int','max_length'=>'','numeric_precision'=>'10','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_id','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_hash','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_position','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_height','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'8','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_weight','primary_key'=>FALSE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_age','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_exp','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_college','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_number','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_salary','primary_key'=>FALSE,'data_type'=>'float','max_length'=>'','numeric_precision'=>'12','default'=>'0','is_nullable'=>'NO'),
	          array('name'=>'player_profile_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'player_bat','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'5','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_throw','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'5','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_pob','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'player_status','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'0','is_nullable'=>'YES'),
	          array('name'=>'last_update','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "player_id";	

	/**
	 * Contructor for PlayersTable class
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
