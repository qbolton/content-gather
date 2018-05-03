#!/usr/bin/php
<?php
define("CONTROLLER_CLASS_NAME","GatherDoo");

//=================================================
// Pathing and file locale constants
//=================================================
define("SCRIPTS_PATH",dirname(__FILE__));
define("CURRENT_SCRIPT_PATH",dirname(__FILE__));
define("ROOT_PLUGIN_PATH",dirname(SCRIPTS_PATH));

//================================================
// include configuration files
//=================================================
require(ROOT_PLUGIN_PATH . "/config.php");

// adding in thumbnail creation
require(ROOT_PLUGIN_PATH . "/simplehtmldom/simple_html_dom.php");
require(ROOT_PLUGIN_PATH . "/readability/Readability.php");

//=================================================
// Gather configuration class
//=================================================
class GatherConfig {
  public function __construct() {
    $this->bot = "gather-2.0.0";
    $this->version = '2.0.0';
    $this->user_agent = 'Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/20121223 Ubuntu/9.25 (jaunty)
    Firefox/3.8';
    // acceptable directives
    // render => JSON,RSS,ATOM
    // feed_is_source => TRUE/FALSE, defaults to TRUE
    // 
    $this->directives = array('render'=>'JSON');
    // ============================
    // behavioral options
    // ============================
    $this->sleep = 2;
    $this->enable_cache = FALSE;
    $this->purge_interval = 365;
    $this->purge_type = "delete";
  }
}

//=================================================
// Gather controller class
//=================================================
class GatherDoo {
  public $process_name = 's.gather.php';
  public $cli = NULL;

