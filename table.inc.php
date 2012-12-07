<?php
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
		// mark active timeframe
		include('functions.inc.php');
		$this->contentTemplate = timeframe($this->contentTemplate);
		
		$this->contentTemplate->tplReplace('feedId', $feedId);
		
		// get data from cosm api
		include('cosmapi.inc.php');
		$cosm = new CosmAPI();
		print $cosm->readFeed($feedId, '', '', '');
		
		// Template Test
		for ( $i = 0; $i < 5; $i++ ) {
			$this->contentTemplate->copyCode('tableRow');
			$this->contentTemplate->tplReplace('t', date('d.m.Y H:i', time()));
			$this->contentTemplate->tplReplace('co', '1');
			$this->contentTemplate->tplReplace('no2', '3');
			$this->contentTemplate->tplReplace('temp', '3');
			$this->contentTemplate->tplReplace('hum', '7');
		}
		$this->contentTemplate->cleanCode('tableRow');
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>