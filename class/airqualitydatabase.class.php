<?php
class AirQualityDatabase {
        private $seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
        private $mods = array('6h' => 'MOD(MINUTE(timestamp), 1) = 0',
                              '24h' => 'MOD(MINUTE(timestamp), 5) = 0',
                              '48h' => 'MOD(MINUTE(timestamp), 10) = 0',
                              '1w' => 'MINUTE(timestamp) = 0',
                              '1m' => 'MOD(HOUR(timestamp), 2) = 0 AND MINUTE(timestamp) = 0',
                              '3m' => 'MOD(HOUR(timestamp), 6) = 0 AND MINUTE(timestamp) = 0');
        private $sensors = array('co', 'no2', 'humidity', 'temperature');
        private $sqlTimeFormat = 'Y-m-d H:i:s';
        private $limit = 1000;
        
        private $mod;
        private $start;
        private $end;
        
        public function __construct($feedId, $timeframe) {
            $this->feedId = $feedId;
            date_default_timezone_set('UTC');
            $this->start = time() - $this->seconds[$timeframe];
            $this->end = time();
            $this->mod = $this->mods[$timeframe];
        }
    
        public function getValues() {
            // get meta data from database and save it to the data array
            $eggQuery = mysql_query('SELECT `lat`, `lon`, `title`, `description`, `location_name`, `ele`, `status`, `exposure` FROM `egg` WHERE `feed_id` = '.$this->feedId);
            if ( is_resource($eggQuery) && mysql_num_rows($eggQuery) == 1 ) {
                $egg = mysql_fetch_object($eggQuery);
                $dataArray = $this->getMetadata($egg);
                
                // get sensor values from database and save it to the data array
                $eggQuery = mysql_query('SELECT `timestamp`, `co`, `no2`, `temperature`, `humidity` FROM `eggdata_'.$this->feedId.'` WHERE ( `timestamp` > \''.date($this->sqlTimeFormat, $this->start).'\' AND  `timestamp` < \''.date($this->sqlTimeFormat, $this->end).'\' ) AND ( '.$this->mod.' ) ORDER BY `timestamp`');
                if ( is_resource($eggQuery) ) {
                    while ( $row = mysql_fetch_object($eggQuery) ) {
                        foreach ( $this->sensors as $sensor ) {
                            $dataArray[strtotime($row->timestamp)][$sensor] = $row->$sensor;
                        }
                    }
                }
                
                return $dataArray;
            }
            else {
                return 'egg_not_found';
            }
        }
        
        public function getValuesInTimeframe($start, $end) {
            // get meta data from database and save it to the data array
            $eggQuery = mysql_query('SELECT `lat`, `lon`, `title`, `description`, `location_name`, `ele`, `status`, `exposure` FROM `egg` WHERE `feed_id` = '.$this->feedId);
            if ( is_resource($eggQuery) && mysql_num_rows($eggQuery) == 1 ) {
                $egg = mysql_fetch_object($eggQuery);
                $dataArray = $this->getMetadata($egg);
                
                // get sensor values from database and save it to the data array
                $eggQuery = mysql_query('SELECT `timestamp`, `co`, `no2`, `temperature`, `humidity` FROM `eggdata_'.$this->feedId.'` WHERE ( `timestamp` > \''.date($this->sqlTimeFormat, $start).'\' AND  `timestamp` < \''.date($this->sqlTimeFormat, $end).'\' ) AND ( '.$this->mod.' ) ORDER BY `timestamp`');
                if ( is_resource($eggQuery) ) {
                    while ( $row = mysql_fetch_object($eggQuery) ) {
                        foreach ( $this->sensors as $sensor ) {
                            $dataArray[strtotime($row->timestamp)][$sensor] = $row->$sensor;
                        }
                    }
                }
                
                return $dataArray;
            }
            else {
                return 'egg_not_found';
            }
        }
        
        public function getCurrentValues() {
            // get meta data from database and save it to the data array
            $eggQuery = mysql_query('SELECT `lat`, `lon`, `title`, `description`, `location_name`, `ele`, `status`, `exposure` FROM `egg` WHERE `feed_id` = '.$this->feedId);
            if ( is_resource($eggQuery) && mysql_num_rows($eggQuery) == 1 ) {
                $egg = mysql_fetch_object($eggQuery);
                $dataArray = $this->getMetadata($egg);
                
                foreach ( $this->sensors as $sensor ) {
                    $query = mysql_query('SELECT `'.$sensor.'` FROM `eggdata_'.$this->feedId.'` WHERE FLOOR(ABS(`'.$sensor.'`)) != 0.0 ORDER BY `timestamp` DESC LIMIT 1');
                    if ( is_resource($query) && $egg = mysql_fetch_array($query) ) {
                        $dataArray['current_value'][$sensor] = $egg[$sensor];
                    }
                }
                
                return $dataArray;
            }
            else {
                return 'egg_not_found';
            }
        }
        
        private function getMetadata($egg) {
            $dataArray['title'] = $egg->title != '' ? htmlentities($egg->title).' - ' : '';
            $dataArray['description'] = $egg->description != '' ? htmlentities($egg->description) : $GLOBALS['translation']['no_description_available'];
            $dataArray['locationName'] = $egg->location_name != '' ? htmlentities($egg->location_name) : $GLOBALS['translation']['not_available'];
            $dataArray['lat'] = intval($egg->lat) != 0 ? $egg->lat.'&deg;' : $GLOBALS['translation']['not_available'];
            $dataArray['lon'] = intval($egg->lon) != 0 ? $egg->lon.'&deg;' : $GLOBALS['translation']['not_available'];
            $dataArray['ele'] = $egg->ele != '' ? htmlentities($egg->ele) : $GLOBALS['translation']['not_available'];
            $dataArray['status'] = $egg->status != '' ? htmlentities($egg->status) : $GLOBALS['translation']['unknown'];
            $dataArray['exposure'] = $egg->exposure != '' ? htmlentities($egg->exposure) : $GLOBALS['translation']['unknown'];
            return $dataArray;
        }
    }
?>