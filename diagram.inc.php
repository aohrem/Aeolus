<?php
	$standardSensor = 'co';
	
	// get the feed id and show error if not available
	if ( isset($_GET['fid']) ) {
		$feedId = htmlentities(mysql_real_escape_string($_GET['fid']));
		
        $errormessage = '';
		
		// mark active timeframe
		$timeframe = timeframe();
		$this->contentTemplate = tplTimeframe($this->contentTemplate, $timeframe);
		
		$this->contentTemplate->tplReplace('feedId', $feedId);
        
		// cosm-API integration
		$cosmAPI = new CosmAPI();
		
		// set parameters for the cosm-API request
		$seconds = array('6h' => 21600, '24h' => 86400, '48h' => 172800, '1w' => 604800, '1m' => 2678400, '3m' => 7776000);
		$start = date('Y-m-d\TH:i:s\Z', time() - $seconds[$timeframe]);
		$end = date('Y-m-d\TH:i:s\Z', time());
		$interval = array('6h' => 60, '24h' => 300, '48h' => 600, '1w' => 10800, '1m' => 604800, '3m' => 2678400);
		$limit = 1000;
		
		// fill in the parameters to read the cosm-API
		if ( ! $dataArray = $cosmAPI->parseFeed($feedId, 'all_values', $start, $end, $limit, $interval[$timeframe], '') ) {
			$this->contentTemplate->cleanCode('tableRow');
			$errormessage = '<div class="details errormessage">'.translate('cosm_api_could_not_be_read').'</div><br>';
		}
		// check if parsing the xml was successfull
		else if ( is_array($dataArray) ) {
			// replace metadata in template
			$metadata = array('title', 'description', 'locationName', 'lat', 'lon', 'ele', 'status', 'exposure');
			foreach ( $metadata as $mdata ) {
				$this->contentTemplate->tplReplace($mdata, $dataArray[$mdata]);
				unset($dataArray[$mdata]);
			}
			
			$hidden = '';
			
			// sort sensor data by timestamp (keys of the data array)
			ksort($dataArray, SORT_NUMERIC);
			
			if ( isset($_GET['sensitivity']) && is_numeric($_GET['sensitivity']) ) {
				$sensitivity = $_GET['sensitivity'];
				if ( $sensitivity < 0 || $sensitivity > 3 ) {
					$sensitivity = 'default';
				}
			}
			else {
				$sensitivity = 'default';
			}
            
            include('datavalidation.inc.php');
            $dataValidation = new DataValidation($dataArray, array('co', 'humidity', 'no2', 'temperature'), $sensitivity, $timeframe);
            $outliers = $dataValidation->getOutliers();
			$containsOutliers = $dataValidation->containsOutliers($outliers);
			$tplOutliers = '';
			
			if ( $sensitivity == 'default' ) {
				$sensitivity = $dataValidation->getDefaultSensitivity();
			}
			
			// mark active outlier detection sensitivity as selected
			for ( $i = 0; $i < 4; $i++ ) {
				if ( $sensitivity == $i ) {
					$sel = ' selected="selected"';
				}
				else {
					$sel = '';
				}
				$this->contentTemplate->tplReplace('sensitivity_'.$i.'_selected', $sel);
			}
			$this->contentTemplate->tplReplace('sensitivity', $sensitivity);
			
            if ( $sensitivity == 0 ) {
                $this->contentTemplate->tplReplace('outlierState', translate('outlier_state_off'));
            }
            
			// check if outliers shall be interpolated
			if ( isset($_GET['interpolateoutliers']) && $_GET['interpolateoutliers'] == 'true' && $sensitivity != 0 ) {
                
				$interpolateOutliers = true;
				$this->contentTemplate->tplReplace('interpolateOutliers', 'true');
                $this->contentTemplate->tplReplace('outlierState', translate('outlier_state_interpolated_diagram'));
                $this->contentTemplate->tplReplace('coOutlierTable', '');
                $this->contentTemplate->tplReplace('no2OutlierTable', '');
                $this->contentTemplate->tplReplace('temperatureOutlierTable', '');
                $this->contentTemplate->tplReplace('humidityOutlierTable', '');
                
				$dataArray = $dataValidation->interpolateOutliers($outliers);
				
				$tplOutliers = '<a href="index.php?s=diagram&amp;fid='.$feedId.'&amp;timeframe='.$timeframe.'&amp;interpolateoutliers=false&amp;lang='.$this->language.'"><span class="bigoutlier interpolated success" onMouseOver="outlierNote(\'outliers_interpolated\');" onMouseOut="outlierNote(\'outliers_interpolated\');">i</span></a><div id="outliers_interpolated" class="bigoutlierhint interpolated">'.translate('outliers_interpolated_diagram').'</div>';
			}
			else {
				$interpolateOutliers = false;
				$this->contentTemplate->tplReplace('interpolateOutliers', 'false');
                $this->contentTemplate->tplReplace('outlierState', translate('outlier_state_marked_diagram'));
                
                if ( $sensitivity != 0 ) {
                    foreach ( $outliers as $sensor => $val ) {
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
                    $this->contentTemplate->tplReplace('coOutlierTable', '');
                    $this->contentTemplate->tplReplace('no2OutlierTable', '');
                    $this->contentTemplate->tplReplace('temperatureOutlierTable', '');
                    $this->contentTemplate->tplReplace('humidityOutlierTable', '');
                }
                
				// check if dataset contains outliers
				if ( $containsOutliers && $sensitivity != 0 ) {
					$tplOutliers = '<a href="index.php?s=diagram&amp;fid='.$feedId.'&amp;timeframe='.$timeframe.'&amp;interpolateoutliers=true&amp;lang='.$this->language.'"><span class="bigoutlier error" onMouseOver="outlierNote(\'outliers_found\');" onMouseOut="outlierNote(\'outliers_found\');">!</span></a><div id="outliers_found" class="bigoutlierhint">'.translate('outliers_found_diagram').'</div>';
				}
			}
			
			$this->contentTemplate->tplReplace('outliers', $tplOutliers);
            
            // iterate sensor data
            $i = 1;
			foreach ( $dataArray as $time => $val ) {				
				// if there is no data, show a -
				if ( ! isset($val['co']) ) { $val['co'] = 'null'; }
				if ( ! isset($val['no2']) ) { $val['no2'] = 'null'; }
				if ( ! isset($val['humidity']) ) { $val['humidity'] = 'null'; }
				if ( ! isset($val['temperature']) ) { $val['temperature'] = 'null'; }
				
				// copy table row and fill in sensor data for one timestamp
				$this->contentTemplate->copyCode('diagramData');
                $this->contentTemplate->tplReplaceOnce('t', date('Y, m-1, d, H, i', $time));
                
                $this->contentTemplate->tplReplaceOnce('co', $val['co']);
                $this->contentTemplate->tplReplaceOnce('lt', date('d.m.Y, g:i a', $time));
                $this->contentTemplate->tplReplaceOnce('co', $val['co']);
                
                if ( $outliers['co'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
                    $coOutlierText = '<br><br>'.translate('value_could_be_an_outlier');
                }
                else {
                    $coOutlierText = '';
                }
                $this->contentTemplate->tplReplaceOnce('co_outlier_text', $coOutlierText);
                
                $this->contentTemplate->tplReplaceOnce('no2', $val['no2']);
                $this->contentTemplate->tplReplaceOnce('lt', date('d.m.Y, g:i a', $time));
                $this->contentTemplate->tplReplaceOnce('no2', $val['no2']);
                
                if ( $outliers['no2'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
                    $no2OutlierText = '<br>'.translate('value_could_be_an_outlier');
                }
                else {
                    $no2OutlierText = '';
                }
                $this->contentTemplate->tplReplaceOnce('no2_outlier_text', $no2OutlierText);
                
                $this->contentTemplate->tplReplaceOnce('temp', $val['temperature']);
                $this->contentTemplate->tplReplaceOnce('lt', date('d.m.Y, g:i a', $time));
                $this->contentTemplate->tplReplaceOnce('temp', $val['temperature']);
                
                if ( $outliers['temperature'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
                    $tempOutlierText = '<br>'.translate('value_could_be_an_outlier');
                }
                else {
                    $tempOutlierText = '';
                }
                $this->contentTemplate->tplReplaceOnce('temp_outlier_text', $tempOutlierText);
                
                $this->contentTemplate->tplReplaceOnce('hum', $val['humidity']);
                $this->contentTemplate->tplReplaceOnce('lt', date('d.m.Y, g:i a', $time));
                $this->contentTemplate->tplReplaceOnce('hum', $val['humidity']);
                
                if ( $outliers['humidity'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
                    $humOutlierText = '<br>'.translate('value_could_be_an_outlier');
                }
                else {
                    $humOutlierText = '';
                }
                $this->contentTemplate->tplReplaceOnce('hum_outlier_text', $humOutlierText);
                
                $this->contentTemplate->tplReplaceOnce('timestamp', $time);
                
                // check if it's the last entry, if true, delete the last comma from at the end of the data table row
                if ( count($dataArray) == $i ) {
                    $tpl = $this->contentTemplate->tplReplaceOnce(',', '');
                }
                else {
                    $tpl = $this->contentTemplate->tplReplaceOnce(',', ',');
                }
                $i++;
			}
			// delete the last row
			$this->contentTemplate->cleanCode('diagramData');
        }
		// handle errors if parsing the xml was not successfull
		else {
			$this->contentTemplate->cleanCode('tableRow');
			$this->contentTemplate->tplReplace('title', '');
			
			$hidden = ' class="hidden"';
            
			$errormessage = '<div class="details errormessage">'.translate('cosm_no_supported_sensor').'</div><br>';
		}
		
		// replace error message in template and hide details
		$this->contentTemplate->tplReplace('errormessage', $errormessage);
		$this->contentTemplate->tplReplace('hidden', $hidden);
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>