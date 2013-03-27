<?php
	define('UNSPECIFIED', 0);
	define('RAW', 1);
	define('COMPUTED', 2);
	
	// class to read the cosm API and parse xml documents given by it
	class CosmAPI {
		private $url = 'http://api.cosm.com/v2/feeds';
		private $api_key = '8XLzHihrwpa2EnIu7I3jOsPALUOSAKxmRmtXNFBBRE9FMD0g';
		private $requestUrl;
		private $debug_mode = false;
        
        private $start;
        private $end;
        private $limit;
        private $interval;
        private $duration;
		
		private function readFeed($url) {
			// set stream options
			$opts = array(
			  'http' => array('ignore_errors' => true)
			);

			// create the stream context
			$context = stream_context_create($opts);
			
			return file_get_contents($url, false, $context);
		}
		
		private function readLocalXml($filename) {
			if ( file_exists('xml/'.$filename.'.xml') ) {
				$f = fopen('xml/'.$filename.'.xml', 'r');
				return fread($f, filesize('xml/'.$filename.'.xml'));
			}
			else {
				return false;
			}
		}
        
        // set parameters if they are not empty
        public function setRequestUrl($feedid, $start, $end, $limit, $interval, $duration, $fileExtension) {
			if ( $start != '' ) {
				$this->start = '&start='.$start;
			}
			if ( $end != '' ) {
				$this->end = '&end='.$end;
			}
			if ( $limit != '' ) {
				$this->limit = '&limit='.$limit;
			}			
			if ( $interval != '' ) {
				$this->interval = '&interval='.$interval;
			}
			if ( $duration != '' ) {
				$this->duration = '&duration='.$duration;
			}
			
			$this->requestUrl = $this->url.'/'.$feedid.'.'.$fileExtension.'?key='.$this->api_key.$this->start.$this->end.$this->limit.$this->interval.$this->duration;
        }
        
        public function getRequestUrl() {
            return $this->requestUrl;
        }
        
		// sends HTTP GET request to the cosm API, parses the returned XML and returns an array with the data of the feed
        // supported values for $values:
        //  'all_values': returns all values of the given timeframe
        //  'current_value': returns only the current value
        // error codes:
        //  'cosm_feed_is_not_an_aqe': feed is no air quality egg
        //  'cosm_no_data_found': no data for this timeframe
        //  'cosm_no_supported_sensor': no supported sensor type found
        //  'cosm_error'
		public function parseFeed($feedid, $returnedValues, $start, $end, $limit, $interval, $duration) {
			$this->setRequestUrl($feedid, '', '', '', '', '', 'xml');
			
			if ( ! $this->debug_mode ) {
				$feedXml = $this->readFeed($this->requestUrl);
			}
			else {
				$feedXml = $this->readLocalXml('test_feed');
			}
            
			$this->setRequestUrl($feedid, $start, $end, $limit, $interval, $duration, 'xml');
			
			if ( strpos($feedXml, '<error>') ) {
				return 'cosm_error';
			}
			else {
				// load xml string as object
				$feedXml = simplexml_load_string($feedXml, 'SimpleXMLExtended');
				
				// check if its an air quality egg
				$aqe = false;
				$aqeTags = array('airqualityegg', 'air quality egg', 'aqe');
				// iterate feed tags
				if ( isset($feedXml->environment->tag) ) {
					foreach ( $feedXml->environment->tag as $tag ) {
						foreach ( $aqeTags as $tagVal ) {
							if ( strstr(strtolower($tag), strtolower($tagVal)) ) {
								$aqe = true;
								break;
							}
						}
					}
                
					// if there is feed meta data fill it into the data array
					$dataArray['title'] = isset($feedXml->environment->title) ? htmlentities($feedXml->environment->title).' - ' : '';
					$dataArray['description'] = isset($feedXml->environment->description) ? htmlentities($feedXml->environment->description) : $GLOBALS['translation']['no_description_available'];
					$dataArray['locationName'] = isset($feedXml->environment->location->name) ? htmlentities($feedXml->environment->location->name) : $GLOBALS['translation']['not_available'];
					$dataArray['lat'] = isset($feedXml->environment->location->lat) ? $feedXml->environment->location->lat.'&deg;' : $GLOBALS['translation']['not_available'];
					$dataArray['lon'] = isset($feedXml->environment->location->lon) ? $feedXml->environment->location->lon.'&deg;' : $GLOBALS['translation']['not_available'];
					$dataArray['ele'] = isset($feedXml->environment->location->ele) ? $feedXml->environment->location->ele : $GLOBALS['translation']['not_available'];
					$dataArray['status'] = isset($feedXml->environment->status) ? $feedXml->environment->status->__toString() : $GLOBALS['translation']['unknown'];
					if ( isset($feedXml->environment->location) ) {
						$dataArray['exposure'] = $feedXml->environment->location->attribute('exposure') != '' ? $feedXml->environment->location->attribute('exposure') : $GLOBALS['translation']['unknown'];
					}
				}
            
				if ( ! $aqe ) {
					return 'cosm_feed_is_not_an_aqe';
				}
            
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
						
							if ( $returnedValues == 'all_values' ) {
								$datastreamRequestUrl = $this->url.'/'.$feedid.'/datastreams/'.$dataFeedId.'.xml?key='.$this->api_key.$this->start.$this->end.$this->limit.$this->interval.$this->duration;
								if ( ! $this->debug_mode ) {
									$datastreamXml = $this->readFeed($datastreamRequestUrl); 
								}
								else {
									$datastreamXml = $this->readLocalXml('test_feed_'.$sensor);
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
									return 'cosm_error';
								}
								else {
									return 'cosm_no_data_found';
								}
							}
							else if ( $returnedValues == 'current_value' )  {
								$dataArray['current_value'][$sensor] = $data->current_value->__toString();
							}
						}
					}
				}
				// no supported sensor type found
				else {
					return 'cosm_no_supported_sensor';
				}
			
				return $dataArray;
			}
		}
	
		// returns geo coordinates of an air quality egg
		public function getEggCoordinates($feedid) {
			$requestUrl = $this->url.'/'.$feedid.'.xml?key='.$this->api_key;
			$xml = $this->readFeed($requestUrl);
			$xml = simplexml_load_string($xml);
			
			if ( isset($xml->environment->location->lat) && isset($xml->environment->location->lon) ) {
				return array('lat' => $xml->environment->location->lat, 'lon' => $xml->environment->location->lon);
			}
			else if ( isset($xml->environment->location->name) ) {
				$name = $xml->environment->location->name;
				
				include('nominatimapi.class.php');
				$nominatimAPI = new NominatimAPI();
				
				return $nominatimAPI->getCoordinates($name);
			}
			else {
				return false;
			}
		}
	}
?>