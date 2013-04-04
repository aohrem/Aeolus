<?php
	class DataVisualisation {
        protected $feedId;
        
        private $timeframes = array('6h', '24h', '48h', '1w', '1m', '3m');
        private $defaultTimeframe = '6h';
        protected $timeframe;
        
        protected $dataValidation;
        protected $sensitivity;
        protected $outliers;
        
        private $metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
        protected $sensors = array('co', 'no2', 'humidity', 'temperature');
        
        // constants used to determine parameters for the cosm-API request
        protected $seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
        protected $interval = array('6h' => 60, '24h' => 300, '48h' => 600, '1w' => 10800, '1m' => 604800, '3m' => 2678400);
        protected $cosmTimeFormat = 'Y-m-d\TH:i:s\Z';
        protected $limit = 1000;
        
        private $tplErrorMessage = '';
        private $tplHidden = '';
        
        protected $contentTemplate;
        protected $dataArray;
        
        protected $cosmSuccess = false;
        
        public function __construct($contentTemplate) {
            $this->contentTemplate = $contentTemplate;
            
            if ( $this->checkFeedId() ) {
                $this->replaceTimeframe();
                $this->getCosmData();
                if ( $this->cosmSuccess ) {
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
        
        private function getCosmData() {
            // set parameters for the API request
            $start = date($this->cosmTimeFormat, time() - $this->seconds[$this->timeframe]);
            $end = date($this->cosmTimeFormat, time());
            $values = 'all_values';
            $interval = $this->interval[$this->timeframe];
            
            // cosm-API integration
            $cosmAPI = new CosmAPI();
            
            // fill in the parameters to read the cosm-API
            $this->dataArray = $cosmAPI->parseFeed($this->feedId, $values, $start, $end, $this->limit, $interval, '');
            
            // handle errors if parsing the xml was not successfull
            if ( ! is_array($this->dataArray) ) {
                $errorCode = $this->dataArray;
                $this->contentTemplate->cleanCode('tableRow');
                $this->contentTemplate->tplReplace('title', '');
                
                $this->tplHidden = ' class="hidden"';
                $this->tplErrorMessage = '<div class="errormessage details">'.translate($errorCode).'</div>';
                
                $this->cosmSuccess = false;
            }
            // parsing the xml was successfull
            else {
                // sort sensor data by timestamp (keys of the data array)
                ksort($this->dataArray, SORT_NUMERIC);
                $this->cosmSuccess = true;
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
            include('datavalidation.class.php');
            $this->dataValidation = new DataValidation($this->dataArray, $this->sensors, $this->sensitivity, $this->timeframe);
            if ( $this->cosmSuccess ) {
                $this->outliers = $this->dataValidation->getOutliers();
            }
        }
        
        private function determineSensitivity() {
            if ( isset($_GET['sensitivity']) && is_numeric($_GET['sensitivity']) ) {
                $this->sensitivity = $_GET['sensitivity'];
                
                if ( $this->sensitivity < 0 || $this->sensitivity > 3 ) {
                    $this->sensitivity = 'default';
                }
            }
            else {
                $this->sensitivity = 'default';
            }
        }
        
        private function replaceSensitivity() {
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
            
            if ( $this->sensitivity == 0 ) {
                $this->contentTemplate->tplReplace('outlierState', translate('outlier_state_off'));
            }
        }
		
		private function replaceStatistics() {
			$statistics = array('current' => $this->sensors, 'mean' => $this->sensors, 'maximum' => $this->sensors, 'minimum' => $this->sensors);
			foreach ( $this->sensors as $sensor ) {
				$statistics['maximum'][$sensor] = 0;
				$statistics['minimum'][$sensor] = null;
				$statistics['mean'][$sensor] = 0;
				
				$size[$sensor] = 0;
			}
			
			foreach ( $this->dataArray as $time => $sensors) {
				foreach ( $sensors as $sensor => $value ) {
					if ( isset($this->dataArray[$time][$sensor]) ) {
						$size[$sensor]++;
					}
					
					if ( $value > $statistics['maximum'][$sensor] ) {
						$statistics['maximum'][$sensor] = $value;
					}
					if ( $statistics['minimum'][$sensor] == null || $value < $statistics['minimum'][$sensor]  ) {
						$statistics['minimum'][$sensor] = $value;
					}
					$statistics['mean'][$sensor] += $value;
				}
			}
			
			$data = $this->dataArray;
			foreach ( $this->sensors as $sensor ) {
				$statistics['mean'][$sensor] /= $size[$sensor];
				$statistics['mean'][$sensor] = round($statistics['mean'][$sensor], 3);
				
				while (! isset($statistics['current'][$sensor]) ) {
					$current = array_pop($data);
					if ( isset($current[$sensor]) ) {
						$statistics['current'][$sensor] = $current[$sensor];
					}
				}
				
				$this->contentTemplate->tplReplace($sensor.'_current', $statistics['current'][$sensor]);
				$this->contentTemplate->tplReplace($sensor.'_mean', $statistics['mean'][$sensor]);
				$this->contentTemplate->tplReplace($sensor.'_minimum', $statistics['minimum'][$sensor]);
				$this->contentTemplate->tplReplace($sensor.'_maximum', $statistics['maximum'][$sensor]);
			}
		}
        
        private function __destruct() {
            // replace error message in template and hide description and outlier detection boxes if not needed
            $this->contentTemplate->tplReplace('errormessage', $this->tplErrorMessage);
            $this->contentTemplate->tplReplace('hidden', $this->tplHidden);
        }
    }
?>