<?php
	define('UNSPECIFIED', 0);
	define('RAW', 1);
	define('COMPUTED', 2);
	
	// class to read the cosm API and parse xml documents given by it
	class CosmAPI {
		private $url = 'http://api.cosm.com/v2/feeds';
		private $api_key = '8XLzHihrwpa2EnIu7I3jOsPALUOSAKxmRmtXNFBBRE9FMD0g';
		private $request_url;
		
		// sends HTTP GET request to the cosm API and returns the answered xml with the feed data
		public function readFeed($feedid, $start, $end, $perPage, $interval, $duration) {
			// set stream options
			$opts = array(
			  'http' => array('ignore_errors' => true)
			);

			// create the stream context
			$context = stream_context_create($opts);
			
			// set parameters if they are not empty
			if ( $start != '' ) {
				$start = '&start='.$start;
			}
			if ( $end != '' ) {
				$end = '&end='.$end;
			}
			if ( $perPage != '' ) {
				$perPage = '&per_page='.$perPage;
			}			
			if ( $interval != '' ) {
				$interval = '&interval='.$interval;
			}
			if ( $duration != '' ) {
				$duration = '&duration='.$duration;
			}
			
			$requestUrl = $this->url.'/'.$feedid.'.xml?key='.$this->api_key.$start.$end.$perPage.$interval.$duration;
			
			// open the file using the defined context
			return file_get_contents($requestUrl, false, $context);
		}
		
		// parses cosm feed given in xml format and returns data as an array
		// error-codes: 1 - feed is no air quality egg, 2 - no data for this timeframe, 3 - no supported sensor type found
		public function parseXML($xml) {
			// load xml string as object
			$xml = simplexml_load_string($xml, 'simple_xml_extended');
			
			if ( isset($xml->environment->data) ) {
				$aqe = false;
				$aqeTags = array('airqualityegg', 'air quality egg', 'aqe');
				// iterate feed tags
				foreach ( $xml->environment->tag as $tag ) {
					foreach ( $aqeTags as $tagVal ) {
						if ( strstr(strtolower($tag), strtolower($tagVal)) ) {
							$aqe = true;
						}
					}
				}
				
				// iterate datastreams
				foreach ( $xml->environment->data as $data ) {
					$dataType = UNSPECIFIED;
					$sensor = '';
					
					if ( isset($data->tag) ) {
						// iterate tags of the datastream
						foreach ( $data->tag as $aqeData ) {
							// check if its the raw data
							if ( strpos($aqeData, 'data_origin=raw') ) {
								$dataType = RAW;
							}
							// check if its computed
							else if ( strpos($aqeData, 'data_origin=computed') ) {
								$dataType = COMPUTED;
							}
							
							// look for the sensor type
							if ( strpos($aqeData, 'sensor_type') ) {
								$sensor = strtolower(str_replace('aqe:sensor_type=', '', $aqeData));
							}
						}
					}
					
					// check if no sensor type was found in the datastream tags ...
					if ( $sensor == '' ) {
						// ... if not, check the datafeed id for a given sensor type
						$sensorTypes = array('co' => 'co', 'no2' => 'no2', 'temp' => 'temperature', 'hum' => 'humidity');
						foreach ( $sensorTypes as $type => $longType ) {
							$dataFeedId = strtolower($data->attribute('id'));
							if ( strstr($dataFeedId, $type) ) { $sensor = $longType; }
						}
					}
					
					// check if its an air quality egg
					if ( ! $aqe ) {
						return 1;
					}
					// check if no sensor and no data are given for this timeframe
					else if ( $sensor != '' && ! isset($data->datapoints->value) ) {
						return 2;
					}
					// check if data is given for this timeframe
					else if ( ! isset($data->datapoints->value) ) {
						break;
					}
					// fill data array
					else if ( ( $sensor != 'no2' && ($dataType == UNSPECIFIED || $dataType == COMPUTED ) ) || ( $sensor == 'no2' && ($dataType == UNSPECIFIED || $dataType == RAW) ) ) {
						foreach ( $data->datapoints->value as $value ) {
							// cut seconds from the time-string and convert it to a php-timestamp
							$at = strtotime(substr($value->attribute('at'), 0, -11));
							
							// save data in the data array, use timestamp as first key, sensor as second key and measured value as array value
							$dataArray[$at][$sensor] = $value->__toString();
						}	
					}
				}
				
				return $dataArray;
			}
			// no supported sensor type found
			else {
				return 3;
			}
		}
	}

	// child class of SimpleXMLElement with method to get attributes of xml tags by their name
	class simple_xml_extended extends SimpleXMLElement {
		public function attribute($name) {
			foreach($this->Attributes() as $key=>$val) {
				if ($key == $name)
					return (string) $val;
			}
		}
	}
?>