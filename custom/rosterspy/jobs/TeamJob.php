<?php
// =====================================================
// Class TeamJob
// Controls the fetch and update of team data
// =====================================================
class TeamJob extends CGJob {

  public function start($options = NULL) {
    parent::start($options);
    $this->logger->info("<<<<<<<<<<<<");
    $this->logger->info("<<<<<< " . strtoupper($this->job->job_type) . " Job ({$this->job->job_name}) Has Been Started >>");
    $this->logger->info("<<<<<<<<<<<<");

    // update cg_jobs table with updated status and exec date
    $this->updateJobStatus("RUNNING");
  }

  public function finish() {
    $this->logger->info(">>>>>>>>>>>>");
    $this->logger->info("<< Processing Has Been Completed for Job ({$this->job->job_name}) >>>>>>"); 
    $this->logger->info(">>>>>>>>>>>>");

    // update cg_jobs table with updated status and exec date
    $this->updateJobStatus("SUCCESS");
  }

  public function stop() {
    $this->logger->info(">>>>>>>>>>>>");
    $this->logger->info("<< Processing Has Been Stopped for Job ({$this->job->job_name}) >>>>>>"); 
    $this->logger->info(">>>>>>>>>>>>");

    // update cg_jobs table with updated status and exec date
    $this->updateJobStatus("ERROR");
  }

  public function run() {
    $conn = new DBConnection($GLOBALS['cli']->dbinfo);
    //print_r($this->job);
    
    // json decode the arguments
    $params = json_decode($this->job->job_params);

    // get the sport data
    $this->sport = $this->sportByName($params->sport); //print_r($sport);

    // build the teams url
    $team_url = $this->sport->sport_profile_url . "teams/"; //print_r($team_url);
    //$team_url = "http://www.gossipgrind.com";

    // get the full html text
    $full_text_query = 
      'use "http://www.datatables.org/data/htmlstring.xml" as htmlstring; select * from htmlstring where url="'. $team_url .'"';
    $post_full_text = Fetch::withYQL($full_text_query);

    //print_r($post_full_text); 

    $team_list = $this->parseTeams(trim($post_full_text->contents->query->results->result)); //print_r($team_list);

    // loop over the teams and save them
    foreach ($team_list as $team) {
      $team->sport_id = $this->sport->sport_id; 
      $team->last_update = date('Y-m-d H:i:s');

      $table = new TeamsTable($conn);

      // check exists
      $check_results = $table->select()->
        where("yahoo_tid = '" . $team->yahoo_tid . "'")->
        andWhere("sport_id = {$this->sport->sport_id}")->run(TRUE);
      if ($check_results->count() == 1) {
        $team_id = $check_results[0]->team_id;
        // do update
        $results = $table->update( (array) $team )->
          where("team_id = {$team_id}")->
          andWhere("sport_id = {$this->sport->sport_id}")->run(TRUE);
      }
      else {
        $results = $table->insert( (array) $team )->run(TRUE);
      }
    }
  }

  private function parseTeams($html) {
    $team_list = array();
    // get every h4 with an anchor tag in it
    if (strlen($html) > 0) {
      // create dom document
      $dom = new DomDocument();
      // load the html
      $dom->loadHTML($html);
      // prepare for xpath queries
      $xpath = new DOMXPath($dom);
      // query
      $results = $xpath->query("//dd/h4/a"); //print_r($results);
      foreach($results as $obj) {
        $team = new StdClass();

        $name = $this->getTeamName($obj->textContent);

        $team->team_name = trim($obj->textContent);
        $team->team_hash = md5( trim($obj->textContent) );

        $team->team_location = trim($name['loc']);
        $team->team_mascot = trim($name['mascot']);

        if ($obj->hasAttribute('href')) {
          $team->team_profile_url = dirname($this->sport->sport_profile_url) . $obj->getAttribute('href');
          $team->team_roster_url = $team->team_profile_url . "roster";
          $team->team_sched_url = $team->team_profile_url . "schedule";
          $team->team_stats_url = $team->team_profile_url . "stats";
          $team->yahoo_tid = basename($team->team_profile_url);
        }
        $team_list[] = $team;
      }
    }
    return $team_list;
  }

  private function getTeamName($text) {
    // break name up by spaces
    $token_list = explode(' ',$text);
    if (count($token_list) == 2) {
      return array('loc'=>$token_list[0], 'mascot'=>$token_list[1]); 
    }
    else if (count($token_list) == 3) {
      return array('loc'=>$token_list[0] . " " . $token_list[1], 'mascot'=>$token_list[2]); 
    }
    else if (count($token_list) == 4) {
      return array('loc'=>$token_list[0] . " " . $token_list[1], 'mascot'=>$token_list[2] . " " . $token_list[3]); 
    }
    else {
      return array('loc'=>$text);
    }
  }

  private function sportByName($sport_name) {
    $retval = NULL;
    $conn = new DBConnection($GLOBALS['cli']->dbinfo);
    // create sql executor instance
    $table = new SportsTable($conn);
    // run query
    $results = $table->select()->where("sport_name = '{$sport_name}'")->run(TRUE);
    if (count($results) == 1) {
      $retval = $results[0];
    }
    else {
      throw Exception("No sport found with that name ({$sport_name})");
    }
    return $retval;
  }

  // ========================================================
  // Function updateJobStatus
  // Updates a job's execution status and execution date
  // ========================================================
  private function updateJobStatus($status) {
    $conn = new DBConnection($GLOBALS['cli']->dbinfo);
    // create sql executor instance
    $table = new CgJobsTable($conn);
    // create associative array with update values
    $data = array(
      "job_exec_status" => $status,
      "job_exec_date" => date('Y-m-d H:i:s')
    );
    // execute the update
    $result = $table->update($data)->where('job_id = ' . $this->job->job_id)->run();
  }
}
?>
