<?php
class Http {
	public static function getUrl($config,$url) {
		$page = new StdClass();
		$page->contents = NULL;
		$page->http_code = NULL;
		$page->mime_type = NULL;

		$curl = curl_init();

		// prepare curl for http request action
		curl_setopt($curl,CURLOPT_USERAGENT,$config->user_agent);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl,CURLOPT_VERBOSE,false);
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,4);
		curl_setopt($curl,CURLOPT_TIMEOUT, 30);

		// setup curl url
		curl_setopt($curl,CURLOPT_URL,$url);
		// store the page contents
		$contents = curl_exec($curl);
		$page->contents = trim($contents);
    // get the complete info array
    // $page->curlinfo = curl_getinfo($curl);
		// get the return code
		$page->http_code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    // get the mime type
		$page->mime_type = curl_getinfo($curl,CURLINFO_CONTENT_TYPE);
		// close curl
		curl_close($curl);
		// check status
		if ($page->http_code >= 200 && $page->http_code < 300) {
			$page->success = true;
		}
		else {
      $page->success = false;
		}
		return $page;
	}
}
?>
