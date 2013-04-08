<?php
if ( $_GET['pass'] == 'faf01b58a34e26f5ed05a4edc5e5c3ee' ) {
    include('class/simplexmlextended.class.php');
    include('class/cosmapi.class.php');
    include('class/mysqlconnection.class.php');

    class UpdateEgg {
        private $feedId;
        private $limit = 50;
        private $metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
        private $sensors = array('co', 'no2', 'humidity', 'temperature');
    
        public function __construct() {
            print $this->updateEgg();
        }
    
        private function updateEgg() {
            if ( isset($_GET['fid']) && is_numeric($_GET['fid']) ) {
                $this->feedId = $_GET['fid'];
            
                $cosmAPI = new CosmAPI();
                $mySqlConnection = new MySqlConnection();
                
                $egg = mysql_fetch_object(mysql_query('SELECT `lastupdated` FROM `egg` WHERE `feed_id` = '.$this->feedId));
                if ( $egg->lastupdated > 0 ) {
                    $start = $egg->lastupdated;
                }
                else {
                    $start = time() - 82800;
                }
                date_default_timezone_set('UTC');
                $end = time();
                    
                if ( ! $dataArray = $cosmAPI->parseFeed($this->feedId, $start, $end, $this->limit, 60) ) {
                    return 'cosm_error';
                }
                else if ( ! is_array($dataArray) ) {
                    return $dataArray;
                }
                else {
                    mysql_query('UPDATE `egg` SET `title` = \''.$dataArray['title'].'\', `description` = \''.$dataArray['description'].'\', `location_name` = \''.$dataArray['locationName'].'\', `ele` = \''.$dataArray['ele'].'\', `status` = \''.$dataArray['status'].'\', `exposure` = \''.$dataArray['exposure'].'\', `lastupdated` = \''.time().'\' WHERE `feed_id` = '.$this->feedId);
                    
                    foreach ( $this->metadata as $mdata ) {
                        if ( isset($dataArray[$mdata]) ) {
                            unset($dataArray[$mdata]);
                        }
                    }
                    
                    foreach ( $dataArray as $time => $val ) {
                        foreach ( $this->sensors as $sensor ) {
                            if ( ! isset($val[$sensor]) ) {
                                $val[$sensor] = 0;
                            }
                        }
						if ( floatval($time) != 0.0 ) {
							mysql_query('INSERT INTO `eggdata_'.$this->feedId.'` ( `timestamp`, `co`, `no2`, `temperature`, `humidity` ) VALUES (\''.date('Y-m-d H:i:s', $time).'\',  \''.$val['co'].'\',  \''.$val['no2'].'\',  \''.$val['temperature'].'\',  \''.$val['humidity'].'\')');
						}
                    }
                }
            }
        }
    }

    new UpdateEgg();
}
?>