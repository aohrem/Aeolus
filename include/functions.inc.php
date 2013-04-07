<?php
    function translate($tag) {
		if ( isset($GLOBALS['translation'][$tag]) ) {
			return $GLOBALS['translation'][$tag];
		}
		else {
			return $tag.' ('.$GLOBALS['translation']['translation_not_found'].')';
		}
	}
    
    function open($file) {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url_parts = explode('/', $url);
		$url = str_replace($url_parts[sizeof($url_parts) - 1], '', $url);
        
        $header = "GET ".$url.$file." HTTP/1.0\r\n";
        $header .= "Host: ".$url."\r\n";
        $header .= "Connection: close\r\n";
        $header .= "\r\n";
        
        // open the connection
        $conn = fsockopen($_SERVER['HTTP_HOST'], '80', $err_num, $err_msg, 30);

        $response = '';
        if ( $conn ) {
            fputs($conn, $header);
        }
    }
?>