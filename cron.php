<?php
include('include/functions.inc.php');
include('class/mysqlconnection.class.php');
$db = new MySqlConnection();
$query = mysql_query('SELECT `feed_id` FROM `egg`');
while ( $egg = mysql_fetch_object($query) ) {
    open('update_egg.php?fid='.$egg->feed_id);
}
print 'success';
?>