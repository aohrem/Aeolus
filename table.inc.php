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
		$interval = array('6h' => '', '24h' => '', '48h' => '', '1w' => 3600, '1m' => 10800, '3m' => 43200);
		$perPage = 1000;
		
		// fill in the parameters to read the cosm-API
		if ( ! $xml = $cosmAPI->readFeed($feedId, $start, $end, $perPage, $interval[$timeframe], '') ) {
			$this->contentTemplate->cleanCode('tableRow');
			$errormessage = '<div class="details errormessage">cosm-API konnte nicht gelesen werden.</div>';
		}
		else {
			// parse xml string
			$dataArray = $cosmAPI->parseXML($xml);
			
			// replace metadata in template
			$metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
			foreach ( $metadata as $mdata ) {
				$this->contentTemplate->tplReplace($mdata, $dataArray[$mdata]);
				unset($dataArray[$mdata]);
			}
			
			// replace debugxml in template
			$this->contentTemplate->tplReplace('debugxml', htmlentities($xml));
			
			$hidden = '';
			
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
				
				$hidden = ' class="hidden"';
				
				switch ( $dataArray ) {
					case 1:
						$errormessage = '<div class="details errormessage">Beim angegebenen cosm-Feed handelt es sich nicht um ein Air Quality Egg.</div>';
					break;
					case 2:
						$errormessage = '<div class="details errormessage">F&uuml;r den angegebenen Zeitraum liegen keine Messungen vor.</div>';
					break;
					case 3:
						$errormessage = '<div class="details errormessage">Es wurde keiner der unterst&uuml;tzten Sensortypen gefunden.</div>';
					break;
				}
			}
		}
		
		// replace error message in template and hide details
		$this->contentTemplate->tplReplace('errormessage', $errormessage);
		$this->contentTemplate->tplReplace('hidden', $hidden);
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>