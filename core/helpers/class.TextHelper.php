<?php
class TextHelper {
  public static function cleanUTF8($content) {
    $content = html_entity_decode($content,ENT_NOQUOTES,'UTF-8');
		$clean_str = preg_replace("/[^[:space:][:alnum:][:punct:]]/","",$content);
    $content = htmlentities($content,ENT_NOQUOTES,'UTF-8');
    if(!mb_check_encoding($clean_str, 'UTF-8')
      OR !($content === mb_convert_encoding(mb_convert_encoding($clean_str, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {
        $content = mb_convert_encoding($content, 'UTF-8');
        if (mb_check_encoding($content, 'UTF-8')) {
          // cool things worked
        }
        else {
          $content = $clean_str;
        }
    }
    else {
      $content = $clean_str;
    }
    return $content;
  }

	public static function cleanForHash($str) {
		$clean_str = preg_replace("/[^[:space:][:alnum:][:punct:]]/","",$str);
		// remove spaces and make all lower case
		return str_replace(' ','',strtolower($clean_str));
	}

	public static function cleanForQuery($str) {
		$clean_str = preg_replace("/[^[:space:][:alnum:][:punct:]]/","",strip_tags($str));
		// remove spaces and make all lower case
		return $clean_str;
	}

	// create md5 hash checksum
	public static function hash($str) {
		$hash_str = null;
		$str = TextHelper::cleanForHash($str);
		// create the hash
		$hash_str = md5( trim($str) );
		return $hash_str;
	}

	public static function isDuplicateUrl($url,$conn) {
		$retval = TextHelper::isDuplicate("url_hash",TextHelper::hash($url),$conn);
    if ($retval == FALSE) {
		  $retval = TextHelper::isDuplicate("url",$url,$conn);
    }
		return $retval;
	}

	public static function isDuplicateTitle($title,$conn) {
		return TextHelper::isDuplicate("title_hash",TextHelper::hash($title),$conn);
	}

	public static function isDuplicateText($text,$conn) {
		return TextHelper::isDuplicate("text_hash",TextHelper::hash($text),$conn);
	}

	public static function isDuplicate($haystack="url_hash",$needle,$conn) {
		$retval = false;
		// check to see if this url is already indexed
		$table = new ArticleTable($conn);
		// query string
		//print_r($table->select()->where($haystack . " = '" . TextHelper::hash($needle) . "'")->export());
		$result = $table->select()->where($haystack . " = '" . $needle . "'")->run();
		if (count($result) > 0) {
			$retval = true;
		}
		return $retval;
	}

  public static function stringToFileName($str) {
    // remove all non-alphanumeric chars at begin & end of string
    $tmp = preg_replace('/^\W+|\W+$/', '', $str); 
    // compress internal whitespace and replace with _
    $tmp = preg_replace('/\s+/', '_', $tmp); 
    // remove all non-alphanumeric chars except _ and -
    return strtolower(preg_replace('/\W-/', '', $tmp)); 
  }

  /**
  * Convert a string to the file/URL safe "slug" form
  *
  * @param string $string the string to clean
  * @param bool $is_filename TRUE will allow additional filename characters
  * @return string
  */
  public static function sanitize($string = '', $is_filename = FALSE)
  {
    // Replace all weird characters with dashes
    $string = preg_replace('/[^\w\-'. ($is_filename ? '~_\.' : ''). ']+/u', '-', trim($string)); 
    // Only allow one dash separator at a time (and make string lowercase)
    $string = mb_strtolower(preg_replace('/--+/u', '-', $string), 'UTF-8');
    //return preg_replace('/-[^a-zA-Z0-9]+$/','', $string);
    return trim($string,'-');
  }

	public static function fixTitle($title_text,$anchor_text, $use_title_text=false) {
		// make sure title_text is not hella long
		$title_split = explode(' - ',$title_text);
		if (is_array($title_split)) {
			//$clean_title_text = trim( implode('-',array_pop($title_split)));
			$clean_title_text = trim( $title_split[0] );
		}
		else {
			$clean_title_text = trim($title_text);
		}

		if (str_word_count($anchor_text) >= 3) {
			$title = trim($anchor_text);
		}
		else if ( (str_word_count($anchor_text) < 3) && (strlen($anchor_text) >= 20) ) {
			$title = trim($anchor_text);
		}
		else {
			// make sure title text is actually longer than the anchor
			if (str_word_count($anchor_text) < str_word_count($clean_title_text)) {
				$title = trim($clean_title_text);	
			}
			else {
				$title = trim($anchor_text);
			}
		}

		if ($use_title_text) {
			$title = trim($clean_title_text);	
			// check size of $title
			if (str_word_count($title) < 2) {
				$title = $title_text;
			}
		}

    // clean the title nicely
    $title = preg_replace('/\s+/',' ',strip_tags( htmlspecialchars_decode($title) ));

		return ucwords( strtolower($title) );
	}

	public static function getDomain($url,$with_scheme=TRUE)
	{
		if(filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === FALSE)
		{
			return false;
		}
		/*** get the url parts ***/
		$parts = parse_url($url);
		/*** return the host domain ***/
    if ($with_scheme) {
		  return $parts['scheme'].'://'.$parts['host'];
    }
    else {
		  return $parts['host'];
    }
	}

	public static function textExcerptOld( $text, $limit = 30, $include_hellip = TRUE ) {
    $chars = '0123456789';
    if( strlen( $text ) > $limit ) {
        $words = str_word_count( $text, 2, $chars );
        $words = array_reverse( $words, TRUE );
        foreach( $words as $length => $word ) {
            if( $length + strlen( $word ) >= $limit ) {
                array_shift( $words );
            } else {
                break;
            }
        }
        $words = array_reverse( $words );
        if ($include_hellip) {
          $text = implode( " ", $words ) . ' &hellip;';
        }
        else {
          $text = implode( " ", $words );
        }
    }
    return $text;
	}

  public static function textExcerpt($text,$limit = 30, $include_hellip = TRUE) {
    if (strlen($text) > $limit) { 
      $text = substr($text, 0, $limit); 
      $text = substr($text,0,strrpos($text," ")); 
      $etc = " ...";  
      if ($include_hellip) {
        $text = $text.' &hellip;'; 
      }
      else {
        $text = $text; 
      }
    }
    return $text; 
  }

}
?>
