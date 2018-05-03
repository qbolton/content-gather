<?php
/**
 * Define useful methods for manipulating string output
 *
 * @package DooFramework
 * @subpackage common.util.string
 */

/**
 * Doo framework StringOutput utility class
 * @package DooFramework
 * @subpackage common.util.string
 */
class StringOutput {
	/** 
	 * Replaces single quotes in a string with double quotes
	 *
	 * @access static public
	 * @param string $text The string containing the text
	 * @return string 
	 */
	static public function qq($text) {return str_replace('`','"',$text); }

	/** 
	 * Prints a properly quoted string
	 *
	 * @access static public
	 * @param string $text The string containing the text
	 * @return void
	 */
	static public function printq($text) { print StringOutput::qq($text); }

	/** 
	 * Prints a properly quoted string and adds a newline to the output
	 *
	 * @access static public
	 * @param string $text The string containing the text
	 * @return void
	 */
	static public function printqn($text) { print StringOutput::qq($text)."\n"; }
}
?>
