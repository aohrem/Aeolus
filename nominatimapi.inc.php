<?php
	class NominatimAPI {
		private $url = 'http://nominatim.openstreetmap.org/search';
		
		private function readFeed($url) {
			// set stream options
			$opts = array(
			  'http' => array('ignore_errors' => true)
			);

			// create the stream context
			$context = stream_context_create($opts);
			
			return file_get_contents($url, false, $context);
		}
		
		public function getCoordinates($query) {
			$requestUrl = $this->url.'?format=xml&limit=1&q='.$query;
			
			print $xml = $this->readFeed($requestUrl);
			
			$xml = simplexml_load_string($xml, 'SimpleXMLExtended');
			
			if ( isset($xml->place) ) {
				$latitude = $xml->place->attribute('lat');
				$longitude = $xml->place->attribute('lon');
				
				return array($latitude, $longitude);
			}
			else {
				return false;
			}
		}
	}
?>