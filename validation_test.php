<?php
	$dataArray = array(
		'1361774340' => array
        (
            'co' => 305115,
            'humidity' => 32,
            'no2' => 610024,
            'temperature' => 23
        ),

    '1361774400' => array
        (
            'co' => 304653,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361774460' => array
        (
            'co' => 304532,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361774520' => array
        (
            'co' => 305356,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361774580' => array
        (
            'co' => 304653,
            'humidity' => 32,
            'no2' => 621324,
            'temperature' => 23
        ),

    '1361774640' => array
        (
            'co' => 304774,
            'humidity' => 32,
            'no2' => 616224,
            'temperature' => 23
        ),

    '1361774700' => array
        (
            'co' => 305236,
            'humidity' => 32,
            'no2' => 604755,
            'temperature' => 23
        ),

    '1361774760' => array
        (
            'co' => 304411,
            'humidity' => 32,
            'no2' => 616224,
            'temperature' => 23
        ),

    '1361774820' => array
        (
            'co' => 304532,
            'humidity' => 32,
            'no2' => 616224,
            'temperature' => 23
        ),

    '1361774880' => array
        (
            'co' => 305236,
            'humidity' => 32,
            'no2' => 624455,
            'temperature' => 23
        ),

    '1361774940' => array
        (
            'co' => 304270,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361775000' => array
        (
            'co' => 304411,
            'humidity' => 32,
            'no2' => 616224,
            'temperature' => 23
        ),

    '1361775060' => array
        (
            'co' => 306324,
            'humidity' => 32,
            'no2' => 621324,
            'temperature' => 23
        ),

    '1361775120' => array
        (
            'co' => 304774,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361775180' => array
        (
            'co' => 304653,
            'humidity' => 32,
            'no2' => 616224,
            'temperature' => 23
        ),

    '1361775240' => array
        (
            'co' => 305236,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361775300' => array
        (
            'co' => 305115,
            'humidity' => 32,
            'no2' => 610024,
            'temperature' => 23
        ),

    '1361775360' => array
        (
            'co' => 305115,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361775420' => array
        (
            'co' => 304653,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361775480' => array
        (
            'co' => 305115,
            'humidity' => 32,
            'no2' => 613124,
            'temperature' => 23
        ),

    '1361775540' => array
        (
            'co' => 305477,
            'humidity' => 32,
            'no2' => 616224,
            'temperature' => 23
        )
	);
	
	function median($dataArray, $sensor, $start, $end) {
		$sensorArray = array();
		
		$i = 0;
		$j = 0;
		
		foreach ( $dataArray as $val ) {
			if ( $j >= $start - 1 && $j < $end ) {
				$sensorArray[$i] = $val[$sensor];
				$i++;
			}
			$j++;
		}
		
		sort($sensorArray);
		
		$median = $sensorArray[floor(sizeof($sensorArray) / 2)];
		
		return $median;
	}
	
	function runmed($dataArray, $windowSize, $sensors) {
		$runmedArray = array();
		
		$i = 0;
		foreach ( $sensors as $sensor ) {
			foreach ( $dataArray as $key => $val ) {
				$start = $i - floor($windowSize / 2);
				$end = $i + floor($windowSize / 2);
				$runmedArray[$key][$sensor] = median($dataArray, $sensor, $start, $end);
				$i++;
			}
			$i = 0;
		}
		
		return $runmedArray;
	}
	
	function rep($value, $size) {
		$array = array();
		for ( $i = 0; $i < $size; $i++ ) {
			$array[$i] = $value;
		}
		return $array;
	}
	
	function getArrayWindow($array, $start, $end, $sensor) {
		$arrayWindow = array();
		
		$i = 0;
		$j = 0;
		
		foreach ( $array as $key => $val ) {
			if ( $j >= $start - 1 && $j < $end ) {
				$arrayWindow[$i] = $val[$sensor];
				$i++;
			}
			$j++;
		}
		
		return $arrayWindow;
	}
    
    function quantile($array, $p) {
        $n = sizeof($array);
        sort($array);
        
        $np = $n * $p;
        $m = 1 - $p;
        $j = floor($np + $m);
        $g = $np + $m - $j;
        
        return (1 - $g) * $array[$j - 1] + $g * $array[$j];
    }
	
	function lableOutliers($dataArray, $windowSize, $sensors) {
		$ytmed = runmed($dataArray, $windowSize, $sensors);
		$k = floor($windowSize / 2);
		$n = sizeof($dataArray);
        
        $i = 0;
        foreach ( $dataArray as $key => $val ) {
            $transArray[$i] = $key;
            $i++;
        }
        
        foreach ( $sensors as $sensor ) {
            $index = $k;
            $outlier[$sensor] = rep(false, $n);
            
		    while ( $index < ( $n - $k ) ) {
			    $window = getArrayWindow($dataArray, $index - $k, $index + $k, $sensor);
			    $interQuartileRange = abs(quantile($window, 0.75) - quantile($window, 0.25));
			    $median = $ytmed[$transArray[$index]][$sensor];
                
			    if ( $dataArray[$transArray[$index - 1]][$sensor] < ($median - 1.5 * $interQuartileRange) || $dataArray[$transArray[$index - 1]][$sensor] > ($median + 1.5 * $interQuartileRange) ) {
				    $outlier[$sensor][$index - 1] = true;
			    }
                
			    $index++;
		    }
        }
        
        return $outlier;
	}
	
	$windowSize = 5;
	$sensors = array('co', 'humidity', 'no2', 'temperature');
	print_r(lableOutliers($dataArray, $windowSize, $sensors));
?>