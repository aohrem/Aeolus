<?php
class Download extends DataVisualisation {
    public function __construct($contentTemplate) {
        parent::__construct($contentTemplate);
        
        $this->outlierInterpolation();
        $this->replaceCosmLinks();
    }
    
    private function outlierInterpolation() {
        $tplOutliers = '';
        $css_checked = ' checked="checked"';
        
        // check if outliers shall be interpolated
        if ( isset($_GET['interpolateoutliers']) && $_GET['interpolateoutliers'] == 'true' ) {
            $this->contentTemplate->tplReplace('io_checked', $css_checked);
            $this->contentTemplate->tplReplace('dio_checked', '');
            $this->contentTemplate->tplReplace('interpolateOutliers', 'true');
        }
        else {
            $this->contentTemplate->tplReplace('io_checked', '');
            $this->contentTemplate->tplReplace('dio_checked', $css_checked);
            $this->contentTemplate->tplReplace('interpolateOutliers', 'false');
        }
        $this->contentTemplate->tplReplace('outliers', $tplOutliers);
    }
    
    private function replaceCosmLinks() {
        // set parameters for the API request
        $start = date($this->cosmTimeFormat, time() - $this->seconds[$this->timeframe]);
        $end = date($this->cosmTimeFormat, time());
        $values = 'all_values';
        $interval = $this->interval[$this->timeframe];
        
        // cosm-API integration
        $cosmAPI = new CosmAPI();
        
        // fill in the parameters to read the cosm-API
        $cosmAPI->setRequestUrl($this->feedId, $start, $end, $this->limit, $interval, '', 'xml');
        $this->contentTemplate->tplReplace('cosm_link_xml', htmlentities($cosmAPI->getRequestUrl()));
        $cosmAPI->setRequestUrl($this->feedId, $start, $end, $this->limit, $interval, '', 'json');
        $this->contentTemplate->tplReplace('cosm_link_json', htmlentities($cosmAPI->getRequestUrl()));
        $cosmAPI->setRequestUrl($this->feedId, $start, $end, $this->limit, $interval, '', 'csv');
        $this->contentTemplate->tplReplace('cosm_link_csv', htmlentities($cosmAPI->getRequestUrl()));
    }
}
?>