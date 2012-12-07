<?php
class CosmAPI {
	private $url = 'http://api.cosm.com/v2/feeds';
	private $api_key = 'S_fFBZ0WcgkikDf29YcwEnVtLmiSAKx1RmgvUFQ0bndFZz0g';
	private $request_url;
	
	public function readFeed($feedid, $start, $end, $interval, $duration) {
		// set stream options
		$opts = array(
		  'http' => array('ignore_errors' => true)
		);

		// create the stream context
		$context = stream_context_create($opts);
		
		if ( $start != '' ) {
			$start = '&start='.$start;
		}
		if ( $end != '' ) {
			$end = '&end='.$end;
		}
		if ( $interval != '' ) {
			$interval = '&interval='.$interval;
		}
		if ( $duration != '' ) {
			$duration = '&duration='.$duration;
		}
		
		// open the file using the defined context
		return file_get_contents($this->url.'/'.$feedid.'.xml?key='.$this->api_key.$start.$end.$interval.$duration, false, $context);
	}
}
?>