  public function main() {
    $this->cli = $cli = $GLOBALS['cli'];

    // check for help arg
    if ( (count($_SERVER['argv']) == 1) || ($this->cli->script->flagExists("h")) ) {
      print "<USAGE> " . $_SERVER['argv'][0] . " --fid=<int(csv list)> <-v|-s> -h -d\n\n";
      exit(0);
    }

    // Test for debug execution flag
    if ($this->cli->script->flagExists('d')) {
      $this->debug_exec = TRUE;
      $this->feed_status = "test";
    }
    else {
      $this->debug_exec = FALSE;
      $this->feed_status = "active";
    }

    // Test for logging verbosity flag
    $this->report_level = cLogger::NORMAL;
    if ($this->cli->script->flagExists('v')) { $this->report_level = cLogger::VERBOSE; }
    if ($this->cli->script->flagExists('s')) { $this->report_level = cLogger::SILENT; }

    // create the logger
    $this->logger = new cLogger( 
      array('process_name' => $this->process_name,
      'function'=>__METHOD__,
      'out_to_screen' => TRUE,
      'message_level' => cLogger::INFO,
      'report_level' => $this->report_level,
      'show_process_name' => FALSE
      ) 
    );

    // create the local script gather config
    $this->config = new GatherConfig();

    // announce debug mode
    if ($this->debug_exec) {
      $this->logger->log('Executing in DEBUG mode');
    }
    
    // register the name of the crawler
    $feed_table = new FeedTable($cli->dbconn);
    if ($this->cli->script->optionExists("fid") && (strlen($this->cli->script->option("fid")->asString()) > 0) ) {
      // if comma present
      $feed_ids = explode(',',$this->cli->script->option("fid")->asString());
      if (count($feed_ids) > 1) {
        $feeds = $feed_table->select()->where("status='".$this->feed_status."'")->andWhere("fid IN (" . implode(',',$feed_ids) . ")")->run(TRUE);
      }
      else if (count($feed_ids) == 1) {
        $feeds = $feed_table->select()->where("status='".$this->feed_status."'")->andWhere("fid=" . $feed_ids[0])->run(TRUE);
      }
      else {
        print "You did not include any feed ids in your arguments\n\n";
        exit(0);
      }
    }    
    else {    
      // create sql statement
      $feeds = $feed_table->select()->where("status='active'")->run(TRUE);
    }

    $this->logger->vars->set('function',__METHOD__);

    // if sources are returned then try to process the feeds
    if ($feeds->count() > 0) {
      // loop over and get feeds
      foreach ($feeds as $feed) {
        $page = NULL;

        if (!is_null($feed->directives)) {
          $directives = json_decode($feed->directives);
        }
        else {
          // set default directives
          $directives = (object) $this->config->directives;
        }

        // set some directive flags
        if (!isset($directives->feed_is_source)) { $directives->feed_is_source = TRUE; }

        $url = $feed->feed_url;

        // =================================================================
        $this->logger->log("========================================================================");
        // =================================================================
         
        if (stristr($feed->fetch_method,"YQLExecutor")!==FALSE) {
          // try to get the actual table to pull from
          $yql_query = "";
          $method = explode("|",$feed->fetch_method);
          if ( (isset($method[1])) && (strcasecmp($method[1],"feed")==0) ) {
            $yql_query = "SELECT * FROM feed WHERE url = '{$url}'";
          }
          if ( (isset($method[1])) && (strcasecmp($method[1],"feednormalizer")==0) ) {
            $yql_query = "SELECT * FROM feednormalizer WHERE url = '{$url}' AND output = 'rss_2.0'";
          }
          else {
            $yql_query = "SELECT * FROM feednormalizer WHERE url = '{$url}' AND output = 'rss_2.0'";
          }
     
          // setup config variables
          $this->config->yql = new StdClass();
          $this->config->yql->yql_method = 'GET';
          $this->config->yql->yql_request_type = 'public';
          $this->config->yql->yql_options = array('format'=>'json');

          $yahoo = new YQLExecutor($this->config->yql);
          // issue query
          $yahoo->yql($yql_query);
          // pull the feed via YQL
          $page = $yahoo->run(); 

          // if there was a fetch error...try another query
          if (!$page->success) {
            $yahoo->yql("SELECT * FROM feed WHERE url = '".$url."'");
            $page = $yahoo->run();
          }
        }
        else {
          $fetch_method = new $feed->fetch_method ($this->config,$feed);
          $fetch_method->options($directives);
          $page = $fetch_method->run();
        }

        // if page was successful the proceed with processing of feed
        if ( (!is_null($page)) && (is_object($page)) && ($page->success) ) {
          $this->logger->log("Fetched feed: " . $feed->name . " successfully",cLogger::INFO);

          // log feed status
          $this->updateFeedStatus(
            array(
              'fid'=>$feed->fid,
              'fetch_status'=>'SUCCESS',
              'fetch_date'=> date("Y-m-d H:i:s")
            )
          );

          if (strcasecmp($directives->render,'json') == 0) {
            $data = $this->reconcileJSON($feed,$page->contents); 
          }
          else if (strcasecmp($directives->render,'rss') == 0) {
            $data = $this->reconcileRSS($feed,$page->contents);
          }
          else if (strcasecmp($directives->render,'atom') == 0) {
            $data = $this->reconcileATOM($feed,$page->contents);
          }
          else {
            $this->logger->log("Could not reconcile feed format for " . $url,cLogger::ERROR);
          }

          // check the number of feed items
          if ($data->count > 0) {
            $this->logger->log("Found " . $data->count . " items for " . $feed->name,cLogger::INFO);
            // loop over the items
            foreach ($data->items as $item) {
              // carry the feed id along
              $item->fid = $feed->fid;
              $item->feed_name = $feed->name;
              $item->feed_site_url = $feed->site_url;
              
              // =================================================================
              // assemble the data into a proper structure
              // =================================================================
              $this->article = $this->assembleItemMeta($item,$data->channel,$directives); 

              // if we need to skip the article based on information we've gathered
              if ($this->article->should_be_skipped) {
                $this->logger->vars->set('function',__METHOD__);
                $this->logger->log("<<<SKIPPING>>> " . $this->article->url,cLogger::NOTICE);
                // move on to next url
                continue;
              }

              // =================================================================
              // deal with the tags that were generated
              // =================================================================
              $this->article = $this->reconcileArticleTags($this->article,$item); 

              // =================================================================
              // Try to pull in the full text for this joint
              // =================================================================
              $this->article = $this->fetchArticleFullText($this->article); 

              // =================================================================
              // Grab media out of post data (images, video links and/or embed code)
              // =================================================================
              $this->article = $this->fetchMediaAssets($this->article);

              // =================================================================
              // save the article data
              // =================================================================
              if (!$this->debug_exec) {
                $result = $this->executeArticleSave($this->article); 
              }
              else {
                $result = new StdClass(); $result->status = TRUE; $result->aid = "000000";
              }

              $this->logger->vars->set('function',__METHOD__);
              if ($result->status) {
                $this->logger->log("[SUCCESS] Insert of article(".$result->aid.") title: " .  $this->article->title,cLogger::INFO);
              }
              else {
                $this->logger->log("[ERROR] Could not insert article data for fid(".$this->article->fid.") with url: " . $url,cLogger::NOTICE);
              }
            }
          }
          else {
            $this->logger->log("There are no items for " . $data->value->title,cLogger::NOTICE);
          }

          // marks the end of processing for a feed source
          $this->logger->log("########################################################################");

        }
        else {
          // log feed status
          // don't update fetch_date because we need to know how long problem has been going on
          //   'fetch_date'=> date("Y-m-d H:i:s")
          $this->updateFeedStatus(
            array(
              'fid'=>$feed->fid,
              'fetch_status'=>'ERROR',
              'fetch_date'=> date("Y-m-d H:i:s")
            )
          );
          $this->logger->log("Could not retrieve " . $url,cLogger::ERROR);
          $this->logger->log("Page details: " . print_r($page,TRUE),cLogger::ERROR);
        }
        // wait n seconds before retrieving next feed url
        sleep($this->config->sleep);
      }
    }
    else {
      $this->logger->log("There is no feed available",cLogger::NOTICE);
      print_r("There is no feed available\n");
    }
    $this->logger->log("<< Processing Has Completed >>");
  }

