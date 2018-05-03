<?php
class ExtractParser extends CGParser {

  // ==============================================
  // Parse()
  // Breaks the passed in feed item up into it's 
  // fields for saving
  // ==============================================
  public function parse() {
    $this->media = new StdClass();
    $this->tags = new StdClass();

    $this->media->images = NULL;
    $this->media->video = NULL;

    // set site id
    $this->vars->site_id = $this->raw_item->site_id;
    // set feed id
    $this->vars->fid = $this->raw_item->fid;

    //print_r($this->raw_item);

    // set title
    $this->setTitle();
    // set document url
    $this->setUrl();
    // set permalink
    $this->setPermalink();
    // set post body
    $this->setContent();
    // set author
    $this->setCreator();
    // set description and post_excerpt
    $this->setDescription();
    // set encoded items
    $this->setImages();
    // set publication date
    $this->setPubDate();
    // set the thumbnail
    $this->setThumbnail();
    // set commentRSS
    // $this->setCommentRSS();
    // get the RSS comments
    $this->setTags();

    // set hash value 
    $this->vars->post_hash = CleanText::hash(
      $this->vars->post_url . $this->vars->post_title
    );

    // check size of post_excerpt
    if ((is_null($this->vars->post_excerpt)) || (strlen($this->vars->post_excerpt) < 30)) {
      $this->exclude = TRUE;
    }
  }

  // set the author/creator
  public function setCreator() {
    // get assumed author/creator info
    if (isset($this->raw_item->authors)) {
      if (count($this->raw_item->authors) > 0) {
        $this->vars->post_creator =
          json_encode( array("name"=>$this->raw_item->authors[0]->name,"url"=>$this->raw_item->authors[0]->url) );
      }
    }
  }

  // grab the title of the document
  public function setTitle() {
    $post_title = NULL;

    // create document title
    //$post_title = preg_replace("/\[([^\[\]]*+|(?R))*\]/","",$this->raw_item->title); 
    //$post_title = ucwords( strtolower( CleanText::makeUTF8(trim($post_title)) ) );
    $this->vars->post_title = $this->raw_item->title;

    // create seo_title value
    $this->vars->post_seo_title = CleanText::sanitize(trim($this->vars->post_title));
  }

  // set the post_url
  public function setUrl() {
    // get the original url if available, otherwise use link
    $this->vars->post_url = trim($this->raw_item->url);
  }

  // set the post_desc
  public function setDescription() {
    if (isset($this->raw_item->description)) {
      $this->vars->description = trim( $this->raw_item->description );
      $this->vars->post_excerpt = trim(CleanText::makeUTF8( $this->raw_item->description ));
    }
    else {
      $this->vars->description = NULL;
      $this->vars->post_excerpt = NULL;
    }
  }

  // set the publication date of the item
  public function setPubDate() {
    if (isset($this->raw_item->pubDate)) {
      $this->vars->post_pubdate_int = strtotime( $this->raw_item->pubDate );
      $this->vars->post_pubdate = date("Y-m-d H:i:s",$this->vars->post_pubdate_int);
    }
    else {
      // how do you not put pubDate in your feed?
      // accomodate this foolishness by using the current date and time
      $this->vars->post_pubdate_int = strtotime("now");
      $this->vars->post_pubdate = date("Y-m-d H:i:s",$this->vars->post_pubdate_int);
    }

    // if, for example, the pubdate is in the future give it current date and time
    $diff = time() - $this->vars->post_pubdate_int;
    if ($diff < 0) {
      $this->vars->post_pubdate_int = strtotime("now");
      $this->vars->post_pubdate = date("Y-m-d H:i:s",$this->vars->post_pubdate_int);
    }
  }

  // set the comment url for the post (currently not used)
  //public function setCommentRSS() {
  //  $this->vars->post_comment_url = NULL;
  //}
  
  // set the post thumbnail
  public function setThumbnail() {
    if (is_null($this->media->images)) {
      $this->media->images = array();
    }

    // add thumbnail item to image media array
    if (isset($this->raw_item->thumbnail)) {
      // create article image array
      $image = array();
      $image['src'] = $this->raw_item->thumbnail->url;
      $image['width'] = ((isset($this->raw_item->thumbnail->width)) ? $this->raw_item->thumbnail->width : 0);
      $image['height'] = ((isset($this->raw_item->thumbnail->height)) ? $this->raw_item->thumbnail->height : 0);
      $image['alt'] = "";
      $image['title'] = "";
      $this->media->images[] = $image;
    }
  }

  // set the post tags
  public function setTags() {
    if (isset($this->raw_item->entities)) {
      $this->tags->category = array();
      // loop over the entities
      foreach($this->raw_item->entities as $entity) {
        $this->tags->category[] = $entity->name;
      }
    }
  }

  // use the guid to set the permalink up
  public function setPermalink() {
    if (isset($this->raw_item->original_url)) {
      $this->vars->post_permalink = $this->raw_item->original_url;
    }
  }

  // handle the encoded content of the feed
  public function setImages() {
    if (isset($this->raw_item->images)) {
      // grab images
      $this->media->images = array();

      // loop over images
      foreach ($this->raw_item->images as $image) {
        // add each to image array
        array_push($this->media->images,
            array("embed_type"=>"img",
                  "media_src"=>$image->url,
                  "media_alt"=>$image->caption,
                  "media_width"=>$image->width,
                  "media_height"=>$image->height,
                  "media_title"=>$image->caption,
                  "media_type"=>"image")
        );
      }
    }
    
    // grab videos
    $this->media->video = NULL;
  }

  // set the post_body
  public function setContent() {
    if (isset($this->raw_item->content)) {
      $this->vars->post_body = $this->raw_item->content;
    }
  }

  public function savePost() {
    try {
      // get db connection
      $conn = new DBConnection($GLOBALS['cli']->dbinfo);
      // get instance of posts table
      $table = new CgRawPostsTable($conn);
      // get sql instance
      $check = new SQLExecutor($conn);

      if (isset($this->tags->category)) {
        // turn the array of tags into json text
        $post_tags_json = json_encode($this->tags->category);
        $this->vars->post_tags = $post_tags_json;
      }

      // turn the array of images into json text
      print_r($this->media->images);
      if ( (isset($this->media->images)) && (count($this->media->images) > 0) ) {
        $this->vars->post_image = json_encode($this->media->images);
      }
      else {
        $this->vars->post_image = NULL;
      }

      // turn the array of video into json text
      if ( (isset($this->media->video)) && (count($this->media->video) > 0) ) {
        $this->vars->post_video = json_encode($this->media->video);
      }
      else {
        $this->vars->post_video = NULL;
      }

      // turn post variables into array
      $save_data = (array) $this->vars;

      $check_results = $check->sql("select raw_post_exists('".$save_data['post_hash']."') as post_exist")->run();
      if ($check_results[0]['post_exist'] == 0) {
        // insert data
        $result = $table->insert($save_data)->run();
      }
      else {
        $result = $table->update($save_data)->where("pid = ".$check_results[0]['post_exist'])->run();
      }

    } 
    catch (Exception $e) {
      throw $e;
    }
    return $result->getInsertId();
  }
}
?>
