<?php
    include('lang/en.lang.php');
    include('functions.inc.php');
	include('class/template.class.php');
	include('class/simplexmlextended.class.php');
	include('class/cosmapi.class.php');
	
	class SOS {
        private $xmlTemplate;
        
        private $parameters = array('request' => array('getObservation', 'getCapabilities', 'describeSensor'),
                                    'service' => array('sos'));
        private $getObservationParameters = array('responseFormat' => array('text/xml;subtype="om/1.0.0"'),
                                                  'offering' => array('airqualityegg'),
                                                  'version' => array('2.0.0'));
        private $describeSensorParameters = array('outputFormat' => array('text/xml;subtype="sensorML/1.0.1"'),
                                                  'version' => array('2.0.0'));
        private $parameter;
        
        private $supportedObservedProperties = array('co', 'no2', 'humidity', 'temperature');
        private $observedPropertyUrn = 'urn:ogc:def:phenomenon:';
        private $observedProperty;
        
		private $feedId;
		private $startTime;
        private $endTime;
        private $outlierInterpolation;
        
		private $metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
		private $units = array('co' => 'ppm', 'no2' => 'ppm', 'humidity' => '%', 'temperature' => 'degreesCelsius');
		private $intervals = array(60 => 21600, 120 => 43200, 240 => 86400, 300 => 432000, 900 => 1209600, 3600 => 2678400, 10800 => 7776000, 21600 => 15552000, 86400 => 31536000);
		private $timeframes = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
		private $limit = 1000;
		
		private $cosmTimeFormat = 'Y-m-d\TH:i:s\Z';
		private $sosTimeFormat = 'Y-m-d\TH:i:s.000+00:00';
		
		private $dataArray;
	
		public function __construct() {
            header('Content-type: application/xml');
            
            $this->xmlTemplate = new Template();
			$this->xmlTemplate->setFileExtension('.xml');
			$this->xmlTemplate->setFolder('xml/');
            
            $this->getParameters($this->parameters);
            switch ( $this->parameter['request'] ) {
                case 'getObservation':
                    $this->getGetObservationParameters();
                    $this->getCosmData();
                    if ( $this->outlierInterpolation > 0 ) {
                        $this->applyDataValidation();
                    }
                    $this->printObservationXml();
                break;
                case 'getCapabilities':
                    $this->printCapabilitiesXml();
                break;
                case 'describeSensor':
                    $this->getDescribeSensorParameters();
                    $this->getCosmData();
                    $this->printDescribeSensorXml();
                break;
            }
		}
		
		private function getParameters($parameters) {
            foreach ( $parameters as $parameter => $supportedValues ) {
                if ( isset($_GET[$parameter]) ) {
                    $this->parameter[$parameter] = $_GET[$parameter];
                }
                else {
                    die('<error>Error - No '.$parameter.' specified. Please set the '.$parameter.' parameter.</error>');
                }
                
                if ( ! in_array($this->parameter[$parameter], $supportedValues) ) {
                    die('<error>Error - The value of parameter '.$parameter.' is not supported. Supported values: '.implode(', ', $supportedValues).'</error>');
                }
            }
        }
        
        private function getGetObservationParameters() {    
            $this->getParameters($this->getObservationParameters);
            
            // check if observedProperty is given and if its value is valid
            $supportedObservedPropertyValues = '';
            foreach ( $this->supportedObservedProperties as $supportedValue ) $supportedObservedPropertyValues .= $this->observedPropertyUrn.$supportedValue.' ';
            if ( isset($_GET['observedProperty']) ) {
                $this->observedProperty = explode(',', $_GET['observedProperty']);
                $i = 0;
                foreach ( $this->observedProperty as $property ) {
                    $this->observedProperty[$i] = str_replace($this->observedPropertyUrn, '', $property);
                    if ( ! in_array($this->observedProperty[$i], $this->supportedObservedProperties) ) {
                        die('<error>Error - The value of parameter observedProperty is not supported. Supported values: '.$supportedObservedPropertyValues.' Values have to be separated by commas.</error>');
                    }
                    $i++;
                }
            }
            else {
                die('<error>Error - No observedProperty specified. Please set the observedProperty parameter. Supported values: '.$supportedObservedPropertyValues.' Values have to be separated by commas.</error>');
            }
            
            if ( isset($_GET['featureOfInterest']) ) {
                $featureOfInterest = $_GET['featureOfInterest'];
                if ( ! preg_match('/^[A-Za-z0-9:]+-+[0-9]{1,10}$/', $featureOfInterest) || ! strpos($featureOfInterest, 'airqualityegg-') ) {
                    die('<error>Error - The featureOfInterest parameter does not match the required format.</error>');
                }
                else {
                    $index = strpos($featureOfInterest, 'airqualityegg-') + 14;
                    $length = strlen($featureOfInterest) - $index;
                    $this->feedId = substr($featureOfInterest, $index, $length);
                    if ( ! is_numeric($this->feedId) ) {
                        die('<error>Error - Feed ID incorrect.</error>');
                    }
                }
            }
            else {
                die('<error>Error - No featureOfInterest specified. Please set the featureOfInterest parameter.</error>');
            }
                
            if ( isset($_GET['eventTime']) ) {
                $eventTime = explode('/', $_GET['eventTime']);
                $eventTime[0] = str_replace('-', '', substr($eventTime[0], 0, -3));
                $eventTime[1] = str_replace('-', '', substr($eventTime[1], 0, -3));
                $this->startTime = strtotime($eventTime[0]);
                $this->endTime = strtotime($eventTime[1]);
                if ( $this->startTime <= 1 || $this->endTime <= 1 ) {
                    die('<error>Error - The given eventTime does not match the required format.</error>');
                }
            }
            else {
                $this->startTime = date($this->cosmTimeFormat, time() - 21600);
                $this->endTime = date($this->cosmTimeFormat, time());
            }
            
            if ( isset($_GET['outlierInterpolation']) && is_numeric($_GET['outlierInterpolation']) && $_GET['outlierInterpolation'] >= 0 && $_GET['outlierInterpolation'] <= 3 ) {
                $this->outlierInterpolation = $_GET['outlierInterpolation'];
            }
		}
		
		private function getCosmData() {
            if ( $this->parameter['request'] == 'getObservation' ) {
                $start = date($this->cosmTimeFormat, $this->startTime);
                $end = date($this->cosmTimeFormat, $this->endTime);
                $values = 'all_values';
                $interval = $this->determineInterval();
			
			    $cosmAPI = new CosmAPI();
                $this->dataArray = $cosmAPI->parseFeed($this->feedId, $values, $start, $end, $this->limit, $interval, '');
			
                if ( is_array($this->dataArray) ) {
			        foreach ( $this->metadata as $mdata ) {
                        unset($this->dataArray[$mdata]);
                    }
                }
                else {
                    die('<error>'.translate($this->dataArray).'</error>');
                }
                print 'bla';
            }
            else if ( $this->parameter['request'] == 'describeSensor' ) {
                $cosmAPI = new CosmAPI();
                $this->dataArray = $cosmAPI->parseFeed($this->feedId, 'current_value', 0, 0, 0, 0, '');
            }
		}
        
        private function determineInterval() {
            $duration = $this->endTime - $this->startTime;
            foreach ( $this->intervals as $interval => $maxRange ) {
                if ( $duration <= $maxRange ) {
                    return $interval;
                }
            }
        }
        
        private function determineTimeframe() {
            $duration = $this->endTime - $this->startTime;
            foreach ( $this->timeframes as $timeframe => $seconds ) {
                if ( $duration <= $seconds ) {
                    return $timeframe;
                }
            }
        }
        
        private function applyDataValidation() {
            include('class/datavalidation.class.php');
            $this->dataValidation = new DataValidation($this->dataArray, $this->supportedObservedProperties, $this->outlierInterpolation, $this->determineTimeframe());
            $outliers = $this->dataValidation->getOutliers();
            $this->dataArray = $this->dataValidation->interpolateOutliers($outliers);
        }
		
		private function printObservationXml() {
			$this->xmlTemplate->readTpl('SOSgetObservation');
			
			$id = 1;
			foreach ( $this->observedProperty as $sensor ) {
				foreach ( $this->dataArray as $time => $val ) {
					if ( isset($val[$sensor]) ) {
						$this->xmlTemplate->copyCode('observationData');
						$this->xmlTemplate->tplReplaceOnce('time', date($this->sosTimeFormat, $time));
						$this->xmlTemplate->tplReplaceOnce('id', $id);
						$this->xmlTemplate->tplReplaceOnce('id', $id);
						$this->xmlTemplate->tplReplaceOnce('id', $id);
						$this->xmlTemplate->tplReplaceOnce('sensor', $sensor);
						$this->xmlTemplate->tplReplaceOnce('unit', $this->units[$sensor]);
						$this->xmlTemplate->tplReplaceOnce('value', $val[$sensor]);
						$id++;
					}
				}
			}
			$this->xmlTemplate->cleanCode('observationData');
			$this->xmlTemplate->tplReplace('feedId', $this->feedId);
			$this->xmlTemplate->printTemplate();
		}
        
        private function printCapabilitiesXml() {
			$this->xmlTemplate->readTpl('SOSgetCapabilities');
			$this->xmlTemplate->printTemplate();
        }
        
        private function getDescribeSensorParameters() {
            if ( isset($_GET['procedure']) ) {
                $procedure = $_GET['procedure'];
                if ( ! preg_match('/^[A-Za-z0-9:]+-+[0-9]{1,10}$/', $procedure) || ! strpos($procedure, 'airqualityegg-') ) {
                    die('<error>Error - The procedure parameter does not match the required format.</error>');
                }
                else {
                    $index = strpos($procedure, 'airqualityegg-') + 14;
                    $length = strlen($procedure) - $index;
                    $this->feedId = substr($procedure, $index, $length);
                    if ( ! is_numeric($this->feedId) ) {
                        die('<error>Error - Feed ID incorrect.</error>');
                    }
                }
            }
            else {
                die('<error>Error - No procedure specified. Please set the procedure parameter.</error>');
            }
        }
        
        private function printDescribeSensorXml() {
			$this->xmlTemplate->readTpl('SOSdescribeSensor');
            $this->xmlTemplate->tplReplace('procedure', 'urn:ogc:object:feature:sensor:airqualityegg-'.$this->feedId);
            
            $this->dataArray['lon'] = str_replace('&deg;', '', $this->dataArray['lon']);
            $this->dataArray['lat'] = str_replace('&deg;', '', $this->dataArray['lat']);
            $this->dataArray['lon'] = str_replace('°', '', $this->dataArray['lon']);
            $this->dataArray['lat'] = str_replace('°', '', $this->dataArray['lat']);
            $this->xmlTemplate->tplReplace('lon', $this->dataArray['lon']);
            $this->xmlTemplate->tplReplace('lat', $this->dataArray['lat']);
            
            if ( $this->dataArray['ele'] != translate('not_available') ) {
                $alt = explode(' ', $this->dataArray['ele']);
                if ( ! isset($alt[1]) ) { $alt[1] = 'unknown'; }
            }
            else {
                $alt[1] = '';
                $alt[0] = translate('not_available');
            }
            
            $this->xmlTemplate->tplReplace('alt_unit', $alt[1]);
            $this->xmlTemplate->tplReplace('alt', $alt[0]);
			$this->xmlTemplate->printTemplate();
        }
	}
	
	new SOS();
?>