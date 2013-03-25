<?php
include "simple_html_dom.php";

/* 
Parsing Lanuv HTML Code and removing unnecessary space characters
Returns an array that contains all values stored between "<td class"mw_%"></td>"-tags
of the Lanuv HTML Code 
*/
function parseLanuv($code){
	$html = file_get_html('http://www.lanuv.nrw.de/luft/temes/heut/'.$code.'.htm');
	$html = str_replace('     ', '#', $html); //replacing empty value tags by #s as helper for further processing
	$html = str_replace(' ', '', $html);
	
	$regexp = '/<tdclass="[_a-z0-1]*">(.*?)<\/td>/'; //Regular Expression being used at following line
	$test = preg_match_all($regexp, $html, $array); //preg_match_all() returns an associative array containing all values that are stored within the regular expression
	$values = $array[0]; //Since both dimensions of the array are identical only the first dimension is further needed
		/*Removing Non-Values at the end of the array*/
		for ($i=0; $i<=10; $i++){
			array_pop($values);
			}

	return $values;
	}

/*
Returns grouped values of a concret sensor in an associative array
combined with the correspondenting time
*/
function get_value_group($values, $key){
	$array_size = sizeof($values);
	$i=0; //
	$r=0; //current index of the array

	$result['time'] = array();
	$result['value'] = array();
	
	while($i<$array_size) {
		$time = $i; //index of the time at each measuring block
		$value = $i+$key; //index of demanded sensortype
			
			/*ignoring XX:30h values*/
			if(strpos($values[$time],'/[0-9]^:30')){
			goto ignore_value;
			}
			/*ignoring empty measurements*/
			if(strpos($values[$value],'#')){
			goto ignore_value;
			}
		
		/*filling the associative array*/
		$result['time'][$r]= $values[$time];
		$result['value'][$r] = $values[$value];
		$r++;
		ignore_value:
		$i += 11;//increasing the index for starting at the next measurement block at each loop
		
	}
	//var_dump($result);
	return $result;
}

/*Getting the latest measurement*/
function getLastValue($array){
	$value = end ($array['value']);
	
	/*returning '-' if station does not provide this sensor or any at the current time*/
	if($value == ''){
		return '-';}
	
	return $value;
}

/*Getting the time when the last measurement was taken*/
function updatedOn($array){
	$value = end ($array['time']);
	
	/*returning '-' if station does not provide this sensor or any at the current time*/
	if($value == ''){
		return '-';}
		
	return $value;
}

$code = 'BIEL';//stationscode
/*
Sensor-Id Codes:
0: time
2: Ozon
3: N
4: NO2
5: LTEM
6: WRI
7: WGES
8: RFEU
9: SO2
10: pm10
*/
$sensor_id = 2;
$testoutput = get_value_group(parseLanuv($code), $sensor_id);
echo getLastValue($testoutput);

?>