<?php
/**
 * Define useful methods for translating, converting and encoding
 * string data
 *
 * @package DooFramework
 * @subpackage common.util.string
 */

/**
 * Doo framework StringConvert utility class
 * @package DooFramework
 * @subpackage common.util.string
 */
class StringConvert {
	/** 
	 * Encodes a string in utf-8 format and also changes HTML special characters into string entities
	 *
	 * @access static public
	 * @param string $text The string containing the text
	 * @return string 
	 */
	static public function safe_encode($text) { return (utf8_encode(htmlspecialchars($text))); }
}
?>
