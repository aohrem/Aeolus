<?php
    // open a new MySQL connection to get the coordinates of all registered eggs
    $mySqlConnection = new MySqlConnection();
    $query = mysql_query('SELECT `feed_id`,`lat`,`lon` FROM `egg`');
    
    // if there are no eggs registered, delete the addEgg() command
    if (mysql_num_rows($query) == 0) {
        $this->contentTemplate->cleanCode('Egg');
    }
    else {
        // iterate eggs and replace their coordinates
        while ($row = mysql_fetch_object($query)) {
            $this->contentTemplate->copyCode('Egg');
            $this->contentTemplate->tplReplaceOnce('egg_lat', $row->lat);
            $this->contentTemplate->tplReplaceOnce('egg_lon', $row->lon);
            $this->contentTemplate->tplReplaceOnce('egg_fid', $row->feed_id);
        }
        $this->contentTemplate->cleanCode('Egg');
    }
?>