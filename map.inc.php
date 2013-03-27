<?php
 	function classifier($value, $min, $max, $return) {
		$deviation = $max - $min;
		$step = floor($deviation / 4);
		if ($return == "class") {
			if ($value <= $min) {
				return 1;
			}
			else if ($value > $max) {
				return 5;
			}
			else {
				return ceil($value / $step);
			}
		}
		else if ($return == "classes") {
			$classes = array();
			$classes["class1"] = "< ". $min ."{unit}";
			$classes["class2"] = $min ."{unit} - ". ($min + $step) ."{unit}";
			$classes["class3"] = ($min + $step) ."{unit} - ". ($min + 2 * $step) ."{unit}";
			$classes["class4"] = ($min + 2 * $step) ."{unit} - ". ($min + 3 * $step) ."{unit}";
			$classes["class5"] =  "> ". $max ."{unit}";
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
		$this->contentTemplate->tplReplace('show', 'class="show"');
		$this->contentTemplate->tplReplace('classify', $classify);
		$url = str_replace('&classify='.$classify,'',$url);
		switch ( $classify ) {
			case "co":
				// TODO: min und max anpassen
				$min = 0;
				$max = 0;
				$unit = "ppm";
				$classes = classifier(0, $min, $max, "classes");
			break;
			case "no2":
				// TODO: min und max anpassen
				$min = 0;
				$max = 0;
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
				$max = 80;
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
			if ( isset($classify) ) {
				$cosmAPI = new cosmAPI();
				$start = date('Y-m-d\TH:i:s\Z', time() - 1);
				$end = date('Y-m-d\TH:i:s\Z', time());
				if( ! $dataArray = $cosmAPI->parseFeed($row->feed_id, 'current_value', 0, 0, 0, 0, '') ) {
					print $row->feed_id." cosmAPI nicht gelesen!<br>";
				}
				// check if parsing the xml was successfull
				else if ( is_array($dataArray) ) {
					$this->contentTemplate->tplReplaceOnce('egg_value', $dataArray['current_value'][$classify]);
					switch ($classify) {
						case "co":
							$class = "class".classifier($dataArray['current_value'][$classify], $min, $max, "class");
						break;
						case "no2":
							$class = "class".classifier($dataArray['current_value'][$classify], $min, $max, "class");
							// to do: change min and max
						break;
						case "temperature":
							$class = "class".classifier($dataArray['current_value'][$classify], $min, $max, "class");
						break;
						case "humidity":
							$class = "class".classifier($dataArray['current_value'][$classify], $min, $max, "class");
							// to do: evtl. pr�fen
						break;
					}
				}
			}
			else {
				$this->contentTemplate->tplReplaceOnce('egg_value', 0);
			}
			$this->contentTemplate->tplReplaceOnce('egg_color', "'".$class."'");
		}
		$this->contentTemplate->cleanCode('Egg');
	}
	$this->contentTemplate->tplReplace('url', $url);
?>