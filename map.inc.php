<?php
	$db = new Sql();
	if ($db->num_rows("SELECT `feed_id` FROM `egg`") == 0) {
		$this->contentTemplate->cleanCode('Egg');
	}
	else {
		$row = $db->fetch("SELECT `feed_id`,`lat`,`lon` FROM `egg` WHERE `feed_id`=56789");
 			$this->contentTemplate->copyCode('Egg');
			$this->contentTemplate->tplReplaceOnce('egg_lat', $row->lat);
			$this->contentTemplate->tplReplaceOnce('egg_lon', $row->lon);
			$this->contentTemplate->tplReplaceOnce('egg_fid', $row->feed_id);
		$this->contentTemplate->cleanCode('Egg');
	}
?>