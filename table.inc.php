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
		include('cosmapi.inc.php');
		$cosmAPI = new CosmAPI();
		
		// set parameters for the cosm-API request
		$seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
		$start = date('Y-m-d\TH:i:s\Z', time() - $seconds[$timeframe]);
		$end = date('Y-m-d\TH:i:s\Z', time());
		$interval = array('6h' => 0, '24h' => 120, '48h' => 240, '1w' => 420, '1m' => 3600, '3m' => 10800);
		$perPage = 500;
		
		// fill in the parameters to read the cosm-API
		if ( ! $xml = $cosmAPI->readFeed($feedId, $start, $end, $perPage, $interval[$timeframe], '') ) {
			$this->contentTemplate->cleanCode('tableRow');
			$errormessage = '<h2>cosm-API konnte nicht gelesen werden.</h2>';
		}
		else {
			// parse xml string
			$dataArray = $cosmAPI->parseXML($xml);
			
			// replace debugxml in template
			$this->contentTemplate->tplReplace('debugxml', htmlentities($xml));
			
			// check if parsing the xml was successfull
			if ( is_array($dataArray) ) {
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
				
				switch ( $dataArray ) {
					case 1:
						$errormessage = '<h2>Beim angegebenen cosm-Feed handelt es sich nicht um ein Air Quality Egg.</h2>';
					break;
					case 2:
						$errormessage = '<h2>F&uuml;r den angegebenen Zeitraum liegen keine Messungen vor.</h2>';
					break;
					case 3:
						$errormessage = '<h2>Es wurden nicht alle erforderlichen Sensortypen gefunden.</h2>';
					break;
				}
			}
		}
		
		// replace error message in template
		$this->contentTemplate->tplReplace('errormessage', $errormessage);
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>