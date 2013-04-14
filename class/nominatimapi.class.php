<?php
    class NominatimAPI {
        private $url = 'http://nominatim.openstreetmap.org/search';

        // method returns coordinates of the geo location specified in $query as an array
        // array[0] = latitude, array[1] = longitude
        public function getCoordinates($query) {
            $requestUrl = $this->url.'?format=xml&limit=1&q='.$query;

            $xml = $this->readFeed($requestUrl);

            $xml = simplexml_load_string($xml, 'SimpleXMLExtended');

            // if coordinates were found, return them ...
            if ( isset($xml->environment->place) ) {
                $latitude = $xml->place->attribute('lat');
                $longitude = $xml->place->attribute('lon');

                return array($latitude, $longitude);
            }
            // ... if not, return false
            else {
                return false;
            }
        }

        // private method to get the source contents of a HTTP GET request
        private function readFeed($url) {
            // set stream options
            $opts = array(
              'http' => array('ignore_errors' => true)
            );

            // create the stream context
            $context = stream_context_create($opts);

            return file_get_contents($url, false, $context);
        }
    }
?>