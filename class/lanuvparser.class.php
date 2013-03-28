<?php
class LanuvParser {
    private $lanuvUrl = 'http://www.lanuv.nrw.de/luft/temes/heut/';
    private $fileExtension = '.htm';
    
    private $sensors = array('ozone' => 2, 'n' => 3, 'no2' => 4, 'ltem' => 5, 'wri' => 6, 'wges' => 7, 'rfue' => 8, 'so2' => 9,'pm10' => 10);
    private $stationCode;
    private $sensorId;
    
    private $dataArray;
    
    public function __construct($stationCode) {
        $this->stationCode = $stationCode;
        $this->getValueGroup($this->parseLanuv());
    }
    
    // function for parsing Lanuv HTML Code and removing unnecessary space characters
    // returns an array which contains all values stored between "<td class"mw_%"></td>"-tags of the Lanuv HTML Code 
    private function parseLanuv() {
        $html = file_get_contents($this->lanuvUrl.$this->stationCode.$this->fileExtension);
        
        // replace empty value tags by #s to simplify further processing
        $html = str_replace('     ', 'null', $html);
        $html = str_replace(' ', '', $html);
        
        // regular expression for parsing the html code
        $regexp = '/<tdclass="[_a-z0-1]*">(.*?)<\/td>/';
        
        // preg_match_all() returns an associative array in $array[1] containing all values that are stored within the regular expression
        preg_match_all($regexp, $html, $array);
        
        // since both dimensions of the array are identical only the first dimension is further needed
        $dataArray = $array[1];
        
		// removing non-values at the end of the array
		for ( $i = 0; $i <= 10; $i++ ) {
			array_pop($dataArray);
        }
        
        return $dataArray;
	}


    // returns grouped values of a concret sensor in an associative array combined with the corresponding time
    private function getValueGroup($dataArray) {
        
		foreach ( $this->sensors as $sensorString => $sensorId ) {
			$i = 0;
        
			// current index of the array
			$r = 0;
			while ( $i < sizeof($dataArray) ) {
				// index of the time at each measuring block
				$time = $i;
            
				// index of demanded sensortype
				$value = $i + $sensorId;
				
				// increase the index for starting at the next measurement block in each loop
				$i += 11;
				
				// ignore empty measurements
				if ( $dataArray[$value] != 'null' ) {
					// fill the data array
					$this->dataArray[$sensorString]['time'][$r] = $dataArray[$time];
					$this->dataArray[$sensorString]['value'][$r] = $dataArray[$value];
					$r++;
				}
			}
		}
    }

    // returns the the latest measurement
    public function getLastValue($sensorString) {
        // return '-' if station does not provide this sensor or any value at the current time
        if ( end($this->dataArray[$sensorString]['value']) == '' ) {
            return '-';
        }
        else {
            return end($this->dataArray[$sensorString]['value']);
        }
    }

    // returns the timeof latest measurement
    public function updatedOn($sensorString) {
        // return '-' if station does not provide this sensor or any value at the current time
        if ( end($this->dataArray[$sensorString]['time']) == '' ) {
            return '-';
        }
        else {
            return end($this->dataArray[$sensorString]['time']);
        }
    }
	
}
?>   