<?php
    open('update_egg.php?fid=75842');
    $mySqlConnection = new MySqlConnection();
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
		}
		$this->contentTemplate->cleanCode('Egg');
	}
	//$this->contentTemplate->tplReplace('url', $url);
?>