  // ======================================================
  // updateFeedStatus
  // ======================================================
  private function updateFeedStatus($updateables) {
    $feed = new FeedTable($GLOBALS['cli']->dbconn);
    $feed->update($updateables)->where("fid=".$updateables['fid'])->run();
  }

  // ======================================================
  // reconcileJSON
  // ======================================================
  private function reconcileJSON($feed,$contents) {
    $no_feed = FALSE;
    $data = new StdClass();

    // check to see if rss is present
    if (isset($contents->query->results->rss)) {
      // grab the channel information
      $data->channel = new StdClass();
      $data->channel->rss = new StdClass();
      $data->channel->rss->version = $contents->query->results->rss->version;
      $data->channel->title = $contents->query->results->rss->channel->title;
      $data->channel->link = $contents->query->results->rss->channel->link;
      $data->channel->description = $contents->query->results->rss->channel->description;
      
      // grab the array of post information
      if (isset($contents->query->results->rss->channel->item)) {
        $data->items = $contents->query->results->rss->channel->item; 
      }
      else {
        $this->logger->log(print_r($contents,TRUE),cLogger::ERROR); 
      }

      if (isset($data->items)) {
        // return a count of post items
        $data->count = count($data->items);
        //print_r($data); exit;
        $no_feed = FALSE;
      }
    }
    else if (isset($contents->query->results->item)) {
      $data->channel = new StdClass();
      $data->channel->rss = new StdClass();
      $data->channel->title = $feed->name;
      $data->channel->link = $feed->site_url;
      $data->channel->description = "";
      $data->items = $contents->query->results->item;
      $data->count = count($contents->query->results->item);
    }
    else {
      $this->logger->log("We did not find RSS for this feed source",cLogger::ERROR); 
      $this->logger->log(print_r($contents,TRUE),cLogger::ERROR); 
    }

    if ($no_feed) {
      $this->logger->log("We did not find RSS for this feed source",cLogger::ERROR); 
      $this->logger->log(print_r($contents,TRUE),cLogger::ERROR); 
    }

    return $data;
  }

