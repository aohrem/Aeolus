<?php
	
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
		// mark active timeframe
		include('functions.inc.php');
		$this->contentTemplate = timeframe($this->contentTemplate);
		
		$this->contentTemplate->tplReplace('feedId', $feedId);
		
		// get data ... cosm api
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>