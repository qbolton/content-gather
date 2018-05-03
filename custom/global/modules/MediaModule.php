<?php
class MediaModule extends CGModule {
  public function run() {}

  // Attempts to grab thumbnails from urls on certain video sites
  private function youtubeThumbs($video_url) {
    $id = NULL;
    $retval = NULL;
    // search for youtube in the name of the video
    if (stristr($video_url,"youtube") === FALSE) {
      // basically do nothing
      $t = NULL;
    }
    else {
      $pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
      preg_match($pattern,$video_url,$matches);
      if (count($matches) == 8) {
        $id = $matches[7];
      }

      // build the photo
      $src = "http://img.youtube.com/vi/{$id}/0.jpg";
      $width = 480;
      $height = 360;
      $title = "";
      $type = "image";
      $retval =
      array("embed_type"=>"img","media_src"=>$src,"media_name"=>"0.jpg","media_alt"=>"","media_width"=>$width,"media_height"=>$height,"media_title"=>$title,"media_type"=>$type,"media_attributes"=>$attributes);
    }
    return $retval;
  }
}
