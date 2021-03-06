<?php
    class DataValidation {
        // default values for sensitivity of the outlier detection, window size and factor
        private $defaultSensitivity = 2;
        private $windowSizes = array('6h' => 12, '24h' => 8, '48h' => 8, '1w' => 24, '1m' => 7, '3m' => 7);
        private $factor = array(1.0, 5.0, 1.5, 1.0);
        private $validation = true;
        
        // containers for the data and trans array, used sensitivity, window size and for the sensor types
        private $dataArray;
        private $transArray;
        private $sensitivity;
        private $windowSize;
        private $sensors;
        
        public function __construct($dataArray, $sensors, $sensitivity, $time) {
            // data array which shall be validated
            $this->dataArray = $dataArray;
            
            // sensor types used to iterate the data array
            $this->sensors = $sensors;
            
            // window size depends on the given time frame, because every time frame uses different data frequencies
            $this->windowSize = $this->windowSizes[$time];
            
            // initialize sensitivity if there was just the default value given
            if ( $sensitivity == 'default' ) {
                $this->sensitivity = $this->defaultSensitivity;
            }
            else {
                $this->sensitivity = $sensitivity;
            }
            
            // cancel data validation if the amount of data values is too small
            if ( sizeof($this->dataArray) < $this->windowSize ) {
                $this->validation = false;
            }
        }
        
        // returns the default sensitivity
        public function getDefaultSensitivity() {
            return $this->defaultSensitivity;
        }
    
        // function calculates outliers in the data array and returns an array with the estimated outliers
        // based on "Outliers and robust methods" slides of Katharina Henneb�hl, ifgi
        public function getOutliers() {
            if ( $this->validation ) {
                // apply the running medians algorithm and save returned array
                $ytmed = $this->runmed($this->dataArray, $this->windowSize, $this->sensors);
            
                // k is half of the window size
                $k = floor($this->windowSize / 2);
            
                // n is the size of the data array
                $n = sizeof($this->dataArray);
            
                // calculate the trans array
                $i = 0;
                foreach ( $this->dataArray as $key => $val ) {
                    $this->transArray[$i] = $key;
                    $i++;
                }
            
                foreach ( $this->sensors as $sensor ) {
                    // calculate trans array by iterating the data array and assign an index to each value
                    $i = 0;
                    foreach ( $this->dataArray as $key => $val ) {
                        $this->transArray[$i] = $key;
                    
                        // if there is no value, it is classified as an outlier
                        if ( ! isset($val[$sensor]) ) {
                            $outliers[$sensor][$key] = true;
                        }
                        // fill in outlier array with default (false) values
                        else {
                            $outliers[$sensor][$key] = false;
                        }
                    
                        $i++;
                    }
                
                    // get the array window with length of the given window size at the start of the array
                    $frontWindow = $this->getArrayWindow($this->dataArray, 0, $this->windowSize, $sensor);
                    // calculate the IQR ...
                    $frontInterQuartileRange = abs($this->quantile($frontWindow, 0.75) - $this->quantile($frontWindow, 0.25));
                    // ... and the median of that window
                    $frontMedian = $this->median($this->dataArray, $sensor, 0, $this->windowSize);
                
                    // check first $windowSize values for outliers
                    for ( $j = 0; $j < $this->windowSize; $j++ ) {
                        $outliers = $this->checkValue($j, $frontMedian, $frontInterQuartileRange, $outliers, $sensor);
                    }
                
                    $index = $k;
                
                    while ( $index < ( $n - $k ) ) {
                        // get the array window with length of the given window size
                        $window = $this->getArrayWindow($this->dataArray, $index - $k, $index + $k, $sensor);
                    
                        // calculate inter quartile range of current window (absolute value of the difference between 75% and 25% quantile)
                        $interQuartileRange = abs($this->quantile($window, 0.75) - $this->quantile($window, 0.25));
                    
                        $median = $ytmed[$this->transArray[$index - 1]][$sensor];
                    
                        // if current value is less than (median - factor * inter quartile range) or greater than (median + factor * inter quartile range), it is classified as an outlier
                        $outliers = $this->checkValue($index - 1, $median, $interQuartileRange, $outliers, $sensor);
                    
                        $index++;
                    }
                
                    // get the array window with length of the given window size at the start of the array
                    $backStart = sizeof($this->dataArray) - $this->windowSize;
                    $backWindow = $this->getArrayWindow($this->dataArray, $backStart, sizeof($this->dataArray), $sensor);
                    // calculate the IQR ...
                    $backInterQuartileRange = abs($this->quantile($backWindow, 0.75) - $this->quantile($backWindow, 0.25));
                    // ... and the median of that window
                    $backMedian = $this->median($this->dataArray, $sensor, $backStart, sizeof($this->dataArray));
                
                    // check last $windowSize values for outliers
                    for ( $j = $backStart; $j < sizeof($this->dataArray); $j++ ) {
                        $outliers = $this->checkValue($j, $backMedian, $backInterQuartileRange, $outliers, $sensor);
                    }
                }
            
                return $outliers;
            }
            return false;
        }
        
        // checks if outliers in the data array were found
        public function containsOutliers($outliers) {
            if ( $this->validation ) {
                // iterate outlier array and return true if there is at least one outlier
                foreach ( $outliers as $sensor => $val ) {
                    foreach ( $outliers[$sensor] as $time => $outlier ) {
                        if ( $outlier ) {
                            return true;
                        }
                    }
                }
            }
            
            // if there was no outlier in the array, return false
            return false;
        }
        
        // returns data array with linear interpolated outliers
        public function interpolateOutliers($outliers) {
            if ( $this->validation ) {
                // iterate sensors
                foreach ( $outliers as $sensor => $val ) {
            
                    // i is the index of the current outlier dataset, used by the trans array
                    $i = 0;
                    foreach ( $outliers[$sensor] as $time => $outlier ) {
                        if ( $outlier ) {
                            // get next predecessor which is not an outlier
                            $pred = 1;
                            while ( isset($this->transArray[$i - $pred]) && (( floatval($this->dataArray[$this->transArray[$i - $pred]][$sensor]) == 0.0 ) || ( isset($outliers[$sensor][$this->transArray[$i - $pred]]) && $outliers[$sensor][$this->transArray[$i - $pred]] ) ) ) {
                                $pred++;
                            }
                        
                            // get next successor which is not an outlier
                            $succ = 1;
                            while ( isset($this->transArray[$i + $succ]) && (( floatval($this->dataArray[$this->transArray[$i + $succ]][$sensor]) == 0.0 ) || ( isset($outliers[$sensor][$this->transArray[$i + $succ]]) && $outliers[$sensor][$this->transArray[$i + $succ]] ) ) ) {
                                $succ++;
                            }
                        
                            // if a predecessor and a successor were found: linear interpolation (mean)
                            if ( isset($this->transArray[$i - $pred]) && isset($this->transArray[$i + $succ]) ) {
                                $interpolatedValue = round(($this->dataArray[$this->transArray[$i - $pred]][$sensor] + $this->dataArray[$this->transArray[$i + $succ]][$sensor] ) / 2, 3);
                            }
                            // if just a successor was found: take successor's value as interpolated value
                            else if ( ! isset($this->transArray[$i - $pred]) && isset($this->transArray[$i + $succ]) ) {
                                $interpolatedValue = $this->dataArray[$this->transArray[$i + $succ]][$sensor];
                            }
                            // if just a predecessor was found: take predecessors's value as interpolated value
                            else if ( isset($this->transArray[$i - $pred]) && ! isset($this->transArray[$i + $succ]) ) {
                                $interpolatedValue = $this->dataArray[$this->transArray[$i - $pred]][$sensor];
                            }
                        
                            // save interpolated value in the data array
                            $this->dataArray[$time][$sensor] = $interpolatedValue;
                        }
                    
                        $i++;
                    }
                }
            }
            
            return $this->dataArray;
        }
        
        // if value at $index is less than (median - factor * inter quartile range) or greater than (median + factor * inter quartile range), it is classified as an outlier
        private function checkValue($index, $median, $iqr, $outliers, $sensor) {
            if ( $iqr == 0 ) { $iqr++; }
            if ( floatval($this->dataArray[$this->transArray[$index]][$sensor]) != 0.0 ) {
                if ( $this->dataArray[$this->transArray[$index]][$sensor] < ($median - $this->factor[$this->sensitivity] * $iqr) ||
                        $this->dataArray[$this->transArray[$index]][$sensor] > ($median + $this->factor[$this->sensitivity] * $iqr) ) {
                    $outliers[$sensor][$this->transArray[$index]] = true;
                }
            }
            else {
                $outliers[$sensor][$this->transArray[$index]] = false;
            }
            return $outliers;
        }
        
        // calculates the median of $dataArray on column $sensor from index $start to index $end
        private function median($dataArray, $sensor, $start, $end) {
            $sensorArray = array();
        
            $i = 0;
            $j = 0;
        
            // iterate values of the data array
            foreach ( $dataArray as $val ) {
                // check if value is within the given interval
                if ( $j >= $start - 1 && $j < $end ) {
                    // if there is a value for the given sensor, save the value in another array
                    if ( floatval($val[$sensor]) != 0.0 ) {
                        $sensorArray[$i] = $val[$sensor];
                        $i++;
                    }
                    else {
                        $end++;
                    }
                }
                $j++;
            }
        
            // sort the sensor array
            sort($sensorArray);
        
            // median is mid value in this array
            $median = 0;
            if ( isset($sensorArray[floor(sizeof($sensorArray) / 2)]) ) {
                $median = $sensorArray[floor(sizeof($sensorArray) / 2)];
            }
        
            return $median;
        }
    
        // running medians function, smoothes the data array
        private function runmed($dataArray, $windowSize, $sensors) {
            $runmedArray = array();
        
            // i is the index of the dataset, used by the trans array
            foreach ( $sensors as $sensor ) {
                $i = 0;
                
                // apply the running median algorithm as described in R library documentation for each value of the data array
                // running median in R library documentation: http://rss.acs.unt.edu/Rdoc/library/stats/html/runmed.html
                foreach ( $dataArray as $key => $val ) {
                    $start = $i - floor($windowSize / 2);
                    $end = $i + floor($windowSize / 2);
                    $runmedArray[$key][$sensor] = $this->median($dataArray, $sensor, $start, $end);
                    $i++;
                }
            }
            
            // return smoothed data array
            return $runmedArray;
        }
    
        // function replicates an array of $size with $value for each element
        private function rep($value, $size) {
            $array = array();
            for ( $i = 0; $i < $size; $i++ ) {
                $array[$i] = $value;
            }
            return $array;
        }
    
        // function gives back the subarray from $start to $end, but just for $sensor
        private function getArrayWindow($array, $start, $end, $sensor) {
            $arrayWindow = array();
        
            // i is the index of the new array where the next value of the subarray will be saved
            // j is the index of the current value of the array
            $i = 0;    $j = 0;
            foreach ( $array as $key => $val ) {
                // check if value is within the given interval
                if ( $j >= $start - 1 && $j < $end ) {
                    // if there is a value, save it to our new subarray
                    if ( floatval($val[$sensor]) != 0.0 ) {
                        $arrayWindow[$i] = $val[$sensor];
                        $i++;
                    }
                    else {
                        $end++;
                    }
                }
                $j++;
            }
            
            return $arrayWindow;
        }
    
        // returns the $p-quantile of $array
        // applied algorithm (like in R library: type 7): http://stat.ethz.ch/R-manual/R-patched/library/stats/html/quantile.html
        private function quantile($array, $p) {
            $n = sizeof($array);
            sort($array);
        
            $np = $n * $p;
            $m = 1 - $p;
            $j = floor($np + $m);
            $g = $np + $m - $j;
            
            if ( isset($array[$j - 1]) && isset($array[$j]) ) {
                return (1 - $g) * $array[$j - 1] + $g * $array[$j];
            }
            else {
                return false;
            }
        }
    }
?>