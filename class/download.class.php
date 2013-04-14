<?php
    class Download extends DataVisualisation {
        private $seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
        private $interval = array('6h' => 60, '24h' => 300, '48h' => 600, '1w' => 10800, '1m' => 604800, '3m' => 2678400);
        private $limit = 1000;
        private $sosTimeFormat = 'Y-m-d\TH:i:s+00';
    
        // constructor calls all necassary methods to build the download page
        public function __construct($contentTemplate) {
            parent::__construct($contentTemplate);
        
            $this->outlierInterpolation();
            $this->replaceCosmLinks();
            $this->replaceSosTimes();
        }
    
        private function outlierInterpolation() {
            $tplOutliers = '';
            $css_checked = ' checked="checked"';
        
            // check if outliers shall be interpolated
            if ( isset($_GET['interpolateoutliers']) && $_GET['interpolateoutliers'] == 'true' ) {
                $this->contentTemplate->tplReplace('io_checked', $css_checked);
                $this->contentTemplate->tplReplace('dio_checked', '');
                $this->contentTemplate->tplReplace('interpolateOutliers', 'true');
                $this->contentTemplate->tplReplace('outlierInterpolation', $this->sensitivity);
            }
            // if not, the the necassary replacements
            else {
                $this->contentTemplate->tplReplace('io_checked', '');
                $this->contentTemplate->tplReplace('dio_checked', $css_checked);
                $this->contentTemplate->tplReplace('interpolateOutliers', 'false');
                $this->contentTemplate->tplReplace('outlierInterpolation', 0);
            }
            $this->contentTemplate->tplReplace('outliers', $tplOutliers);
        }
    
        private function replaceCosmLinks() {
            // set parameters for the API request
            $start = time() - $this->seconds[$this->timeframe];
            $end = time();
            $interval = $this->interval[$this->timeframe];
        
            // cosm-API integration
            $cosmAPI = new CosmAPI();
        
            // fill in the parameters to read the cosm-API
            $cosmAPI->setRequestUrl($this->feedId, $start, $end, $this->limit, $interval, 'xml');
            $this->contentTemplate->tplReplace('cosm_link_xml', htmlentities($cosmAPI->getRequestUrl()));
            $cosmAPI->setRequestUrl($this->feedId, $start, $end, $this->limit, $interval, 'json');
            $this->contentTemplate->tplReplace('cosm_link_json', htmlentities($cosmAPI->getRequestUrl()));
            $cosmAPI->setRequestUrl($this->feedId, $start, $end, $this->limit, $interval, 'csv');
            $this->contentTemplate->tplReplace('cosm_link_csv', htmlentities($cosmAPI->getRequestUrl()));
        }
    
        private function replaceSosTimes() {
            $start = date($this->sosTimeFormat, time() - $this->seconds[$this->timeframe]);
            $end = date($this->sosTimeFormat, time());
            $sosTimes = $start.'/'.$end;
            $this->contentTemplate->tplReplace('sostimes', $sosTimes);
        }
    }
?>