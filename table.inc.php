<?php
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
		$errormessage = '';
		
		// mark active timeframe
		include('functions.inc.php');
		$timeframe = timeframe();
		$this->contentTemplate = tplTimeframe($this->contentTemplate, $timeframe);
		$this->contentTemplate->tplReplace('feedId', $feedId);
		
		// cosm-API integration
		$cosmAPI = new CosmAPI();
		
		// set parameters for the cosm-API request
		$seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
		$start = date('Y-m-d\TH:i:s\Z', time() - $seconds[$timeframe]);
		$end = date('Y-m-d\TH:i:s\Z', time());
		$interval = array('6h' => 60, '24h' => 300, '48h' => 600, '1w' => 10800, '1m' => 604800, '3m' => 2678400);
		$limit = 1000;
		
		// fill in the parameters to read the cosm-API
		if ( ! $dataArray = $cosmAPI->parseFeed($feedId, $start, $end, $limit, $interval[$timeframe], '') ) {
			$this->contentTemplate->cleanCode('tableRow');
			$errormessage = '<div class="details errormessage">'.$GLOBALS['translation']['cosm_api_could_not_be_read'].'</div>';
		}
		// check if parsing the xml was successfull
		else if ( is_array($dataArray) ) {
			// replace metadata in template
			$metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
			foreach ( $metadata as $mdata ) {
				$this->contentTemplate->tplReplace($mdata, $dataArray[$mdata]);
				unset($dataArray[$mdata]);
			}
			
			$hidden = '';
			
			// sort sensor data by timestamp (keys of the data array)
			ksort($dataArray, SORT_NUMERIC);
			
			// iterate sensor data
			foreach ( $dataArray as $time => $val ) {
				// if there is no data, show a -
				if ( ! isset($val['co']) ) { $val['co'] = '-'; }
				if ( ! isset($val['no2']) ) { $val['no2'] = '-'; }
				if ( ! isset($val['humidity']) ) { $val['humidity'] = '-'; }
				if ( ! isset($val['temperature']) ) { $val['temperature'] = '-'; }
				
				// copy table row and fill in sensor data for one timestamp
				$this->contentTemplate->copyCode('tableRow');
				$this->contentTemplate->tplReplaceOnce('t', date('d.m.Y H:i', $time));
				$this->contentTemplate->tplReplaceOnce('co', $val['co']);
				$this->contentTemplate->tplReplaceOnce('no2', $val['no2']);
				$this->contentTemplate->tplReplaceOnce('temp', $val['temperature']);
				$this->contentTemplate->tplReplaceOnce('hum', $val['humidity']);
			}
			// delete the last row
			$this->contentTemplate->cleanCode('tableRow');
		}
		// handle errors if parsing the xml was not successfull
		else {
			$this->contentTemplate->cleanCode('tableRow');
			$this->contentTemplate->tplReplace('title', '');
			
			$hidden = ' class="hidden"';
            
			$errormessage = '<div class="details errormessage">'.$GLOBALS['translation'][$dataArray].'</div>';
		}
		
		// replace error message in template and hide details
		$this->contentTemplate->tplReplace('errormessage', $errormessage);
		$this->contentTemplate->tplReplace('hidden', $hidden);
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>