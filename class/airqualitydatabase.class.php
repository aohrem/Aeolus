<?php
    class AirQualityDatabase {
        // array containing the amount of seconds a time frame consists of
        private $seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
        
        // array containing which rows shall be read from the database (i.e. every 5th row for 24 hours)
        private $mods = array('6h' => 1, '24h' => 5, '48h' => 10, '1w' => 60, '1m' => 120, '3m' => 360);
        private $sensors = array('co', 'no2', 'humidity', 'temperature');
        private $sqlTimeFormat = 'Y-m-d H:i:s';
        
        // attributes containing the current mod and start and end date and time (as unix timestamp) for the database requests
        private $mod;
        private $start;
        private $end;
        
        public function __construct($feedId, $timeframe) {
            $this->feedId = $feedId;
            
            // set the timezone to UTC to get current values from the database
            date_default_timezone_set('UTC');
            
            // determine start, end and mod
            $this->start = time() - $this->seconds[$timeframe];
            $this->end = time();
            $this->mod = $this->mods[$timeframe];
        }
    
        // returns values in time frame saved in the object and meta data for the AQE as an associative array
        public function getValues() {
            // get meta data from database
            $eggQuery = mysql_query('SELECT `lat`, `lon`, `title`, `description`, `location_name`, `ele`, `status`, `exposure` FROM `egg` WHERE `feed_id` = '.$this->feedId);
            
            // check if query was a valid MySQL resource
            if ( is_resource($eggQuery) && mysql_num_rows($eggQuery) == 1 ) {
                // save meta data to the data array
                $egg = mysql_fetch_object($eggQuery);
                $dataArray = $this->getMetadata($egg);
                
                // get sensor values from database and save it to the data array
                $eggQuery = mysql_query('SELECT `timestamp`, `co`, `no2`, `temperature`, `humidity`
                    FROM `eggdata_'.$this->feedId.'` e
                    JOIN ( SELECT @i := 0 ) i
                    WHERE `timestamp` > \''.date($this->sqlTimeFormat, $this->start).'\'
                        AND  `timestamp` < \''.date($this->sqlTimeFormat, $this->end).'\'
                        AND ( MOD( @i := @i +1, '.$this->mod.' ) = 0 )
                    ORDER BY `timestamp`');
                
                if ( is_resource($eggQuery) ) {
                    // iterate data in the database table
                    while ( $row = mysql_fetch_object($eggQuery) ) {
                        // save value for each sensor and timestamp into the data array
                        foreach ( $this->sensors as $sensor ) {
                            $dataArray[strtotime($row->timestamp)][$sensor] = $row->$sensor;
                        }
                    }
                }
                
                return $dataArray;
            }
            // if query was not a valid MySQL resource
            else {
                return 'egg_not_found';
            }
        }
        
        // returns values in time frame given in the parameters and meta data for the AQE as an associative array
        public function getValuesInTimeframe($start, $end) {
            // get meta data from database
            $eggQuery = mysql_query('SELECT `lat`, `lon`, `title`, `description`, `location_name`, `ele`, `status`, `exposure` FROM `egg` WHERE `feed_id` = '.$this->feedId);
            
            // check if query was a valid MySQL resource
            if ( is_resource($eggQuery) && mysql_num_rows($eggQuery) == 1 ) {
                // save meta data to the data array
                $egg = mysql_fetch_object($eggQuery);
                $dataArray = $this->getMetadata($egg);
                
                // get sensor values from database and save it to the data array
                $eggQuery = mysql_query('SELECT `timestamp`, `co`, `no2`, `temperature`, `humidity`
                    FROM `eggdata_'.$this->feedId.'` e
                    JOIN ( SELECT @i := 0 ) i
                    WHERE `timestamp` > \''.date($this->sqlTimeFormat, $start).'\'
                        AND  `timestamp` < \''.date($this->sqlTimeFormat, $end).'\'
                        AND ( MOD( @i := @i +1, '.$this->mod.' ) = 0 )
                    ORDER BY `timestamp`');
                
                if ( is_resource($eggQuery) ) {
                    // iterate data in the database table
                    while ( $row = mysql_fetch_object($eggQuery) ) {
                        // save value for each sensor and timestamp into the data array
                        foreach ( $this->sensors as $sensor ) {
                            $dataArray[strtotime($row->timestamp)][$sensor] = $row->$sensor;
                        }
                    }
                }
                
                return $dataArray;
            }
            // if query was not a valid MySQL resource
            else {
                return 'egg_not_found';
            }
        }
        
        // returns a data array with meta data and the current sensor data values of the AQE
        public function getCurrentValues() {
            // get meta data from database
            $eggQuery = mysql_query('SELECT `lat`, `lon`, `title`, `description`, `location_name`, `ele`, `status`, `exposure` FROM `egg` WHERE `feed_id` = '.$this->feedId);
            
            // check if query was a valid MySQL resource
            if ( is_resource($eggQuery) && mysql_num_rows($eggQuery) == 1 ) {
                // save meta data to the data array
                $egg = mysql_fetch_object($eggQuery);
                $dataArray = $this->getMetadata($egg);
                
                // iterate sensors, ...
                foreach ( $this->sensors as $sensor ) {
                    // ... generate a SQL query for each sensor to get the current value...
                    $query = mysql_query('SELECT `'.$sensor.'` FROM `eggdata_'.$this->feedId.'` WHERE ABS(`'.$sensor.'`) != 0.0 ORDER BY `timestamp` DESC LIMIT 1');
                    
                    // ... and save the current value to the data array if the query was valid
                    if ( is_resource($query) && $egg = mysql_fetch_array($query) ) {
                        $dataArray['current_value'][$sensor] = $egg[$sensor];
                    }
                }
                
                return $dataArray;
            }
            // if query was not a valid MySQL resource
            else {
                return 'egg_not_found';
            }
        }
        
        // saves meta data of the AQE to the data array
        private function getMetadata($egg) {
            $dataArray['title'] = $egg->title != '' ? htmlentities($egg->title).' - ' : '';
            $dataArray['description'] = $egg->description != '' ? htmlentities($egg->description) : $GLOBALS['translation']['no_description_available'];
            $dataArray['locationName'] = $egg->location_name != '' ? htmlentities($egg->location_name) : $GLOBALS['translation']['not_available'];
            $dataArray['lat'] = floatval($egg->lat) != 0.0 ? $egg->lat.'&deg;' : $GLOBALS['translation']['not_available'];
            $dataArray['lon'] = floatval($egg->lon) != 0.0 ? $egg->lon.'&deg;' : $GLOBALS['translation']['not_available'];
            $dataArray['ele'] = $egg->ele != '' ? htmlentities($egg->ele) : $GLOBALS['translation']['not_available'];
            $dataArray['status'] = $egg->status != '' ? htmlentities($egg->status) : $GLOBALS['translation']['unknown'];
            $dataArray['exposure'] = $egg->exposure != '' ? htmlentities($egg->exposure) : $GLOBALS['translation']['unknown'];
            return $dataArray;
        }
    }
?>