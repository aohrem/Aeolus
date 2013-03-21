<?php
class Table extends DataVisualisation {
    private $interpolateOutliers;
    
    public function __construct($contentTemplate) {
        parent::__construct($contentTemplate);
        
        $this->outlierInterpolation();
        if ( $this->cosmSuccess ) {
            $this->fillTable();
        }
    }
    
    private function outlierInterpolation() {
        $tplOutliers = '';
        $css_checked = ' checked="checked"';
        
        // check if outliers shall be interpolated
        if ( isset($_GET['interpolateoutliers']) && $_GET['interpolateoutliers'] == 'true' ) {
            $this->interpolateOutliers = true;
            $this->contentTemplate->tplReplace('io_checked', $css_checked);
            $this->contentTemplate->tplReplace('dio_checked', '');
            $this->contentTemplate->tplReplace('interpolateOutliers', 'true');
            
            if ( $this->cosmSuccess && $this->dataValidation->containsOutliers($this->outliers) && $this->sensitivity != 0 ) {
                $tplOutliers = '<a href="index.php?s=table&amp;fid='.$this->feedId.'&amp;timeframe='.$this->timeframe.'&amp;interpolateoutliers=false&amp;sensitivity='.$this->sensitivity.'&amp;lang={language}"><span class="bigoutlier interpolated success" onMouseOver="outlierNote(\'outliers_interpolated\');" onMouseOut="outlierNote(\'outliers_interpolated\');">i</span></a><div id="outliers_interpolated" class="bigoutlierhint interpolated">'.translate('outliers_interpolated').'</div>';
                
                // interpolate outliers
                $this->dataArray = $this->dataValidation->interpolateOutliers($this->outliers);
            }
        }
        else {
            $this->interpolateOutliers = false;
            $this->contentTemplate->tplReplace('io_checked', '');
            $this->contentTemplate->tplReplace('dio_checked', $css_checked);
            $this->contentTemplate->tplReplace('interpolateOutliers', 'false');
            
            // check if dataset contains outliers and outlier detection is on
            if ( $this->cosmSuccess && $this->dataValidation->containsOutliers($this->outliers) && $this->sensitivity != 0 ) {
                $tplOutliers = '<a href="index.php?s=table&amp;fid='.$this->feedId.'&amp;timeframe='.$this->timeframe.'&amp;interpolateoutliers=true&amp;sensitivity='.$this->sensitivity.'&amp;lang={language}"><span class="bigoutlier error" onMouseOver="outlierNote(\'outliers_found\');" onMouseOut="outlierNote(\'outliers_found\');">!</span></a><div id="outliers_found" class="bigoutlierhint">'.translate('outliers_found').'</div>';
            }
        }
        $this->contentTemplate->tplReplace('outliers', $tplOutliers);
    }
    
    private function fillTable() {
        // iterate sensor data
        foreach ( $this->dataArray as $time => $val ) {
            // copy table row and fill in sensor data for one timestamp
            $this->contentTemplate->copyCode('tableRow');
            
            // iterate sensors
            foreach ( $this->sensors as $sensor ) {
                // replace date and time
                $this->contentTemplate->tplReplaceOnce('t', date(translate('php_time_format'), $time));
                
                // if there is no data, show a -
                if ( ! isset($val[$sensor]) ) { $val[$sensor] = '-'; }
                $this->contentTemplate->tplReplaceOnce($sensor, $val[$sensor]);
                
                // mark outliers with a hint box
                $outlierTemplate = new Template();
                if ( $this->outliers[$sensor][$time] && $this->sensitivity != 0 ) {
                    $outlierTemplate->readTpl('table_outlier_box');
                    
                    if ( $this->interpolateOutliers ) {
                        $replaceArray = array(
                            'sensor' => $sensor,
                            'text' => 'i',
                            'css_color' => 'success',
                            'css_style' => 'interpolated',
                            'time' => $time,
                            'hint' => translate('value_is_interpolated'));
                    }
                    else {
                        $replaceArray = array(
                            'sensor' => $sensor,
                            'text' => '!',
                            'css_color' => 'error',
                            'css_style' => '',
                            'time' => $time,
                            'hint' => translate('value_could_be_an_outlier'));
                    }
                    
                    $outlierTemplate->tplMultipleReplace($replaceArray);
                }
                
                // replace outlier hint box
                $this->contentTemplate->tplReplaceOnce($sensor.'Outlier', $outlierTemplate->getTpl());
            }
        }
        // delete the last row
        $this->contentTemplate->cleanCode('tableRow');
    }
}
?>