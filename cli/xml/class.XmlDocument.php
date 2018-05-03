<?php
/**
 * XmlDocument class extends DOMDocument class to provide easy xml manipulation
 *
 * @package Getloaded
 * @subpackage common.xml
 */

/**
 * XmlDocument class
 *
 * @package Getloaded
 * @subpackage Common.Xml
 */
class XmlDocument extends DOMDocument {

	/**
	 * Constructs elements and texts from an array or string.
	 * The array can contain an element's name in the index part
	 * and an element's text in the value part.
	 *
	 * It can also creates an xml with the same element tagName on the same
	 * level.
	 *
	 * ex:
	 * <nodes>
	 *   <node>text</node>
	 *   <node>
	 *     <field>hello</field>
	 *     <field>world</field>
	 *   </node>
	 * </nodes>
	 *
	 * Array should then look like:
	 *
	 * Array (
	 *   "nodes" => Array (
	 *     "node" => Array (
	 *       0 => "text"
	 *       1 => Array (
	 *         "field" => Array (
	 *           0 => "hello"
	 *           1 => "world"
	 *         )
	 *       )
	 *     )
	 *   )
	 * )
	 *
	 * @param mixed $mixed An array or string.
	 *
	 * @param DOMElement[optional] $domElement Then element
	 * from where the array will be constructed to.
	 *
	 */
	public function fromMixed($mixed, DOMElement $domElement = null) {

		$domElement = is_null($domElement) ? $this : $domElement;

		if (is_array($mixed)) {
			foreach( $mixed as $index => $mixedElement ) {

				if ( is_numeric($index) ) {
					if ( $index == 0 ) {
						$node = $domElement;
					} else {
						$node = $this->createElement($domElement->tagName);
						$domElement->parentNode->appendChild($node);
					}
				} 
				else {
					$node = $this->createElement($index);
					$domElement->appendChild($node);
					// apply attributes to newly created domElement
					if (is_array($mixedElement)) {
						if (array_key_exists('attributes',$mixedElement)) {
							// loop over the attribute array
							foreach($mixedElement['attributes'] as $name => $value) {
								$attribute = $this->createAttribute($name);
								$node->appendChild($attribute);
								// do basic value cleaning
								$text = $this->createTextNode( htmlspecialchars( trim(chop(str_replace("\r\n",'',preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value)))),ENT_QUOTES,'UTF-8' ) );
								$attribute->appendChild($text);
							}
							// remove the attributes element from the mixedElement array
							unset($mixedElement['attributes']);
						}
					}
				}

				$this->fromMixed($mixedElement, $node);

			}
		} else {
			$domElement->appendChild($this->createTextNode( htmlspecialchars( trim(chop(str_replace("\r\n",'',preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $mixed)))),ENT_QUOTES,'UTF-8' ) ));
		}
	}

}
?>