  // ======================================================
  // assembleItemMeta
  // ======================================================
  private function assembleItemMeta($item,$channel,$directives=NULL) {
    $this->logger->vars->set('function',__METHOD__);

    $feed_domain = $item_domain = NULL;

    //print_r($item);
    $article = new StdClass();
    // set image
    $article->feed_site_url = $item->feed_site_url;
    $article->image = NULL;
    $article->video = NULL;
    $article->should_be_skipped = FALSE;

    // feed id
    $article->fid = $item->fid;
    // feed name
    $article->feed_name = $item->feed_name;

    $item->title = preg_replace("/\[([^\[\]]*+|(?R))*\]/","",$item->title);
    // title
    $article->title = ucwords( strtolower( TextHelper::cleanUTF8(trim($item->title)) ) ); 
    // clean the title up
    $article->seo_title = TextHelper::sanitize(trim($item->title));

    // get the original url if available, otherwise use link
    if (isset($item->origLink)) {
      if (isset($item->origLink->content)) {
        $article->url = trim($item->origLink->content);
      }
      else {
        $article->url = trim($item->origLink);
      }
    }
    else {
      $article->url = trim($item->link);
    }

    // automatically exclude any item that has a domain not on the feed domain
    $item_domain = str_replace('www.','',TextHelper::getDomain($article->url,FALSE));  
    $feed_domain = str_replace('www.','',TextHelper::getDomain($item->feed_site_url,FALSE));
    if ( strcasecmp($item_domain,$feed_domain) != 0) {
      $this->logger->log($article->url . " is not on feed source domain",cLogger::NOTICE);
      $article->should_be_skipped = TRUE;
    }

    // get assumed author/creator info
    if (isset($item->creator)) {
      $article->creator = trim($item->creator);
    }

    // check to see if url is a duplicate
    if (TextHelper::isDuplicateUrl($article->url,$this->cli->dbconn)) {
      $this->logger->log("URL already processed: " . $article->url);
      $article->should_be_skipped = TRUE;
      return $article;
    }
    else if (TextHelper::isDuplicate('seo_title',$article->seo_title,$this->cli->dbconn)) {
      $this->logger->log("URL already processed: " . $article->url);
      $article->should_be_skipped = TRUE;
      return $article;
    }
    else {
      $do_nothing = NULL;
    }

    // get the url hash
    $article->url_hash = TextHelper::hash( $article->url );
    // handle the description
    $html = new simple_html_dom();
    if (isset($item->description)) {
      if (is_array($item->description)) {
        $html->load(trim($item->description[1]));
      }
      else {
        $html->load(trim($item->description));
      }
      $article->excerpt = trim(TextHelper::cleanUTF8( $html->plaintext ));
    }
    else if (isset($item->encoded)) {
      $html->load(trim($item->encoded));
      $article->excerpt = trim(TextHelper::cleanUTF8( $html->plaintext ));
    }
    else {
      // set excerpt to null if we don't have one at this point.  This really shouldn't
      // happen.  We can fashion a workable excerpt from the fetched fulltext later if
      // we need to.
      $article->excerpt = NULL;
    }

    // check size of excerpt
    if ((is_null($article->excerpt)) || (strlen($article->excerpt) < 30)) {
      $this->logger->log("The article excerpt is too short");
      $article->should_be_skipped = TRUE;
      return $article;
    }
    
    // grab any encoded content
    if (isset($item->encoded)) {
      $article->encoded_content = $item->encoded;
    }

    // handle the publication date
    if (isset($item->pubDate)) {
      $article->pub_date_int = strtotime( $item->pubDate );
      $article->pub_date = date("Y-m-d H:i:s",$article->pub_date_int);
    }
    else {
      // how do you not put pubDate in your feed?
      // accomodate this foolishness by using the current date and time
      $article->pub_date_int = strtotime("now");
      $article->pub_date = date("Y-m-d H:i:s",$article->pub_date_int);
    }

    // if the publish time for the article is way in the future then skip the item
    $diff = time() - $article->pub_date_int;
    if ($diff < 0) {
      $article->should_be_skipped = TRUE;
      $this->logger->log("The article located here: " . $article->url . " has a future publish date");
      return $article;
    }

    // check to see if the article pub_date is young enough to proceed
    if (isset($directives->acceptable_item_age)) {
      $acceptable_item_age = $directives->acceptable_item_age;
    }
    else {
      $acceptable_item_age = "2 days ago";
    }
    if (strtotime($acceptable_item_age) >= $article->pub_date_int) {
      $this->logger->log("The article located here: " . $article->url . " is older than what we need");
      $article->should_be_skipped = TRUE;
      return $article;
    }

    // check to see if thumbnail is set for post
    if (isset($item->thumbnail)) {
      // create article image array
      $article->image = array();
      $article->image[0]['src'] = $item->thumbnail->url;
      $article->image[0]['width'] = ((isset($item->thumbnail->width)) ? $item->thumbnail->width : 0);
      $article->image[0]['height'] = ((isset($item->thumbnail->height)) ? $item->thumbnail->height : 0);
      $article->image[0]['alt'] = "";
      $article->image[0]['title'] = "";
    }

    // check to see if the item has comment rss (may be useful later)
    if (isset($item->commentRss)) {
      if (isset($item->commentRss->content)) {
        $article->comments_url = $item->commentRss->content;
      }
      else {
        $article->comments_url = $item->commentRss;
      }
    }

    // check to see if the source_meta item exists
    /*if ( (!$this->debug_exec) && (!$article->should_be_skipped) ) {
      // check directives
      if ($directives->feed_is_source) {
        if ( (isset($channel->title)) && (strlen($channel->title) > 0)) {
          $article->smid = $this->reconcileSourceMeta($channel->link,$channel->title); 
        }
        else {
          $article->smid = $this->reconcileSourceMeta($channel->link); 
        }
      }
      else {
        if (isset($item->source)) {
          $article->smid = $this->reconcileSourceMeta($article->url,$item->source); 
        }
        else {
          $article->smid = $this->reconcileSourceMeta($article->url); 
        }
      }

    }*/

    return $article;
  }

