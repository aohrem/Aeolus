<?php
    // abstract super class for table view, diagram view and download page
    abstract class DataVisualisation {
        protected $feedId;
        
        // supported time frames, default value, if no valid time frame is set and current time frame
        private $timeframes = array('6h', '24h', '48h', '1w', '1m', '3m');
        private $defaultTimeframe = '6h';
        protected $timeframe;
        
        // containers for the HTTP GET values and the outlier classification array
        protected $dataValidation;
        protected $sensitivity;
        protected $outliers;
        
        // arrays for meta data and sensor iteration
        private $metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
        protected $sensors = array('co', 'no2', 'humidity', 'temperature');
        
        // containers for template replacements (default value: empty)
        private $tplErrorMessage = '';
        private $tplHidden = '';
        
        // containers for the content template to work on and the data array
        protected $contentTemplate;
        protected $dataArray;
        
        // saves boolean value if database queries were successfull or not
        protected $dataSuccess = false;
    
    
        // constructor calls all necessary methods to build the standard template elements on the visualisation sites and to validate the data
        public function __construct($contentTemplate) {
            $this->contentTemplate = $contentTemplate;
            
            if ( $this->checkFeedId() ) {
                $this->replaceTimeframe();
                $this->getData();
                if ( $this->dataSuccess ) {
                    $this->replaceMetaData();
                }
                
                $this->determineSensitivity();
                
                $this->applyDataValidation();
                $this->replaceSensitivity();
                $this->replaceStatistics();
            }
        }
        
        private function checkFeedId() {
            // get the feed id and show error if not available
            if ( isset($_GET['fid']) && is_numeric($_GET['fid']) ) {
                $this->feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
                $this->contentTemplate->tplReplace('feedId', $this->feedId);
                return true;
            }
            else {
                $this->contentTemplate->readTpl('error_feedid');
                return false;
            }   
        }
        
        private function replaceTimeframe() {
            // get the timeframe to show and check if it is valid
            if ( isset($_GET['timeframe']) && in_array($_GET['timeframe'], $this->timeframes) ) {
                $this->timeframe = htmlentities(mysql_real_escape_string($_GET['timeframe']));
            }
            // use the default timeframe if no timeframe was given or the given timeframe is not valid
            else {
                $this->timeframe = $this->defaultTimeframe;
            }
            
            // replace timeframe
            $this->contentTemplate->tplReplace('time', $this->timeframe);
            
            $cssActive = ' class="active"';
            
            // mark active timeframe tabs
            foreach ( $this->timeframes as $val ) {
                if ( $val == $this->timeframe ) {
                    $this->contentTemplate->tplReplace($val.'_active', $cssActive);
                }
                else {
                    $this->contentTemplate->tplReplace($val.'_active', '');
                }
            }
        }
        
        private function getData() {
            // connect to the MySQL database and request all necessary data
            $mysqlConnection = new MySQLConnection();
            $aqDatabase = new AirQualityDatabase($this->feedId, $this->timeframe);
            $this->dataArray = $aqDatabase->getValues();
            
            // handle errors if parsing the xml was not successfull
            if ( ! is_array($this->dataArray) ) {
                $errorCode = $this->dataArray;
                $this->contentTemplate->cleanCode('tableRow');
                $this->contentTemplate->tplReplace('title', '');
                
                // hide all unnecassary layout elements if there is no data
                $this->tplHidden = ' class="hidden"';
                $this->tplErrorMessage = '<div class="errormessage details">'.translate($errorCode).'</div>';
                
                $this->dataSuccess = false;
            }
            // parsing the xml was successfull
            else {
                $this->dataSuccess = true;
            }
        }
        
        private function replaceMetaData() {
            // replace meta data in template and delete meta data in the data array
            foreach ( $this->metadata as $mdata ) {
                $this->contentTemplate->tplReplace($mdata, $this->dataArray[$mdata]);
                unset($this->dataArray[$mdata]);
            }
        }
        
        private function applyDataValidation() {
            // apply the data validation to the data array using all sensors and the determined sensitivity and time frame
            include('datavalidation.class.php');
            $this->dataValidation = new DataValidation($this->dataArray, $this->sensors, $this->sensitivity, $this->timeframe);
            if ( $this->dataSuccess ) {
                $this->outliers = $this->dataValidation->getOutliers();
            }
        }
        
        private function determineSensitivity() {
            if ( isset($_GET['sensitivity']) && is_numeric($_GET['sensitivity']) ) {
                $this->sensitivity = $_GET['sensitivity'];
                
                // if sensitivity is outside of the supported value range, set it to the default value
                if ( $this->sensitivity < 0 || $this->sensitivity > 3 ) {
                    $this->sensitivity = 'default';
                }
            }
            // if no sensitivity is set, set it to the default value
            else {
                $this->sensitivity = 'default';
            }
        }
        
        private function replaceSensitivity() {
            // get the default sensitivity value from the data validation class if sensitivity is 'default'
            if ( $this->sensitivity == 'default' ) {
                $this->sensitivity = $this->dataValidation->getDefaultSensitivity();
            }
            
            // mark active outlier detection sensitivity as selected
            for ( $i = 0; $i <= 3; $i++ ) {
                $sel = '';
                
                if ( $this->sensitivity == $i ) {
                    $sel = ' selected="selected"';
                }
                
                $this->contentTemplate->tplReplace('sensitivity_'.$i.'_selected', $sel);
            }
            $this->contentTemplate->tplReplace('sensitivity', $this->sensitivity);
        }
        
        // this method computes statistics consisting of current value, mean, maximum and minimum in a certain time frame and puts them into the content template
        private function replaceStatistics() {
            if ( sizeof($this->dataArray) > 0 && is_array($this->dataArray) ) {
                // initialise associative statistics array
                $statistics = array('current' => $this->sensors, 'mean' => $this->sensors, 'maximum' => $this->sensors, 'minimum' => $this->sensors);
                
                // initialise default values for maximum, minimum, mean and the size of the data array
                foreach ( $this->sensors as $sensor ) {
                    $statistics['maximum'][$sensor] = 0;
                    $statistics['minimum'][$sensor] = null;
                    $statistics['mean'][$sensor] = 0;
                
                    $size[$sensor] = 0;
                }
                
                // iterate the data array ...
                foreach ( $this->dataArray as $time => $sensors) {
                    // ... and the sensors
                    foreach ( $sensors as $sensor => $value ) {
                        // if there is a value, increment the data array size for this sensor
                        if ( floatval($this->dataArray[$time][$sensor]) != 0.0 ) {
                            $size[$sensor]++;
                        }
                        
                        // if value is bigger than current maximum, value is used as the new maximum
                        if ( $value > $statistics['maximum'][$sensor] ) {
                            $statistics['maximum'][$sensor] = $value;
                        }
                        
                        // if minimum is initialised as null or the value is less than current minimum, value is used as the new minimum
                        if ( $statistics['minimum'][$sensor] == null || ( floatval($value) < $statistics['minimum'][$sensor] && floatval($value) != 0.00 ) ) {
                            $statistics['minimum'][$sensor] = $value;
                        }
                        
                        // cumulate mean value
                        $statistics['mean'][$sensor] += $value;
                    }
                }
                
                // connect to the database to get the current value for each sensor
                $mysqlConnection = new MySQLConnection();
                $aqDatabase = new AirQualityDatabase($this->feedId, $this->timeframe);
                $data = $aqDatabase->getCurrentValues();
                foreach ( $this->sensors as $sensor ) {
                    // calculate mean by dividing the cumulated value by the determined data size
                    if ( $size[$sensor] != 0 ) $statistics['mean'][$sensor] /= $size[$sensor];
                    $statistics['mean'][$sensor] = round($statistics['mean'][$sensor], 2);
                    $statistics['current'][$sensor] = $data['current_value'][$sensor];
                    
                    // replace values in the content template
                    $this->contentTemplate->tplReplace($sensor.'_current', $statistics['current'][$sensor]);
                    $this->contentTemplate->tplReplace($sensor.'_mean', $statistics['mean'][$sensor]);
                    $this->contentTemplate->tplReplace($sensor.'_minimum', $statistics['minimum'][$sensor]);
                    $this->contentTemplate->tplReplace($sensor.'_maximum', $statistics['maximum'][$sensor]);
                }
            }
            
            // if the was no data found, replace values by -
            foreach ( $this->sensors as $sensor ) {
                $this->contentTemplate->tplReplace($sensor.'_current', '-');
                $this->contentTemplate->tplReplace($sensor.'_mean', '-');
                $this->contentTemplate->tplReplace($sensor.'_minimum', '-');
                $this->contentTemplate->tplReplace($sensor.'_maximum', '-');
            }
        }
        
        private function __destruct() {
            // replace error message in template and hide description and outlier detection boxes if not needed
            $this->contentTemplate->tplReplace('errormessage', $this->tplErrorMessage);
            $this->contentTemplate->tplReplace('hidden', $this->tplHidden);
        }
    }
?>