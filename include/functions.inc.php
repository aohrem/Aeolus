<?php
    // file contains some function which are independent from any classes
    
    // gets the language specified by the user and checks if it is valid
    function setLanguage($languages) {
        $language;
        $standardLanguage = 'en';
        
        // get current language
        if ( isset($_GET['lang']) ) {
            $language = $_GET['lang'];
        
            // check if we have got a correct language abbreviation
            if ( ! in_array($language, $languages) ) {
                $language = $standardLanguage;
            }
        }
        // if no language is set, use the standard language
        else {
            $language = $standardLanguage;
        }
    
        include('lang/'.$language.'.lang.php');
        
        return $language;
    }
    
    // returns the translated language tag
    function translate($tag) {
        if ( isset($GLOBALS['translation'][$tag]) ) {
            return $GLOBALS['translation'][$tag];
        }
        else {
            return $tag.' ('.$GLOBALS['translation']['translation_not_found'].')';
        }
    }
    
    // opens another php script on this server
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