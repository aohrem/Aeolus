<?php
	// find URL of the current page
	$url = $_SERVER['REQUEST_URI'];
	$url_parts = explode('/', $url);
    $url = $url_parts[sizeof($url_parts)-1];
	// delete former input for classify in URL
	if (isset($_GET['classify'])){
		$classifier = htmlentities(mysql_real_escape_string($_GET['classify']));
		$this->contentTemplate->tplReplace('show', 'class="show"');
		$this->contentTemplate->tplReplace('classifier', $classifier);
		$url = str_replace('&classify='.$classifier,'',$url);
	}
	else {
		$this->contentTemplate->tplReplace('show', '');
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
			// Check if classification shoud be shown
			if (isset($_GET['classify'])) {
				$cosmAPI = new cosmAPI();
				$start = date('Y-m-d\TH:i:s\Z', time() - 1);
				$end = date('Y-m-d\TH:i:s\Z', time());
				if( ! $data_array = $cosmAPI->parseFeed($row->feed_id, '', $end, 1, 86400, '1minutes') ) {
					print $row->feed_id." cosmAPI nicht gelesen!<br>";
				}
				// check if parsing the xml was successfull
				else if ( is_array($data_array) ) {
					print "<p><b>".$row->feed_id."</b><br>";
					var_dump(end($data_array));
					/* print "<br>".
					var_dump(end($data_array)); */
					print "</p>";
				}
				else {
					print $row->feed_id." fail!!!<br>";
				}
				// distinction of classes for classifying
				$this->contentTemplate->tplReplaceOnce('egg_color', "'class1'");
			}
			else {
				$this->contentTemplate->tplReplaceOnce('egg_color', "'noval'");
			}
		}
		$this->contentTemplate->cleanCode('Egg');
	}
	$this->contentTemplate->tplReplace('url', $url);
?>