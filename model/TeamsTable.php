<?php
/**
 * Define base database functionality for the teams table 
 *
 * Defines the base Teams class 
 *
 */

/**
 * TeamsTable base class
 */
class TeamsTable extends DBTable {
	/** 
	 * @access protected
	 * @var string String that contains the table name unto which the SQL operations will be applied
	 */
	protected $TABLENAME = "teams";	

	/** 
	 * @access protected
	 * @var array Associative array data structure containing table column specific information
	 */
	protected $COLUMNINFO = array(
	          array('name'=>'team_id','primary_key'=>TRUE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'yahoo_tid','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'5','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_name','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_hash','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_location','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_mascot','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_logo_path','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'team_profile_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_roster_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_sched_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_stats_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'last_update','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_id','primary_key'=>TRUE,'data_type'=>'smallint','max_length'=>'','numeric_precision'=>'5','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'sport_id','primary_key'=>FALSE,'data_type'=>'tinyint','max_length'=>'','numeric_precision'=>'3','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_hash','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_location','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'128','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_mascot','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'64','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_logo_path','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'YES'),
	          array('name'=>'team_profile_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'team_roster_url','primary_key'=>FALSE,'data_type'=>'varchar','max_length'=>'255','numeric_precision'=>'','default'=>'','is_nullable'=>'NO'),
	          array('name'=>'last_update','primary_key'=>FALSE,'data_type'=>'datetime','max_length'=>'','numeric_precision'=>'','default'=>'','is_nullable'=>'NO')
	);	

	/** 
	 * @access protected
	 * @var string Primary key for the table
	 */
	protected $PRIMARYKEY = "team_id";	

	/**
	 * Contructor for TeamsTable class
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