  // ======================================================
  // fetchArticleFullText
  // ======================================================
  private function fetchArticleFullText($article) {
    $this->logger->vars->set('function',__METHOD__);
    $article->body = NULL;
    // grab the full text
    $page = Http::getUrl($this->config,$article->url);
    // if we got it
    if ($page->success) {
      $this->logger->log("Fetched full text of " . $article->url . " successfully");

      // try to make things better if we can
      if (function_exists('tidy_parse_string')) {
        $tidy = tidy_parse_string($page->contents, array('indent'=>TRUE), 'UTF8');
        $tidy->cleanRepair();
        $page->contents = $tidy->value;
      }

      /*$url = urlencode($article->url);
      $fetch_url = "http://dev.gossipgrind.com/rsstest/makefulltextfeed.php?url={$url}&max=5&links=preserve&exc=&summary=1&format=json&submit=Create+Feed";
      $page = file_get_contents($fetch_url);
      print_r(json_decode($page)); exit;*/

      $rb = new Readability($page->contents,$article->url);
      $rb->debug = FALSE;
      $rb->convertLinksToFootnotes = TRUE;
      $result = $rb->init();
      if ($result) {
        if ($this->debug_exec) {
          $this->logger->log("== Title From Full Text =====================================",cLogger::INFO);
          $this->logger->log($rb->getTitle()->textContent);
        }
        $content = $rb->getContent()->innerHTML;
        // if we've got Tidy, let's clean it up for output
        if (function_exists('tidy_parse_string')) {
          $tidy = tidy_parse_string($content, array('indent'=>TRUE, 'show-body-only' => TRUE), 'UTF8');
          $tidy->cleanRepair();
          $content = $tidy->value;
        }
        //$article->title = $rb->getTitle()->textContent;
        //$article->body = TextHelper::cleanUTF8($content);
        //$article->body = html_entity_decode($content,ENT_QUOTES,'UTF-8');
        $article->body = $content;
      } 
      else {
        $this->logger->log('Looks like we couldn\'t find the content. :(');
      }
    }

    // put something in the body if there is no real text in it 
    if (strlen($article->body) == 0) { $article->body = $article->excerpt; }

    return $article;
  }

