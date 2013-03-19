<?php
	// find URL of the current page
	$url = $_SERVER['REQUEST_URI'];
	$url_parts = explode('/', $url);
    $url = $url_parts[sizeof($url_parts)-1];
	// delete former input for classify in URL
	if (isset($_GET['classify'])){
		$classify = htmlentities(mysql_real_escape_string($_GET['classify']));
		$this->contentTemplate->tplReplace('show', 'class="show"');
		$this->contentTemplate->tplReplace('classify', $classify);
		$url = str_replace('&classify='.$classify,'',$url);
	}
	else {
		$this->contentTemplate->tplReplace('show', '');
	}
	
 	function classifier($wert, $min, $max) {
		$spann = $max - $min;
		$schritt = floor($spann / 5);
		if ($wert<=$min) {
			return 1;
		}
		else if ($wert>$max) {
			return 5;
		}
		else {
			return ceil($wert/$schritt);
		}
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
							$class = "class".classifier($dataArray['current_value'][$classify], 0, 30);
							// to do: change min and max
						break;
						case "no2":
							$class = "class".classifier($dataArray['current_value'][$classify], 0, 30);
							// to do: change min and max
						break;
						case "temperature":
							$class = "class".classifier($dataArray['current_value'][$classify], 0, 30);
						break;
						case "humidity":
							$class = "class".classifier($dataArray['current_value'][$classify], 20, 80);
							// to do: evtl. prüfen
						break;
					}
				}
			}
			$this->contentTemplate->tplReplaceOnce('egg_color', "'".$class."'");
		}
		$this->contentTemplate->cleanCode('Egg');
	}
	$this->contentTemplate->tplReplace('url', $url);
?>