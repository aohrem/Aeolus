<?php
if ( $_GET['pass'] == 'faf01b58a34e26f5ed05a4edc5e5c3ee' ) {
	include('include/functions.inc.php');
	include('class/mysqlconnection.class.php');

	$db = new MySqlConnection();
	$query = mysql_query('SELECT `feed_id` FROM `egg`');
	while ( $egg = mysql_fetch_object($query) ) {
		open('update_egg.php?fid='.$egg->feed_id.'&pass='.$_GET['pass']);
	}
	print 'success';
}
?>