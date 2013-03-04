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