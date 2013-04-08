<?php
 	function classifier($value, $min, $max, $return) {
		$deviation = $max - $min;
		$step = floor($deviation / 3);
		$value = floatval($value);
        
		if ( $return == 'class' ) {
			if ($value <= $min) {
				return 'class'. 1;
			}
			else if ( $value > $min && $value <= ($min+$step) ) {
				return 'class'. 2;
			}
			else if ( $value > ($min+$step) && $value <= ($min+2*$step) ) {
				return 'class'. 3;
			}
			else if ( $value > ($min+2*$step) && $value <= $max ) {
				return 'class'. 4;
			}
			else if ($value > $max) {
				return 'class'. 5;
			}
			else {
				return "noval";
			}
		}
		else if ($return == 'classes') {
			$classes = array();
			$classes["class1"] = "&le; ". $min ." {unit}";
			$classes["class2"] = "&gt; ". $min ." {unit} - ". ($min + $step) ." {unit}";
			$classes["class3"] = "&gt; ". ($min + $step) ." {unit} - ". ($min + 2 * $step) ." {unit}";
			$classes["class4"] = "&gt; ". ($min + 2 * $step) ." {unit} - ". $max ." {unit}";
			$classes["class5"] = "&gt; ". $max ." {unit}";
			return $classes;
		}
	}
	
	// find URL of the current page
	$url = $_SERVER['REQUEST_URI'];
	// delete former input for classify in URL
	$url_parts = explode('/', $url);
    $url = $url_parts[sizeof($url_parts)-1];
	if ( ! preg_match('/lanuv=/',$url) ) {
		$url = $url ."&amp;lanuv={lanuv_url}";
	}
	
	if (isset($_GET['classify'])){
		$classify = htmlentities(mysql_real_escape_string($_GET['classify']));
		$this->contentTemplate->tplReplace('show', ' class="show"');
		$this->contentTemplate->tplReplace('classify', $classify);
		$url = str_replace('&classify='.$classify, '', $url);
        
		switch ( $classify ) {
			case 'co':
				// TODO: min und max anpassen
				$min = 80;
				$max = 130;
				$unit = 'ppm';
				$classes = classifier(0, $min, $max, 'classes');
			break;
			case 'no2':
				// TODO: min und max anpassen
				$min = 230;
				$max = 270;
				$unit = 'ppm';
				$classes = classifier(0, $min, $max, 'classes');
			break;
			case 'temperature':
				$min = 0;
				$max = 30;
				$unit = '&deg;C';
				$classes = classifier(0, $min, $max, 'classes');
			break;
			case 'humidity':
				// TODO: min und max evtl. verfeinern
				$min = 20;
				$max = 80;
				$unit = "%";
				$classes = classifier(0, $min, $max, 'classes');
			break;
			default:
				header("Location:index.php?s=map&lang=".$this->language);
			break;
		}
		$this->contentTemplate->tplReplace('egg_sensor', '_'.$classify);
		$this->contentTemplate->tplReplace('class1_interval', $classes["class1"]);
		$this->contentTemplate->tplReplace('class2_interval', $classes["class2"]);
		$this->contentTemplate->tplReplace('class3_interval', $classes["class3"]);
		$this->contentTemplate->tplReplace('class4_interval', $classes["class4"]);
		$this->contentTemplate->tplReplace('class5_interval', $classes["class5"]);
		$this->contentTemplate->tplReplace('unit', $unit);
	}
	else {
		$this->contentTemplate->tplReplace('show', '');
		$this->contentTemplate->tplReplace('egg_sensor', "");
	}
	
	$mySqlConnection = new MySqlConnection();
	
	// include AQEs to map
	$query = mysql_query('SELECT `feed_id`, `lat`, `lon` FROM `egg`');
	if (mysql_num_rows($query) == 0) {
		$this->contentTemplate->cleanCode('Egg');
	}
	else {
		while ($row = mysql_fetch_object($query)) {
 			$this->contentTemplate->copyCode('Egg');
			$this->contentTemplate->tplReplaceOnce('egg_lat', $row->lat);
			$this->contentTemplate->tplReplaceOnce('egg_lon', $row->lon);
			$this->contentTemplate->tplReplaceOnce('egg_fid', $row->feed_id);
			$class = 'noval';
            
            $aqDatabase = new AirQualityDatabase($row->feed_id, '6h');
            $dataArray = $aqDatabase->getCurrentValues();
            
			if ( is_array($dataArray) && isset($dataArray['current_value']) ) {
				if ( isset($classify) ) {
					switch ($classify) {
						case "co":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, 'class');
						break;
						case "no2":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, 'class');
							// to do: change min and max
						break;
						case "temperature":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, 'class');
						break;
						case "humidity":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, 'class');
							// to do: evtl. prüfen
						break;
					}
				}
				
				if ( ! isset($dataArray['current_value']['co']) ) {
					$dataArray['current_value']['co'] = 0;
				}
				if ( ! isset($dataArray['current_value']['no2']) ) {
					$dataArray['current_value']['no2'] = 0;
				}
				if ( ! isset($dataArray['current_value']['temperature']) ) {
					$dataArray['current_value']['temperature'] = 0;
				}
				if ( ! isset($dataArray['current_value']['humidity']) ) {
					$dataArray['current_value']['humidity'] = 0;
				}
                
				$this->contentTemplate->tplReplaceOnce('egg_coval', $dataArray['current_value']['co']);
				$this->contentTemplate->tplReplaceOnce('egg_no2val', $dataArray['current_value']['no2']);
				$this->contentTemplate->tplReplaceOnce('egg_tempval', $dataArray['current_value']['temperature']);
				$this->contentTemplate->tplReplaceOnce('egg_humval', $dataArray['current_value']['humidity']);
				$this->contentTemplate->tplReplaceOnce('egg_title', mysql_real_escape_string(substr($dataArray['title'], 0, -3)));
			}
			else {
				$this->contentTemplate->tplReplaceOnce('egg_coval', 0);
				$this->contentTemplate->tplReplaceOnce('egg_no2val', 0);
				$this->contentTemplate->tplReplaceOnce('egg_tempval', 0);
				$this->contentTemplate->tplReplaceOnce('egg_humval', 0);
				$this->contentTemplate->tplReplaceOnce('egg_title', "-");
			}
			$this->contentTemplate->tplReplaceOnce('egg_color', "'".$class."'");
		}
		$this->contentTemplate->cleanCode('Egg');
	}
	
	if ( isset($_GET['lanuv']) ) {
		$lanuv = htmlentities($_GET['lanuv']);
		if ($lanuv == 'true'){
			$lanuv_url= "false";
			// add Lanuv-Symbol to map
			$query_lanuv = mysql_query("SELECT * FROM `lanuv`");
			if (mysql_num_rows($query_lanuv) == 0) {
				$this->contenTemplate->clean_code('Lanuv');
			}
			else {
				while ($row = mysql_fetch_object($query_lanuv)) {
					$this->contentTemplate->copyCode('Lanuv');
					$this->contentTemplate->tplReplaceOnce('lanuv_lat', $row->lat);
					$this->contentTemplate->tplReplaceOnce('lanuv_lon', $row->lon);
					$this->contentTemplate->tplReplaceOnce('lanuv_code', $row->code);
					/* $this->contentTemplate->tplReplaceOnce('lanuv_city', $row->feed_id);
					$this->contentTemplate->tplReplaceOnce('lanuv_street', $row->feed_id); */
				}
				$this->contentTemplate->cleanCode('Lanuv');
				
			}
			
		}
	}
	else {
		$lanuv_url= "true";
	}
	$url = str_replace('{lanuv_url}', $lanuv_url, $url);
/* 	$this->contentTemplate->tplReplace('lanuv_url', $lanuv_url); */
	$this->contentTemplate->tplReplace('url', $url);
?>