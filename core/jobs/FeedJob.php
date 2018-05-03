<?php
class FeedJob extends CGJob {

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
    //print_r($this->job);

    // get all active feed items that are past their fetch interval
    $feeds = $this->getFeeds(); 

    // loop over the feeds that should be run 
    foreach ($feeds as $feed) {
      $loop_counter = 1;
      //print_r($feed);

      $site_id = $feed['site_id'];
      $feed_id = $feed['fid'];
      $feed_url = $feed['feed_url'];
      $feed_type = $feed['feed_type'];
      $feed_handler = NULL;

      $this->logger->info("===========================================================");
      $this->logger->info("Opening feed for \"" . $feed['feed_name'] . "\"({$feed_id})");
      $this->logger->info("===========================================================");

      // if there is a feed handler class that exists for the feed, then create new instance
      if (!is_null($feed['feed_class'])) {

        $this->logger->info("Loading feed handler \"" . $feed['feed_class'] . "\"");

        $feed_handler = new $feed['feed_class'] (); 
        // add object_spy to it
        $feed_handler->os = new ObjectSpy($feed_handler);
      }
         
      // fetch each feed with yql
      $raw_json = $this->getWithYQL($feed_url,$feed_type,FALSE);

      // if page was successful the proceed with processing of feed
      if ( (!is_null($raw_json)) && (is_object($raw_json)) && ($raw_json->success) ) {
        // get the item/post/entry array
        $feed_items = $this->getItemsFromFeedXML($raw_json->contents,$feed_type);

        // loop over the incoming posts 
        foreach($feed_items as $raw_feed_item) {

          // exit process loop if fetch_cap is reached
          if ($loop_counter == $feed['fetch_cap']) {
            $this->logger->info("Fetch_Cap reached.  Breaking out of feed item loop.");
            break;
          }

          // things to add to raw_feed_item
          $raw_feed_item->site_id = $site_id;
          $raw_feed_item->fid = $feed_id;

          //print_r($raw_feed_item);
          $this->logger->debug( print_r($raw_feed_item,TRUE) );

          // instantiate object to parse the feed item
          $post = NULL;
          if (strcasecmp($feed_type,'RSS') == 0) {
            $this->logger->debug("Creating new RSSParser Object");
            $post = new RSSParser($raw_feed_item);
          }
          else if (strcasecmp($feed_type,'ATOM') == 0) {
            $this->logger->debug("Creating new ATOMParser Object");
            $post = new AtomParser($raw_feed_item);
          }
          else {
            $this->logger->error("Feed_Type ({$feed_type}) NOT Recognized");
            throw Exception("Feed_Type ({$feed_type}) NOT Recognized");
          }

          // break the feed item up into it's meta detail fields
          $post->parse();

          // ================================================
          // if the feed handler is set then see if a post method exists
          // ================================================
          if (is_object($feed_handler)) {

            if ($feed_handler->os->method_exists('parse_post')) {
              $post = $feed_handler->parse_post($raw_feed_item,$post);
            }

            // ================================================
            // if feed handler exists for parsing fulltext 
            // ================================================
            if ($feed_handler->os->method_exists('parse_fulltext')) {
              // get the full html text
              $full_text_query = 
                'use "http://www.datatables.org/data/htmlstring.xml" as htmlstring; select * from htmlstring where url="'. $post->post_url .'"';
              $post_full_text = Fetch::withYQL($full_text_query);

              // process the full text with rss_full_text software
              //FullText::getMetaTitle( trim($post_full_text->contents->query->results->result) ); exit;

              //print_r($post_full_text); 
              $post = $feed_handler->parse_fulltext($post, trim($post_full_text->contents->query->results->result) );
            }
          }

          // ================================================
          // RUN ANY GLOBAL MODULES FOR THIS JOB
          // ================================================
          if (!is_null($this->job->job_modules)) {
            // break the modules up into separate items
            $job_modules = explode(",",$this->job->job_modules);
            // loop over the potential modules and make it happen
            foreach($job_modules as $module) {
              $module_obj = new $module ($post);
              $post = $module_obj->run();
            }
          }

          // ================================================
          // DETERMINE IF THE POST SHOULD BE EXCLUDED
          // ================================================
          $exclude = $this->excludePost( $feed,$post );
          if (strcasecmp($exclude,"SIMPLE_EXCLUDE") == 0) {
            continue;
          else if (strcasecmp($exclude,"ITEM_AGE_EXCLUDE") == 0) {
            // skip the remainder of the feed
            // WHY? Because we are assuming that the feeds are listed
            // in chronological order.  So if we are skipping this item
            // because it's too old, then the rest of them are also
            $this->logger->warning("Skipping remaining " . $feed['feed_name'] . " feed items because we are assuming they are too old");
            break;
          }
          else {
          // ================================================
          // ATTEMPT TO SAVE ALL POST PIECES
          // ================================================
            try {
              $post_id = 0;
              // The we want to save the post
              $this->logger->info("Saving post " . $post->vars->post_title);
              $post_id = $post->savePost();
              $this->logger->info("Post saved with ID " . $post_id);
            }
            catch (Exception $e) {
              // possibly add error queue
              // add errors to error stack
              // $this->errors->add($e,'database');
              $this->logger->error($e->getMessage());
            }
          }
          
          // increment counter loop
          $loop_counter++;
        }

        // update feed status
        Feed::update( array('fid'=>$feed_id,'fetch_status'=>'SUCCESS','fetch_date'=>date('Y-m-d H:i:s')) );

        // feed messages, statistics, etc
        $this->logger->info("The feed for \"" . $feed['feed_name'] . "\"({$feed_id}) has been completed");

      }
      else {

        Feed::update( array('fid'=>$feed_id,'fetch_status'=>'ERROR','fetch_date'=>date('Y-m-d H:i:s')) );

        $this->logger->warning("###################################################");
        $this->logger->warning( print_r($raw_json,TRUE) );
        $this->logger->warning("The feed for \"" . $feed['feed_name'] . "\"({$feed_id}) has been closed");
        $this->logger->warning("###################################################");
      }
    }
  }

