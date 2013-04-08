<?php
    include('class/simplexmlextended.class.php');
    include('class/cosmapi.class.php');
    include('class/mysqlconnection.class.php');
    
    class CreateEgg {
        private $feedId;
        private $limit = 1000;
        private $metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
        private $sensors = array('co', 'no2', 'humidity', 'temperature');
        
        public function __construct() {
            print $this->createEgg();
        }
        
        private function createEgg() {
            if ( isset($_GET['fid']) && is_numeric($_GET['fid']) ) {
                $this->feedId = $_GET['fid'];
                
                $cosmAPI = new CosmAPI();
                $mySqlConnection = new MySqlConnection();
                
                mysql_query('CREATE TABLE IF NOT EXISTS `eggdata_'.$this->feedId.'` ( `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `co` double(9,3) NOT NULL, `no2` double(9,3) NOT NULL, `temperature` double(6,2) NOT NULL, `humidity` double(6,3) NOT NULL, PRIMARY KEY (`timestamp`) ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');
                $num_rows = mysql_num_rows(mysql_query('SELECT `timestamp` FROM `eggdata_'.$this->feedId.'`'));
                
                if ( $num_rows < 1 ) {
                    $metadata = false;
                    for ( $seconds = 0; $seconds <= 3; $seconds++ ) {
                        $start = time() - $seconds * 60000 - 60000;
                        $end = time() - $seconds * 60000;
                        
                        if ( ! $dataArray = $cosmAPI->parseFeed($this->feedId, $start, $end, $this->limit, 60) ) {
                            return 'cosm_error';
                        }
                        else if ( ! is_array($dataArray) ) {
                            return $dataArray;
                        }
                        else {
                            if ( ! $metadata ) {
                                mysql_query('UPDATE `egg` SET `title` = \''.$dataArray['title'].'\', `description` = \''.$dataArray['description'].'\', `location_name` = \''.$dataArray['locationName'].'\', `ele` = \''.$dataArray['ele'].'\', `status` = \''.$dataArray['status'].'\', `exposure` = \''.$dataArray['exposure'].'\', `lastupdated`=\''.time().'\' WHERE `feed_id` = '.$this->feedId);
                            }
                            
                            foreach ( $metadata as $mdata ) {
                                if ( isset($dataArray[$mdata]) ) {
                                    unset($dataArray[$mdata]);
                                }
                            }
                            
                            foreach ( $dataArray as $time => $val ) {
                                $nullsensors = 0;
                                foreach ( $this->sensors as $sensor ) {
                                    if ( ! isset($val[$sensor]) ) {
                                        $val[$sensor] = 0;
                                        $nullsensors++;
                                    }
                                }
                                if ( $nullsensors < 4 && floatval($time) != 0.0 ) {
                                    mysql_query('INSERT INTO `eggdata_'.$this->feedId.'` ( `timestamp`, `co`, `no2`, `temperature`, `humidity` ) VALUES (\''.date('Y-m-d H:i:s', $time).'\',  \''.$val['co'].'\',  \''.$val['no2'].'\',  \''.$val['temperature'].'\',  \''.$val['humidity'].'\')');
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    new CreateEgg();
?>