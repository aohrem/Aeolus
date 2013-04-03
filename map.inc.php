<?php
 	function classifier($value, $min, $max, $return) {
		$deviation = $max - $min;
		$step = floor($deviation / 3);
		$value = floatval($value);
		if ($return == "class") {
			if ($value <= $min) {
				return "class". 1;
			}
			else if ( $value > $min && $value <= ($min+$step) ) {
				return "class". 2;
			}
			else if ( $value > ($min+$step) && $value <= ($min+2*$step) ) {
				return "class". 3;
			}
			else if ( $value > ($min+2*$step) && $value <= $max ) {
				return "class". 4;
			}
			else if ($value > $max) {
				return "class". 5;
			}
			else {
				return "noval";
			}
		}
		else if ($return == "classes") {
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
	
	if (isset($_GET['classify'])){
		$classify = htmlentities(mysql_real_escape_string($_GET['classify']));
		$this->contentTemplate->tplReplace('show', ' class="show"');
		$this->contentTemplate->tplReplace('classify', $classify);
		$url = str_replace('&classify='.$classify, '', $url);
		switch ( $classify ) {
			case "co":
				// TODO: min und max anpassen
				$min = 100;
				$max = 300;
				$unit = "ppm";
				$classes = classifier(0, $min, $max, "classes");
			break;
			case "no2":
				// TODO: min und max anpassen
				$min = 230;
				$max = 270;
				$unit = "ppm";
				$classes = classifier(0, $min, $max, "classes");
			break;
			case "temperature":
				$min = 0;
				$max = 30;
				$unit = "&deg;C";
				$classes = classifier(0, $min, $max, "classes");
			break;
			case "humidity":
				// TODO: min und max evtl. verfeinern
				$min = 20;
				$max = 50;
				$unit = "%";
				$classes = classifier(0, $min, $max, "classes");
			break;
			default:
				header("Location:index.php?s=map&lang=".$this->language);
			break;
		}
		$this->contentTemplate->tplReplace('egg_sensor', "_".$classify);
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
	
	$db = new Sql();
	$query = mysql_query('SELECT `feed_id`,`lat`,`lon` FROM `egg`');
	if (mysql_num_rows($query) == 0) {
		$this->contentTemplate->cleanCode('Egg');
	}
	else {
		while ($row = mysql_fetch_object($query)) {
 			$this->contentTemplate->copyCode('Egg');
			$this->contentTemplate->tplReplaceOnce('egg_lat', $row->lat);
			$this->contentTemplate->tplReplaceOnce('egg_lon', $row->lon);
			$this->contentTemplate->tplReplaceOnce('egg_fid', $row->feed_id);
			$class = "noval";
			// Check if classification shoud be shown
			$cosmAPI = new cosmAPI();
			$start = date('Y-m-d\TH:i:s\Z', time() - 1);
			$end = date('Y-m-d\TH:i:s\Z', time());
			if( ! $dataArray = $cosmAPI->parseFeed($row->feed_id, 'current_value', 0, 0, 0, 0, '') ) {
				print $row->feed_id." cosmAPI nicht gelesen!<br>";
			}
			// check if parsing the xml was successfull
			else if ( is_array($dataArray) ) {
				if ( isset($classify) ) {
					switch ($classify) {
						case "co":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, "class");
						break;
						case "no2":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, "class");
							// to do: change min and max
						break;
						case "temperature":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, "class");
						break;
						case "humidity":
							$class = classifier($dataArray['current_value'][$classify], $min, $max, "class");
							// to do: evtl. prüfen
						break;
					}
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
	$this->contentTemplate->tplReplace('url', $url);
?>