  // ======================================================
  // fetchMediaAssets
  // ======================================================
  private function fetchMediaAssets($article) {
    // set this to null
    $article->image = NULL;
    $exclude_image = FALSE;

    $this->logger->vars->set('function',__METHOD__);
    // attempt to pull out any images or videos from the article fulltext
    if ( (isset($article->body)) && (!is_null($article->body)) ) {
      $html = new simple_html_dom(); $html->load($article->body);
      // find img tags
      $images = $html->find('img'); 
      // if images
      if (count($images) > 0) {
        if (!is_array($article->image)) {
          $article->image = array();
        }
        // loop over the images
        foreach ($images as $img) {
          //print_r($img->getAllAttributes());
          $exclude_image = FALSE;
          $src = $img->src;
          $alt = (isset($img->alt)) ? $img->alt : "";
          $width = (isset($img->width)) ? $img->width : 0;
          $height = (isset($img->height)) ? $img->height : 0;
          $title = (isset($img->title)) ? $img->title : "";

          $name = explode('?',basename($img->src));

          // if src is a relative url, then try to fix it
          if (stristr($src,"http://") === FALSE) {
            // add url to the src
            $src = rtrim($article->feed_site_url,'/') . "/" . ltrim($src,'/');
          }

          // attempt to retrieve image size
          $image_data = getimagesize($src);
          if ( (is_array($image_data)) && ($image_data[0] > 100) && ($image_data[1] > 50) ) {
            $exclude_image = FALSE;
          }
          else {
            $exclude_image = TRUE;
          }

          // exclude certain image types
          if ( ($exclude_image == FALSE) && (stristr($name[0],".trans_") === FALSE) &&
               (stristr($name[0],".trans.") === FALSE) &&
               (stristr($name[0],"share") === FALSE) &&
               (stristr($name[0],"spacer") === FALSE) ) {
            // to hopefully get rid of ads and icons and other crap
            if ( ($width > 100) && ($height > 100) ) { 
              array_push($article->image,
                array("src"=>$src,"name"=>$name[0],"alt"=>$alt,"width"=>$width,"height"=>$height,"title"=>$title)
              );
            }
            else {
              array_push($article->image,
                array("src"=>$src,"name"=>$name[0],"alt"=>$alt,"width"=>$width,"height"=>$height,"title"=>$title)
              );
            }
          }
        }

        // only do this if there ended up being qualifying images
        if (count($article->image) > 0) {
          // create thumbnail images if of certain size
          //$article = $this->createThumbnails($article);
          $this->logger->vars->set('function',__METHOD__);
        }
        else {
          $article->image = NULL;
        }
      }
    }

    // handle the situation for videos
    $article->video = NULL;
    $videos_found_in_buffer = FALSE;
    $content_buffer = array('body','encoded_content');
    foreach ($content_buffer as $cb) {
      if ($videos_found_in_buffer) {
        break;
      }
      if ( (isset($article->$cb)) && (!is_null($article->$cb)) ) {
        $html = new simple_html_dom(); $html->load($article->$cb);
        // find object and iframe tags
        $objects = $html->find('embed'); $iframes = $html->find('iframe');
        // if objects
        if (count($objects) > 0) {
          if (!isset($article->video)) {
            $article->video = array();
          }
          // loop over the videos
          foreach ($objects as $embed) {
            $article->embed_type = 'object';
            $src = $embed->src;
            $type = $embed->type;
            $height = (isset($embed->height)) ? $embed->height : 0;
            $width = (isset($embed->width)) ? $embed->width : 0;
            $screen = (isset($embed->allowfullscreen)) ? $embed->allowfullscreen : false;
            $flashvars = (isset($embed->flashvars)) ? $embed->flashvars : "";
            array_push($article->video,$embed->getAllAttributes());
            /*array_push($article->video,
              array("src"=>$src,"width"=>$width,"height"=>$height,"type"=>$type,"screen"=>$screen,"flashvars"=>$flashvars)
            );*/
            $videos_found_in_buffer = TRUE;
          }
        }

        // if iframes
        if (count($iframes) > 0) {
          if (!isset($article->video)) {
            $article->video = array();
          }
          // loop over the videos
          foreach ($iframes as $embed) {
            // should get all attributes here
            $article->embed_type = 'iframe';
            $src = $embed->src;
            $type = $embed->type;
            $height = (isset($embed->height)) ? $embed->height : 0;
            $width = (isset($embed->width)) ? $embed->width : 0;
            $screen = (isset($embed->allowfullscreen)) ? $embed->allowfullscreen : false;
            $flashvars = (isset($embed->flashvars)) ? $embed->flashvars : "";
            /*array_push($article->video,
              array("src"=>$src,"width"=>$width,"height"=>$height,"type"=>$type,"screen"=>$screen,"flashvars"=>$flashvars)
            );*/
            array_push($article->video,$embed->getAllAttributes());
            $videos_found_in_buffer = TRUE;
          }
        }
        else {
          $article->video = NULL;
        }

      }
    }

    // one last check for images 
    // if there is a youtube video then snatch up the thumbnail
    if (!is_null($article->video)) {
      $this->logger->log('Looks like we got some videos to embed son!');
      // loop over the video array
      foreach ($article->video as $video) {
        $video_thumb = $this->getVideoThumbnails($article,$video['src']);
        if (!is_null($video_thumb)) {
          if (is_null($article->image)) {
            $article->image = array();
          }
          array_push($article->image,$video_thumb);
        }
      }
    }

    return $article;
  }

