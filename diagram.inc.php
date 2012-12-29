<?php
	$standardSensor = 'co';
	
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
		// mark active timeframe
		include('functions.inc.php');
		$timeframe = timeframe();
		$this->contentTemplate = tplTimeframe($this->contentTemplate, $timeframe);
		
		$this->contentTemplate->tplReplace('feedId', $feedId);
		
		// set standard sensor if no sensor given
		if ( ! isset($_GET['sensor']) ) {
			$sensor = $standardSensor;
		}
		else {
			$sensor = htmlentities($_GET['sensor']);
		}
		
		$this->contentTemplate->tplReplace('sensor', $sensor);
		
		// mark active sensor
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
		
		// get data ... cosm api
		
		// Diagram Test
		for ( $i = 0; $i < 5; $i++ ) {
			$this->contentTemplate->copyCode('diagramData');
			$this->contentTemplate->tplReplace('x', 570);
			$this->contentTemplate->tplReplace('y', 200);
		}
		$this->contentTemplate->cleanCode('diagramData');
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>