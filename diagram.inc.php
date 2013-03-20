<?php
class Diagram extends DataVisualisation {
    private $standardSensor = 'co';
    
    public function __construct($contentTemplate, $language) {
        parent::__construct($contentTemplate, $language);
        
        $this->outlierInterpolation();
        if ( $this->cosmSuccess ) {
            $this->fillDiagram();
        }
    }
    
    private function outlierInterpolation() {
        $tplOutliers = '';
        $css_checked = ' checked="checked"';
        
        // check if outliers shall be interpolated
        if ( isset($_GET['interpolateoutliers']) && $_GET['interpolateoutliers'] == 'true' ) {
            $this->interpolateOutliers = true;
            
            $replaceArray = array('io_checked' => $css_checked,
                'dio_checked' => '',
                'interpolateOutliers' => 'true');
            foreach ( $this->sensors as $sensor ) {
                $replaceArray[$sensor.'OutlierTable'] = '';
            }
            $this->contentTemplate->tplMultipleReplace($replaceArray);
            
            if ( $this->cosmSuccess && $this->dataValidation->containsOutliers($this->outliers) && $this->sensitivity != 0 ) {
                $tplOutliers = '<a href="index.php?s=diagram&amp;fid='.$this->feedId.'&amp;timeframe='.$this->timeframe.'&amp;interpolateoutliers=false&amp;sensitivity='.$this->sensitivity.'&amp;lang='.$this->language.'"><span class="bigoutlier interpolated success" onMouseOver="outlierNote(\'outliers_interpolated\');" onMouseOut="outlierNote(\'outliers_interpolated\');">i</span></a><div id="outliers_interpolated" class="bigoutlierhint interpolated">'.translate('outliers_interpolated_diagram').'</div>';
                
                // interpolate outliers
                $this->dataArray = $this->dataValidation->interpolateOutliers($this->outliers);
            }
        }
        else {
            $this->interpolateOutliers = false;
            
            $replaceArray = array('io_checked' => '',
                'dio_checked' => $css_checked,
                'interpolateOutliers' => 'false');
            
            if ( $this->sensitivity != 0 ) {
                foreach ( $this->outliers as $sensor => $val ) {
                    $i = 0;
                    $outlierTable[$sensor] = '';
                    foreach ( $val as $time => $out ) {
                        if ( $out == 1 ) {
                            $outlierTable[$sensor] .= '{column:1, row:'.$i.'}, ';
                        }
                        $i++;
                    }
                    $outlierTable[$sensor] = substr($outlierTable[$sensor], 0, -2);
                    $this->contentTemplate->tplReplace($sensor.'OutlierTable', $outlierTable[$sensor]);
                }
            }
            else {
                foreach ( $this->sensors as $sensor ) {
                    $replaceArray[$sensor.'OutlierTable'] = '';
                }
            }
            
            $this->contentTemplate->tplMultipleReplace($replaceArray);
            
            // check if dataset contains outliers and outlier detection is on
            if ( $this->cosmSuccess && $this->dataValidation->containsOutliers($this->outliers) && $this->sensitivity != 0 ) {
                $tplOutliers = '<a href="index.php?s=diagram&amp;fid='.$this->feedId.'&amp;timeframe='.$this->timeframe.'&amp;interpolateoutliers=true&amp;sensitivity='.$this->sensitivity.'&amp;lang='.$this->language.'"><span class="bigoutlier error" onMouseOver="outlierNote(\'outliers_found\');" onMouseOut="outlierNote(\'outliers_found\');">!</span></a><div id="outliers_found" class="bigoutlierhint">'.translate('outliers_found_diagram').'</div>';
            }
        }
        $this->contentTemplate->tplReplace('outliers', $tplOutliers);
    }
    
    private function fillDiagram() {
        // iterate sensor data
        $i = 1;
        foreach ( $this->dataArray as $time => $val ) {
            // copy data table row and fill in sensor data for one timestamp
            $this->contentTemplate->copyCode('diagramData');
            
            // replace date and time
            $this->contentTemplate->tplReplaceOnce('t', date('Y, m-1, d, H, i', $time));
            
            // iterate sensors
            foreach ( $this->sensors as $sensor ) {
                // if there is no data, set it to null
                if ( ! isset($val[$sensor]) ) { $val[$sensor] = 'null'; }
                $this->contentTemplate->tplReplaceOnce($sensor, $val[$sensor]);
                $this->contentTemplate->tplReplaceOnce('lt', date(translate('php_time_format'), $time));
                $this->contentTemplate->tplReplaceOnce($sensor, $val[$sensor]);
                
                if ( $this->outliers[$sensor][$time] && ! $this->interpolateOutliers && $this->sensitivity != 0 ) {
                    $outlierText = 'outlierText';
                }
                else {
                    $outlierText = 'noText';
                }
                
                $this->contentTemplate->tplReplaceOnce($sensor.'_outlier_text', $outlierText);
            }
            
            $this->contentTemplate->tplReplaceOnce('timestamp', $time);
            
            // check if it's the last entry, if true, delete the last comma from at the end of the data table row
            if ( count($this->dataArray) == $i ) {
                $this->contentTemplate->tplReplaceOnce(',', '');
            }
            else {
                $this->contentTemplate->tplReplaceOnce(',', ',');
            }
            $i++;
        }
        
        // delete the last row
        $this->contentTemplate->cleanCode('diagramData');
    }
}
?>