  private function getVideoThumbnails($article,$video_url) {
    $id = null;
    $retval = null;
    // search for youtube in the name of the video
    if (stristr($video_url,"youtube") === FALSE) {
      $t = 0;
    }
    else {
      $pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
      preg_match($pattern,$video_url,$matches); 
      if (count($matches) == 8) {
        $id = $matches[7];
      }
      // build the photo 
      $src = "http://img.youtube.com/vi/{$id}/0.jpg";
      $retval = array("src"=>$src,"name"=>"0.jpg","alt"=>"","width"=>480,"height"=>360,"title"=>"");
    }
    return $retval;
  }

  // ======================================================
  // reconcileArticleTags
  // ======================================================
  private function reconcileArticleTags($article,$item) {
    $this->logger->vars->set('function',__METHOD__);
    $article->tags = array();

    // if the category field is present 
    if (isset($item->category)) {

      // if category is present but it's not an array
      if (!is_array($item->category)) {
        $item->category = array($item->category);
      }

      // loop over each tag and check to see if it exists in the table
      foreach($item->category as $tag) {

        // check and see if tag is StdClass
        if ($tag instanceof StdClass) {
          // grab content property
          $tag = $tag->content;
        }

        $table = new TagTable($GLOBALS['cli']->dbconn);

        // fix the tag record
        $new_tag = new StdClass();
        $new_tag->tag_name = trim( strtolower($tag) );
        $new_tag->tag_slug = trim( TextHelper::sanitize($new_tag->tag_name) );

        // check to see if it exists
        $result = $table->select()->where("tag_slug='".$new_tag->tag_slug."'")->limit(1)->run(TRUE);
        if ($result->count() > 0) {
          $new_tag->tagid = $result[0]->tagid;
          $new_tag->tag_exists = TRUE;
          $new_tag->count = $result[0]->count + 1;
        }
        else {
          $new_tag->tag_exists = FALSE;
          $new_tag->count = 1;
        }

        // add tags to the article
        $article->tags[] = $new_tag;
      } 
    }
    return $article;
  }

  // ======================================================
  // reconcileSourceMeta
  // ======================================================
  private function reconcileSourceMeta($url,$name=NULL) {
    $this->logger->vars->set('function',__METHOD__);
    // initialize sourcemeta id
    $smid = 0;
    $table = new SourceMetaTable($GLOBALS['cli']->dbconn);
    // get the actual domain
    $domain = TextHelper::getDomain($url,FALSE);
    $sm_domain = TextHelper::getDomain($url);
    // search to see if that domain is in the house
    $result = $table->select()->where("url='".trim($sm_domain)."'")->limit(1)->run(TRUE);
    if ($result->count() > 0) {
      $smid = $result[0]->smid;
    }
    else {
      $source_meta_insert = array();
      // we need to insert it
      if (!is_null($name)) {
        $source_meta_insert['full_name'] = strtolower($name);
      }
      $source_meta_insert['name'] = $domain;
      $source_meta_insert['url'] = trim($sm_domain);
      // do insert
      $insert = $table->insert($source_meta_insert)->run(TRUE);
      if ($insert->affected() == 0) {
        $this->logger->log('We could not insert a new SourceMetaTable row with the following data: '); 
        $this->logger->log('SourceMeta name: ' . strtolower($source_meta_insert['name']) . ' -- SourceMeta url: ' .  $source_meta_insert['url']); 
        $smid = 0;
      }
      else {
        $smid = $insert->getInsertId();
      }
    }
    return $smid;
  }

