<?php
	class simple_xml_extended extends SimpleXMLElement {
		public function Attribute($name) {
			foreach($this->Attributes() as $key=>$val) {
				if ($key == $name)
					return (string) $val;
			}
		}
	}
	
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
		$errormessage = '';
		
		// mark active timeframe
		include('functions.inc.php');
		$this->contentTemplate = timeframe($this->contentTemplate);
		
		$this->contentTemplate->tplReplace('feedId', $feedId);
		
		// get data from cosm api
		include('cosmapi.inc.php');
		$cosmAPI = new CosmAPI();
		if ( ! $xml = $cosmAPI->readFeed($feedId, date('Y-m-d\TH:i:s\Z', time()-21600), date('Y-m-d\TH:i:s\Z', time()), '0', '') ) {
			$errormessage = 'cosm-API konnte nicht gelesen werden.';
		}
		else {
			$this->contentTemplate->tplReplace('debugxml', htmlentities($xml));
			// parse xml document given by the cosm API
			$xml = simplexml_load_string($xml, 'simple_xml_extended');
			$xml = $xml->environment;
			
			if ( isset($xml->data) ) {
				foreach ( $xml->data as $data ) {
					$rawData = false;
					
					if ( isset($data->tag) ) {
						foreach ( $data->tag as $aqeData ) {
							if ( strpos($aqeData, 'data_origin=raw') ) {
								$rawData = true;
							}
							
							if ( strpos($aqeData, 'sensor_type') ) {
								$sensor = strtolower(str_replace('aqe:sensor_type=', '', $aqeData));
							}
						}
					}
					
					if ( ! $rawData ) {
						if ( isset($data->datapoints->value) && isset($sensor) ) {
							$i = 0;
							foreach ( $data->datapoints->value as $value ) {
								$at = strtotime(substr($value->Attribute('at'), 0, -11));
								$dataArray[$at][$sensor] = $value->__toString();
								$i++;
							}
						}
						
						if ( ! isset($sensor) ) {
							$this->contentTemplate->cleanCode('tableRow');
							$errormessage = '<h2>Beim angegebenen cosm-Feed handelt es sich nicht um ein Air Quality Egg.</h2>';
						}
					}
				}
				
				ksort($dataArray, SORT_NUMERIC);
			
				if ( isset($dataArray) ) {
					foreach ( $dataArray as $time => $val ) {
						if ( ! isset($val['co']) ) { $val['co'] = '-'; }
						if ( ! isset($val['no2']) ) { $val['no2'] = '-'; }
						if ( ! isset($val['humidity']) ) { $val['humidity'] = '-'; }
						if ( ! isset($val['temperature']) ) { $val['temperature'] = '-'; }
					
						$this->contentTemplate->copyCode('tableRow');
						$this->contentTemplate->tplReplaceOnce('t', date('d.m.Y H:i', $time));
						$this->contentTemplate->tplReplaceOnce('co', $val['co']);
						$this->contentTemplate->tplReplaceOnce('no2', $val['no2']);
						$this->contentTemplate->tplReplaceOnce('temp', $val['temperature']);
						$this->contentTemplate->tplReplaceOnce('hum', $val['humidity']);
					}
					$this->contentTemplate->cleanCode('tableRow');
				}
				else {
					$this->contentTemplate->cleanCode('tableRow');
					if ( $errormessage == '' ) { $errormessage = '<h2>F&uuml;r den angegebenen Zeitraum liegen keine Messungen vor.</h2>'; }
				}
				
			}
		}
		$this->contentTemplate->tplReplace('errormessage', $errormessage);
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>