<?php
    class DataValidation {
        private $dataArray;
		private $transArray;
	    private $windowSize = 31;
        private $factor = 1.5;
	    private $sensors;
        
        public function __construct($dataArray, $sensors) {
            $this->dataArray = $dataArray;
            $this->sensors = $sensors;
        }
	
	    public function getOutliers() {
		    $ytmed = $this->runmed($this->dataArray, $this->windowSize, $this->sensors);
		    $k = floor($this->windowSize / 2);
		    $n = sizeof($this->dataArray);
            
            $i = 0;
            foreach ( $this->dataArray as $key => $val ) {
                $this->transArray[$i] = $key;
                $i++;
            }
            
            foreach ( $this->sensors as $sensor ) {
                $i = 0;
                foreach ( $this->dataArray as $key => $val ) {
                    $this->transArray[$i] = $key;
                    $outliers[$sensor][$this->transArray[$i]] = false;
                    $i++;
                }
                
                $index = $k;
                
		        while ( $index < ( $n - $k ) ) {
			        $window = $this->getArrayWindow($this->dataArray, $index - $k, $index + $k, $sensor);
			        $interQuartileRange = abs($this->quantile($window, 0.75) - $this->quantile($window, 0.25));
			        $median = $ytmed[$this->transArray[$index]][$sensor];
                    
                    if ( isset($this->dataArray[$this->transArray[$index - 1]][$sensor]) ) {
			            if ( $this->dataArray[$this->transArray[$index - 1]][$sensor] < ($median - $this->factor * $interQuartileRange) ||
                                $this->dataArray[$this->transArray[$index - 1]][$sensor] > ($median + $this->factor * $interQuartileRange) ) {
				            $outliers[$sensor][$this->transArray[$index - 1]] = true;
			            }
                    }
                    else {
                        $outliers[$sensor][$this->transArray[$index - 1]] = true;
                    }
                    
			        $index++;
		        }
            }
            
            return $outliers;
	    }
		
		public function outliersFound($outliers) {
			foreach ( $outliers as $sensor => $val ) {
				foreach ( $outliers[$sensor] as $time => $outlier ) {
					if ( $outlier ) {
						return true;
					}
				}
			}
			return false;
		}
		
		public function interpolateOutliers($outliers) {
			foreach ( $outliers as $sensor => $val ) {
				$i = 0;
				foreach ( $outliers[$sensor] as $time => $outlier ) {
					if ( $outlier ) {
						$pred = 1;
						while ( $outliers[$sensor][$this->transArray[$i - $pred]] ) {
							$pred++;
						}
						
						$succ = 1;
						while ( $outliers[$sensor][$this->transArray[$i + $succ]] ) {
							$succ++;
						}
						
						$interpolatedValue = round(( $this->dataArray[$this->transArray[$i - $pred]][$sensor] + $this->dataArray[$this->transArray[$i + $succ]][$sensor] ) / 2);
						
						$this->dataArray[$time][$sensor] = $interpolatedValue;
					}
					$i++;
				}
			}
			return $this->dataArray;
		}
        
	    private function median($dataArray, $sensor, $start, $end) {
		    $sensorArray = array();
		
		    $i = 0;
		    $j = 0;
		
		    foreach ( $dataArray as $val ) {
			    if ( $j >= $start - 1 && $j < $end ) {
                    if ( isset($val[$sensor]) ) {
				        $sensorArray[$i] = $val[$sensor];
                    }
                    else {
                        $sensorArray[$i] = 0;
                    }
				    $i++;
			    }
			    $j++;
		    }
		
		    sort($sensorArray);
		
		    $median = $sensorArray[floor(sizeof($sensorArray) / 2)];
		
		    return $median;
	    }
	
	    private function runmed($dataArray, $windowSize, $sensors) {
		    $runmedArray = array();
		
		    $i = 0;
		    foreach ( $sensors as $sensor ) {
			    foreach ( $dataArray as $key => $val ) {
				    $start = $i - floor($windowSize / 2);
				    $end = $i + floor($windowSize / 2);
				    $runmedArray[$key][$sensor] = $this->median($dataArray, $sensor, $start, $end);
				    $i++;
			    }
			    $i = 0;
		    }
		
		    return $runmedArray;
	    }
	
	    private function rep($value, $size) {
		    $array = array();
		    for ( $i = 0; $i < $size; $i++ ) {
			    $array[$i] = $value;
		    }
		    return $array;
	    }
	
	    private function getArrayWindow($array, $start, $end, $sensor) {
		    $arrayWindow = array();
		
		    $i = 0;
		    $j = 0;
		
		    foreach ( $array as $key => $val ) {
			    if ( $j >= $start - 1 && $j < $end ) {
                    if ( isset($val[$sensor]) ) {
				        $arrayWindow[$i] = $val[$sensor];
                    }
                    else {
                        $arrayWindow[$i] = 0;
                    }
				    $i++;
			    }
			    $j++;
		    }
		
		    return $arrayWindow;
	    }
    
        private function quantile($array, $p) {
            $n = sizeof($array);
            sort($array);
        
            $np = $n * $p;
            $m = 1 - $p;
            $j = floor($np + $m);
            $g = $np + $m - $j;
        
            return (1 - $g) * $array[$j - 1] + $g * $array[$j];
        }
    }
?>