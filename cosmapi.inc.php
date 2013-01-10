<?php
	define('UNSPECIFIED', 0);
	define('RAW', 1);
	define('COMPUTED', 2);
	
	// class to read the cosm API and parse xml documents given by it
	class CosmAPI {
		private $url = 'http://api.cosm.com/v2/feeds';
		private $api_key = '8XLzHihrwpa2EnIu7I3jOsPALUOSAKxmRmtXNFBBRE9FMD0g';
		private $request_url;
		private $debug_mode = true;
		
		private function readFeed($url) {
			// set stream options
			$opts = array(
			  'http' => array('ignore_errors' => true)
			);

			// create the stream context
			$context = stream_context_create($opts);
			
			return file_get_contents($url, false, $context);
		}
		
		private function readXml($filename) {
			if ( file_exists('xml/'.$filename.'.xml') ) {
				$f = fopen('xml/'.$filename.'.xml', 'r');
				return fread($f, filesize('xml/'.$filename.'.xml'));
			}
			else {
				return false;
			}
		}
		
		// sends HTTP GET request to the cosm API, parses the returned XML and returns an array with the data of the feed
		// error-codes: 1 - feed is no air quality egg, 2 - no data for this timeframe, 3 - no supported sensor type found, 4 - cosm error
		public function parseFeed($feedid, $start, $end, $limit, $interval, $duration) {
			// set parameters if they are not empty
			if ( $start != '' ) {
				$start = '&start='.$start;
			}
			if ( $end != '' ) {
				$end = '&end='.$end;
			}
			if ( $limit != '' ) {
				$limit = '&limit='.$limit;
			}			
			if ( $interval != '' ) {
				$interval = '&interval='.$interval;
			}
			if ( $duration != '' ) {
				$duration = '&duration='.$duration;
			}
			
			$requestUrl = $this->url.'/'.$feedid.'.xml?key='.$this->api_key;
			
			if ( ! $this->debug_mode ) {
				$feedXml = $this->readFeed($requestUrl);
			}
			else {
				$feedXml = $this->readXml('test_feed');
			}
			
			// print '<pre>'.htmlentities($feedXml).'</pre>';
			
			// load xml string as object
			$feedXml = simplexml_load_string($feedXml, 'SimpleXMLExtended');
			
			// check if its an air quality egg
			$aqe = false;
			$aqeTags = array('airqualityegg', 'air quality egg', 'aqe');
			// iterate feed tags
			foreach ( $feedXml->environment->tag as $tag ) {
				foreach ( $aqeTags as $tagVal ) {
					if ( strstr(strtolower($tag), strtolower($tagVal)) ) {
						$aqe = true;
						break;
					}
				}
			}
			if ( ! $aqe ) {
				return 1;
			}
			
			$dataArray['title'] = isset($feedXml->environment->title) ? htmlentities($feedXml->environment->title).' - ' : '';
			$dataArray['description'] = isset($feedXml->environment->description) ? htmlentities($feedXml->environment->description) : 'Keine Beschreibung verf&uuml;gbar.';
			$dataArray['locationName'] = isset($feedXml->environment->location->name) ? htmlentities($feedXml->environment->location->name) : 'nicht angegeben';
			$dataArray['lat'] = isset($feedXml->environment->location->lat) ? $feedXml->environment->location->lat.'&deg;' : 'nicht angegeben';
			$dataArray['lon'] = isset($feedXml->environment->location->lon) ? $feedXml->environment->location->lon.'&deg;' : 'nicht angegeben';
			$dataArray['ele'] = isset($feedXml->environment->location->ele) ? $feedXml->environment->location->ele : 'nicht angegeben';
			$dataArray['status'] = isset($feedXml->environment->status) ? $feedXml->environment->status->__toString() : 'unbekannt';
			$dataArray['exposure'] = $feedXml->environment->location->attribute('exposure') != '' ? $feedXml->environment->location->attribute('exposure') : 'unbekannt';
			
			if ( isset($feedXml->environment->data) ) {
				// iterate datastreams
				foreach ( $feedXml->environment->data as $data ) {
					$dataFeedId = $data->attribute('id');
					$dataType = UNSPECIFIED;
					$sensor = '';
					
					if ( isset($data->tag) ) {
						// iterate tags of the datastream
						foreach ( $data->tag as $aqeData ) {
							// check if its the raw data
							if ( strstr($aqeData, 'data_origin=raw') ) {
								$dataType = RAW;
							}
							// check if its computed
							else if ( strstr($aqeData, 'data_origin=computed') ) {
								$dataType = COMPUTED;
							}
							
							// look for the sensor type
							if ( strstr($aqeData, 'sensor_type') ) {
								$sensor = strtolower(str_replace('aqe:sensor_type=', '', $aqeData));
							}
						}
					}
					
					// check if no sensor type was found in the datastream tags ...
					if ( $sensor == '' ) {
						// ... if not, check the datafeed id for a given sensor type
						$sensorTypes = array('co' => 'co', 'no2' => 'no2', 'temp' => 'temperature', 'hum' => 'humidity');
						foreach ( $sensorTypes as $type => $longType ) {
							if ( strstr(strtolower($dataFeedId), $type) ) { $sensor = $longType; }
						}
					}
					
					if ( $sensor != '' &&
						( ( $sensor != 'no2' && ($dataType == UNSPECIFIED || $dataType == COMPUTED) ) ||
						  ( $sensor == 'no2' && ($dataType == UNSPECIFIED || $dataType == RAW) ) ) ) {
						
						$datastreamRequestUrl = $this->url.'/'.$feedid.'/datastreams/'.$dataFeedId.'.xml?key='.$this->api_key.$start.$end.$limit.$interval.$duration;
						if ( ! $this->debug_mode ) {
							$datastreamXml = $this->readFeed($datastreamRequestUrl); 
						}
						else {
							$datastreamXml = $this->readXml('test_feed_'.$sensor);
						}
						
						// print '<br><br><pre>'.htmlentities($datastreamXml).'</pre>';
						
						// load datastream xml string as object
						$datastreamXml = simplexml_load_string($datastreamXml, 'SimpleXMLExtended');
						
						if ( isset($datastreamXml->environment->data->datapoints->value) ) {
							$values = $datastreamXml->environment->data->datapoints->value;
						
							foreach ( $values as $value ) {
								// cut seconds from the time-string and convert it to a php-timestamp
								$at = strtotime(substr($value->attribute('at'), 0, -11));
								
								// save data in the data array, use timestamp as first key, sensor as second key and measured value as array value
								$dataArray[$at][$sensor] = $value->__toString();
							}
						}
						else if ( isset($datastreamXml->title) ) {
							return 4;
						}
						else {
							return 2;
						}
					}
				}
			}
			// no supported sensor type found
			else {
				return 3;
			}
			
			return $dataArray;
		}
	}

	// child class of SimpleXMLElement with method to get attributes of xml tags by their name
	class SimpleXMLExtended extends SimpleXMLElement {
		public function attribute($name) {
			foreach($this->Attributes() as $key=>$val) {
				if ($key == $name)
					return (string) $val;
			}
		}
	}
?>