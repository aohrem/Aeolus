<?php
    include('lang/en.lang.php');
    include('functions.inc.php');
	include('class/template.class.php');
	include('class/simplexmlextended.class.php');
	include('class/cosmapi.class.php');
	
	class SOS {
		private $siteUrl;
        
        private $parameters = array('request' => array('getObservation', 'getCapabilities', 'describeSensor'),
            'service' => array('sos'),
            'version' => array('1.0.0'),
            'responseFormat' => array('text/xml;subtype="om/1.0.0"'));
        private $parameter;
        
        private $supportedObservedProperties = array('co', 'no2', 'humidity', 'temperature');
        private $observedPropertyUrn = 'urn:ogc:def:phenomenon:';
        private $observedProperty;
        
		private $feedId;
		private $sensor;
		private $supportedTimeframes = array('6h', '24h', '48h', '1w', '1m', '3m');
		private $defaultTimeframe = '6h';
		private $timeframe;
        
		private $metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
        private $sensors = array('co', 'no2', 'humidity', 'temperature');
		private $units = array('co' => 'ppm', 'no2' => 'ppm', 'humidity' => '%', 'temperature' => 'degreesCelsius');
        private $seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
		private $interval = array('6h' => 60, '24h' => 300, '48h' => 600, '1w' => 10800, '1m' => 604800, '3m' => 2678400);
		private $limit = 5;
		
		private $cosmTimeFormat = 'Y-m-d\TH:i:s\Z';
		private $sosTimeFormat = 'Y-m-d\TH:i:s.000+00:00';
		
		private $dataArray;
	
		public function __construct() {
            header('Content-type: application/xml');
            $this->getParameters();
            
            switch ( $this->paramater['request'] ) {
                case 'getObservation':
                    $this->getCosmData();
                    $this->printObservationXml();
                    break;
            }
		}
		
		private function getParameters() {
			$this->siteUrl = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            
            foreach ( $this->parameters as $parameter => $supportedValues ) {
                if ( isset($_GET[$parameter]) ) {
                    $this->paramater[$parameter] = $_GET[$parameter];
                }
                else {
                    die('<error>Error - No '.$parameter.' specified. Please set the '.$parameter.' parameter.</error>');
                }
                
                if ( ! in_array($this->paramater[$parameter], $supportedValues) ) {
                    die('<error>Error - The value of parameter '.$parameter.' is not supported. Supported values: '.implode(', ', $supportedValues).'</error>');
                }
            }
            
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
            
            if ( isset($_GET['fid']) && is_numeric($_GET['fid']) ) {
                $this->feedId = $_GET['fid'];
            }
            else if ( $this->request != 'getCapabilities' ) {
                die('<error>Error - Feed ID incorrect.</error>');
            }
                
            if ( ! in_array($this->sensor, $this->sensors) ) {
                $this->sensor = 'all';
            }
                
            if ( isset($_GET['timeframe']) ) {
                $this->timeframe = $_GET['timeframe'];
            }
                
            if ( ! in_array($this->timeframe, $this->supportedTimeframes) ) {
                $this->timeframe = $this->defaultTimeframe;
            }
		}
		
		private function getCosmData() {
            $start = date($this->cosmTimeFormat, time() - $this->seconds[$this->timeframe]);
            $end = date($this->cosmTimeFormat, time());
            $values = 'all_values';
            $interval = $this->interval[$this->timeframe];
			
			$cosmAPI = new CosmAPI();
            $this->dataArray = $cosmAPI->parseFeed($this->feedId, $values, $start, $end, $this->limit, $interval, '');
			
			foreach ( $this->metadata as $mdata ) {
                unset($this->dataArray[$mdata]);
            }
		}
		
		private function printObservationXml() {
			$xmlTemplate = new Template();
			$xmlTemplate->setFileExtension('.xml');
			$xmlTemplate->setFolder('xml/');
			$xmlTemplate->readTpl('SOSgetObservation');
			
			$id = 1;
			foreach ( $this->observedProperty as $sensor ) {
				foreach ( $this->dataArray as $time => $val ) {
					if ( isset($val[$sensor]) ) {
						$xmlTemplate->copyCode('observationData');
						$xmlTemplate->tplReplaceOnce('time', date($this->sosTimeFormat, $time));
						$xmlTemplate->tplReplaceOnce('id', $id);
						$xmlTemplate->tplReplaceOnce('id', $id);
						$xmlTemplate->tplReplaceOnce('id', $id);
						$xmlTemplate->tplReplaceOnce('sensor', $sensor);
						$xmlTemplate->tplReplaceOnce('unit', $this->units[$sensor]);
						$xmlTemplate->tplReplaceOnce('value', $val[$sensor]);
						$id++;
					}
				}
			}
			$xmlTemplate->cleanCode('observationData');
			$xmlTemplate->tplReplace('feedId', $this->feedId);
			
			$xmlTemplate->printTemplate();
		}
		
		public function __desctruct() {
		}
	}
	
	new SOS();
?>