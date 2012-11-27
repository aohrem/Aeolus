<?php
	$f = fopen('tpl/main.html', 'r');
    $tpl = fread($f, filesize('tpl/main.html'));
	print $tpl;
?>