  // ======================================================
  // executeArticleSave
  // ======================================================
  private function executeArticleSave($article) {
    $this->logger->vars->set('function',__METHOD__);
    $retval = new StdClass(); 
    $retval->status = TRUE; 
    $retval->aid = 0;
    $retval->tagid = 0;
    $article_table = new ArticleTable($GLOBALS['cli']->dbconn);
    //print_r((array) $article);

    // if images then turn to json
    if (!is_null($article->image)) {
      $article->image = json_encode($article->image);
    }
    // if videos then turn to json
    if (!is_null($article->video)) {
      $article->video = json_encode($article->video);
    }

    if (is_object($article)) {
      $save_data = (array) $article;
    }
    else {
      $save_data = $article;
    }

    // kick off the save situation
    $result = $article_table->insert($save_data)->run();
    if ($result->affected() == 0) {
      $retval->status = FALSE;
    }
    else {
      $retval->aid = $result->getInsertId();
    }

    // handle the tags insertion
    if ($retval->aid > 0) {
      // loop over the article tags
      foreach($article->tags as $tag) {
        $tag_table = new TagTable($GLOBALS['cli']->dbconn);
        if ($tag->tag_exists) { // tag exists
          $tag_result = $tag_table->update((array) $tag)->where('tagid='.$tag->tagid)->run();
          $retval->tagid = $tag->tagid;
        }
        else { // tag does not exist
          $tag_result = $tag_table->insert((array) $tag)->run();
          $retval->tagid = $tag_result->getInsertId();
        }
        // insert new row into taxonomy table
        $taxonomy_table = new TaxonomyTable($GLOBALS['cli']->dbconn);
        $taxonomy_table->insert(array('aid'=>$retval->aid,'tagid'=>$retval->tagid))->run();
      }
    }

    return $retval;
  }

  // ======================================================
  // purgeOrphan tags
  // ======================================================
  private function purgeOrphanTags() {
    $orphan_taxonomy = new SQLExecutor($GLOBALS['cli']->dbconn);
    $orphan_taxonomy->sql("DELETE FROM taxonomy WHERE taxonomy.aid NOT IN (SELECT article.aid FROM article)")->run(TRUE);
  }

  // ======================================================
  // purgeOldData
  // ======================================================
  private function purgeOldData() {
    $this->logger->vars->set('function',__METHOD__);
    $config = $this->config;
    $select = new SQLExecutor($GLOBALS['cli']->dbconn);
    $purge_query = "SELECT aid FROM article WHERE last_update < DATE_SUB(NOW(), INTERVAL " . $config->purge_interval . " DAY)";
    $select->sql($purge_query);
    $delete_count = $select->run(); 
    
    // if any rows to purge/shrink
    if (count($delete_count) > 0) {
      if (strcasecmp($config->purge_type,"delete") == 0) {
        $this->logger->log("We will attempt to DELETE " . count($delete_count) . " rows from crawl_items");
        // delete articles
        $delete = new SQLExecutor($GLOBALS['cli']->dbconn);
        $delete->sql(
          "DELETE FROM article " .
          "WHERE last_update < DATE_SUB(NOW(), INTERVAL " . $config->purge_interval ." DAY)"
        );
        $delete_results = $delete->run();
        $this->logger->log($delete_results->affected() . " rows purged in articles");
      }
      else {
        $this->logger->log("We will attempt to DELETE " . count($delete_count) . " rows from crawl_items");
        // delete articles
        $delete = new SQLExecutor($GLOBALS['cli']->dbconn);
        $delete->sql(
          "UPDATE article SET status='retired', body=NULL " .
          "WHERE last_update < DATE_SUB(NOW(), INTERVAL " . $config->purge_interval . " DAY)");
        $delete_results = $delete->run();
        $this->logger->log($delete_results->affected() . " rows retired in articles");
      }

      // delete tags from deleted articles
      $taxonomy_delete = new SQLExecutor($GLOBALS['cli']->dbconn);
      $taxonomy_delete->sql(
        "DELETE FROM taxonomy WHERE aid IN (" . $purge_query .")"
      );
      $taxonomy_delete_results = $taxonomy_delete->run();
      $this->logger->log($taxonomy_delete_results->affected() . " rows deleted from the taxonomy table");
    }
  }
}

$command = new GatherDoo();
// run the main 
$command->main();
