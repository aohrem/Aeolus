<?php
    define('UNSPECIFIED', 0);
    define('RAW', 1);
    define('COMPUTED', 2);
    
    // class to read the cosm API and parse xml documents given by it
    class CosmAPI {
        // edit your cosm API key between the ' '
        private $api_key = '8XLzHihrwpa2EnIu7I3jOsPALUOSAKxmRmtXNFBBRE9FMD0g';
        
        // cosm API URL and time format
        private $url = 'http://api.cosm.com/v2/feeds';
        private $cosmTimeFormat = 'Y-m-d\TH:i:s\Z';
        private $requestUrl;
        
        // debug mode does not get the data directly from cosm but gets it from xml/test_feed_---.xml files
        // if you want to use it, set this attribute to true
        private $debug_mode = false;
        
        // parameters of the cosm API request
        private $start;
        private $end;
        private $limit;
        private $interval;
        
        private function readFeed($url) {
            // set stream options
            $opts = array(
              'http' => array('ignore_errors' => true)
            );

            // create the stream context
            $context = stream_context_create($opts);
            
            // return the source content of the requested URL
            return file_get_contents($url, false, $context);
        }
        
        // method to get contents of local xml files for the debug mode
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
        public function setRequestUrl($feedid, $start, $end, $limit, $interval, $fileExtension) {
            if ( $start != '' ) {
                // determine start date and time in cosm time format using the given UNIX timestamp
                $start = date($this->cosmTimeFormat, $start);
                $this->start = '&start='.$start;
            }
            if ( $end != '' ) {
                // determine end date and time in cosm time format using the given UNIX timestamp
                $end = date($this->cosmTimeFormat, $end);
                $this->end = '&end='.$end;
            }
            if ( $limit != '' ) {
                $this->limit = '&limit='.$limit;
            }            
            if ( $interval != '' ) {
                $this->interval = '&interval='.$interval;
            }
            
            // generate cosm request URL
            $this->requestUrl = $this->url.'/'.$feedid.'.'.$fileExtension.'?key='.$this->api_key.$this->start.$this->end.$this->limit.$this->interval;
        }
        
        // returns current cosm request URL
        public function getRequestUrl() {
            return $this->requestUrl;
        }
        
        // sends HTTP GET request to the cosm API, parses the returned XML and returns an array with the data of the feed
        // error codes:
        //  'cosm_feed_is_not_an_aqe': feed is no air quality egg
        //  'cosm_no_data_found': no data for this timeframe
        //  'cosm_no_supported_sensor': no supported sensor type found
        //  'cosm_error'
        public function parseFeed($feedid, $start, $end, $limit, $interval) {
            $this->setRequestUrl($feedid, '', '', '', '', 'xml');
            
            // if the debug mode is on, use xml/test_feed_---.xml files instead of cosm API requests
            if ( ! $this->debug_mode ) {
                $feedXml = $this->readFeed($this->requestUrl);
            }
            else {
                $feedXml = $this->readLocalXml('test_feed');
            }
            
            // generate cosm API request URL for the meta data request
            $this->setRequestUrl($feedid, $start, $end, $limit, $interval, 'xml');

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
                }
                if ( ! $aqe ) {
                    return 'cosm_feed_is_not_an_aqe';
                }
                
                // if there is feed meta data fill it into the data array
                $dataArray['title'] = isset($feedXml->environment->title) ? $feedXml->environment->title->__toString() : '';
                $dataArray['description'] = isset($feedXml->environment->description) ? $feedXml->environment->description->__toString() : '';
                $dataArray['locationName'] = isset($feedXml->environment->location->name) ? $feedXml->environment->location->name->__toString() : '';
                $dataArray['lat'] = isset($feedXml->environment->location->lat) ? $feedXml->environment->location->lat->__toString() : '';
                $dataArray['lon'] = isset($feedXml->environment->location->lon) ? $feedXml->environment->location->lon->__toString() : '';
                $dataArray['ele'] = isset($feedXml->environment->location->ele) ? $feedXml->environment->location->ele->__toString() : '';
                $dataArray['status'] = isset($feedXml->environment->status) ? $feedXml->environment->status->__toString() : '';
                if ( isset($feedXml->environment->location) ) {
                    $dataArray['exposure'] = $feedXml->environment->location->attribute('exposure') != '' ? $feedXml->environment->location->attribute('exposure') : '';
                }
            
                if ( isset($feedXml->environment->data) ) {
                    // iterate datastreams
                    foreach ( $feedXml->environment->data as $data ) {
                        $dataFeedId = $data->attribute('id');
                        
                        // default value for the data type is unspecified
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
                        
                        // take the unspecified or computed values
                        if ( $sensor != '' && ($dataType == UNSPECIFIED || $dataType == COMPUTED) ) {
                            
                            // generate new cosm API request URL for each sensor and ...
                            $datastreamRequestUrl = $this->url.'/'.$feedid.'/datastreams/'.$dataFeedId.'.xml?key='.$this->api_key.$this->start.$this->end.$this->limit.$this->interval;
                            
                            // ... if the debug mode is off, use it to read the cosm feed
                            if ( ! $this->debug_mode ) {
                                $datastreamXml = $this->readFeed($datastreamRequestUrl); 
                            }
                            // ... else use test data from xml/test_feed_---.xml files
                            else {
                                $datastreamXml = $this->readLocalXml('test_feed_'.$sensor);
                            }
                            
                            // load datastream xml string as object
                            $datastreamXml = simplexml_load_string($datastreamXml, 'SimpleXMLExtended');
                            
                            // check if there are values in the specified time frame
                            if ( isset($datastreamXml->environment->data->datapoints->value) ) {
                                $values = $datastreamXml->environment->data->datapoints->value;
                                
                                // iterate the data values
                                foreach ( $values as $value ) {
                                    // cut seconds from the time-string and convert it to a php-timestamp
                                    $at = strtotime(substr($value->attribute('at'), 0, -11));
                                        
                                    // convert ppb to ppm
                                    $value = floatval($value->__toString());
                                    if ( $sensor == 'co' || $sensor == 'no2' ) {
                                        $value /= 1000;
                                    }
                                        
                                    // save data in the data array, use timestamp as first key, sensor as second key and measured value as array value
                                    $dataArray[$at][$sensor] = $value;
                                }
                            }
                            // if there are no values in the specified time frame, return error message
                            else {
                                return 'cosm_no_data_found';
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
            // generate cosm API request URL to determine the coordinates
            $requestUrl = $this->url.'/'.$feedid.'.xml?key='.$this->api_key;
            $xml = $this->readFeed($requestUrl);
            $xml = simplexml_load_string($xml);
            
            // check if geo coordinates (latitude and longitude) are given in the returned xml and use them
            if ( isset($xml->environment->location->lat) && isset($xml->environment->location->lon) ) {
                return array($xml->environment->location->lat, $xml->environment->location->lon);
            }
            // if there are no geo coordinates, check if there is a location name
            else if ( isset($xml->environment->location->name) ) {
                $name = $xml->environment->location->name;
                
                // if there is a location name, determine it's geo location using the OSM Nominatim API
                include('class/nominatimapi.class.php');
                $nominatimAPI = new NominatimAPI();
                
                return $nominatimAPI->getCoordinates($name);
            }
            else {
                return false;
            }
        }
    }
?>