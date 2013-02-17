<?php
// class of SimpleXMLElement with method to get attributes of xml tags by their name
class SimpleXMLExtended extends SimpleXMLElement {
	public function attribute($name) {
		foreach( $this->Attributes() as $key => $val ) {
			if ( $key == $name ) {
				return (string) $val;
			}
		}
	}
}
?>