  // ========================================================
  // Function getFeeds
  // This function grabs a list of feeds eligible for the job 
  // run
  // ========================================================
  private function getFeeds($num_feeds=10) {
    // Run option variable
    $run_fid = 0;
    $query_stmt = NULL;
    // get database connection
    $conn = new DBConnection($GLOBALS['cli']->dbinfo);
    // create sql executor instance
    $query = new SQLExecutor($conn);

    if (!is_null($this->options)) {
      if ($this->options->exists('run_fid')) {
        $run_fid = $this->options->get('run_fid');
      }
    }

    // setup feed fetch query
    if ($run_fid > 0) {
      $query_stmt = "SELECT * FROM cg_feeds WHERE feed_status = 'active'" .
      " AND fid = {$run_fid}" . 
      " AND feed_type IN ('rss','atom')" .
      " AND site_id = " . $this->job->site_id;
    }
    else {
      $query_stmt = "SELECT * FROM cg_feeds WHERE feed_status = 'active'" .
      " AND fetch_date < DATE_SUB(NOW(), INTERVAL fetch_interval HOUR)" .
      " AND feed_type IN ('rss','atom')" .
      " AND site_id = " . $this->job->site_id;
    }
    
    // run feed fetch query
    $results = $query->sql($query_stmt . " ORDER BY fetch_date ASC LIMIT " . $num_feeds)->run();

    // Output what's gonna be processed
    foreach ($results as $r) {
      $this->logger->debug($r['feed_name'] . " will be fetched for processing");
    }

    // close the database connection
    $conn->close();
    return $results;
  }

  // ========================================================
  // Function getWithYQL
  // Handles the yahoo query language call to pull in the 
  // specified feed url
  // ========================================================
  private function getWithYQL($feed_url,$feed_type,$normalize=TRUE) {
    $yahoo = NULL;
    $yql_query = NULL;
    // setup yql directives
    $yql = new StdClass();
    $yql->yql_method = 'GET';
    $yql->yql_request_type = 'public';
    $yql->yql_options = array('format'=>'json');

    // setup feed type
    //if ($feed_type) {

    $this->logger->debug("Fetching " . $feed_type . " feed from " . $feed_url); 

    // setup actual yql query
    $yql_query = "SELECT * FROM feed WHERE url = '{$feed_url}'";

    $yahoo = new YQLExecutor($yql);
    // issue query
    $yahoo->yql($yql_query);
    // pull the feed via YQL
    $json = $yahoo->run();

    return $json;
  }

  // ========================================================
  // Function getItemsFromFeedXML
  // returns the array of feed items/entries/posts to loop
  // over and process
  // ========================================================
  private function getItemsFromFeedXML($contents,$feed_type) {
    $items = NULL;

    if (strcasecmp($feed_type,'RSS') == 0) {
      //$this->logger->log( print_r($contents,TRUE) ); 
      // verify the results array
      if ( (isset($contents->query->results->item)) && (count($contents->query->results->item) > 0) ) {
        $items = $contents->query->results->item;
      }
    }
    else if (strcasecmp($feed_type,'ATOM') == 0) {
      $this->logger->warning( print_r($contents,TRUE) ); 
      $this->logger->warning( "We are now exiting the main process..." ); 
      $this->updateJobStatus("ERROR");
      exit(0);
    }
    else {
      $this->logger->warning( print_r($contents,TRUE) ); 
      $this->logger->warning( "We are now exiting the main process..." ); 
      $this->updateJobStatus("ERROR");
      exit(0);
    }

    return $items; 
  }

  // ========================================================
  // Function excludePost
  // sets rules for skipping incoming posts
  // Pass $post by reference
  // ========================================================
  private function excludePost($feed,$post) {
    $retval = "NO_EXCLUDE";
    if ($post->exclude) {
      $retval = "SIMPLE_EXCLUDE";
    }

    // -----------------------------------------------------
    // setup up global exclusion rules for a FEEDJOB
    // -----------------------------------------------------

    // check to see if the article pub_date is young enough to proceed
    if ($this->options->exists('acceptable_item_age')) {
      $acceptable_item_age = $this->options->get('acceptable_item_age');
    }
    else {
      $acceptable_item_age = "2 days ago";
    }
    if (strtotime($acceptable_item_age) >= $post->vars->post_pubdate_int) {
      $this->logger->info("The article located here: " . $post->vars->post_url . " is older than what we need");
      $retval = "ITEM_AGE_EXCLUDE";
    }

    // automatically exclude any item that has a domain not on the feed domain
    $item_domain = str_replace('www.','',UrlKit::getDomain($post->vars->post_url,FALSE));
    $feed_domain = str_replace('www.','',UrlKit::getDomain($feed['web_url'],FALSE));
    if ( strcasecmp($item_domain,$feed_domain) != 0) {
      $this->logger->info($post->vars->post_url . " is not on feed source domain");
      $retval = "SIMPLE_EXCLUDE";
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
