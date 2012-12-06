<?php
	$standardSensor = 'co';
	
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
		// mark active timeframe
		include('functions.inc.php');
		$this->contentTemplate = timeframe($this->contentTemplate);
		
		$this->contentTemplate->tplReplace('feedId', $feedId);
		
		if ( ! isset($_GET['sensor']) ) {
			$sensor = $standardSensor;
		}
		else {
			$sensor = htmlentities($_GET['sensor']);
		}
		
		$this->contentTemplate->tplReplace('sensor', $sensor);
		
		$cssActive = ' class="active"';
		$sensors = array('co', 'no2', 'temp');
		foreach ( $sensors as $val ) {
			if ( $val == $sensor ) {
				$this->contentTemplate->tplReplace($val.'_active', $cssActive);
			}
			else {
				$this->contentTemplate->tplReplace($val.'_active', '');
			}
		}
		
		if ( $sensor == 'hum' ) {
			$this->contentTemplate->tplReplace('hum_active', 'active ');
		}
		else {
			$this->contentTemplate->tplReplace('hum_active', '');
		}
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>