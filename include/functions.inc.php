<?php
    function translate($tag) {
		if ( isset($GLOBALS['translation'][$tag]) ) {
			return $GLOBALS['translation'][$tag];
		}
		else {
			return $tag.' ('.$GLOBALS['translation']['translation_not_found'].')';
		}
	}
?>