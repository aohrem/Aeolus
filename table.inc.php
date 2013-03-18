<?php
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
			$errormessage = '<div class="details errormessage">'.translate('cosm_api_could_not_be_read').'</div>';
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
                $this->contentTemplate->tplReplace('outlierState', translate('outlier_state_interpolated'));
				$dataArray = $dataValidation->interpolateOutliers($outliers);
				
				$tplOutliers = '<a href="index.php?s=table&amp;fid='.$feedId.'&amp;timeframe='.$timeframe.'&amp;interpolateoutliers=false&amp;lang='.$this->language.'"><span class="bigoutlier interpolated success" onMouseOver="outlierNote(\'outliers_interpolated\');" onMouseOut="outlierNote(\'outliers_interpolated\');">i</span></a><div id="outliers_interpolated" class="bigoutlierhint interpolated">'.translate('outliers_interpolated').'</div>';
			}
			else {
				$interpolateOutliers = false;
				$this->contentTemplate->tplReplace('interpolateOutliers', 'false');
                $this->contentTemplate->tplReplace('outlierState', translate('outlier_state_marked'));
				
				// check if dataset contains outliers
				if ( $containsOutliers && $sensitivity != 0 ) {
					$tplOutliers = '<a href="index.php?s=table&amp;fid='.$feedId.'&amp;timeframe='.$timeframe.'&amp;interpolateoutliers=true&amp;lang='.$this->language.'"><span class="bigoutlier error" onMouseOver="outlierNote(\'outliers_found\');" onMouseOut="outlierNote(\'outliers_found\');">!</span></a><div id="outliers_found" class="bigoutlierhint">'.translate('outliers_found').'</div>';
				}
			}
			
			$this->contentTemplate->tplReplace('outliers', $tplOutliers);
			
			function outlierReplace($template, $sensor, $text, $css_color, $css_style, $time, $hint) {
				$template->tplReplace('sensor', $sensor);
				$template->tplReplace('text', $text);
				$template->tplReplace('css_color', $css_color);
				$template->tplReplace('css_style', $css_style);
				$template->tplReplace('time', $time);
				$template->tplReplace('hint', $hint);
				return $template;
			}
			
			// iterate sensor data
			foreach ( $dataArray as $time => $val ) {				
				// if there is no data, show a -
				if ( ! isset($val['co']) ) { $val['co'] = '-'; }
				if ( ! isset($val['no2']) ) { $val['no2'] = '-'; }
				if ( ! isset($val['humidity']) ) { $val['humidity'] = '-'; }
				if ( ! isset($val['temperature']) ) { $val['temperature'] = '-'; }
				
                // mark outliers
				$outlierTemplateCo = new Template();
				if ( $outliers['co'][$time] && $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateCo->readTpl('table_outlier_box');
					$outlierTemplateCo = outlierReplace($outlierTemplateCo, 'co', 'i', 'success', 'interpolated', $time, translate('value_is_interpolated'));
				}
                else if ( $outliers['co'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateCo->readTpl('table_outlier_box');
					$outlierTemplateCo = outlierReplace($outlierTemplateCo, 'co', '!', 'error', '', $time, translate('value_could_be_an_outlier'));
                }
				
				$outlierTemplateNo2 = new Template();
				if ( $outliers['no2'][$time] && $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateNo2->readTpl('table_outlier_box');
					$outlierTemplateNo2 = outlierReplace($outlierTemplateNo2, 'no2', 'i', 'success', 'interpolated', $time, translate('value_is_interpolated'));
				}
                else if ( $outliers['no2'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateNo2->readTpl('table_outlier_box');
					$outlierTemplateNo2 = outlierReplace($outlierTemplateNo2, 'no2', '!', 'error', '', $time, translate('value_could_be_an_outlier'));
                }
				
				$outlierTemplateTemp = new Template();
				if ( $outliers['temperature'][$time] && $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateTemp->readTpl('table_outlier_box');
					$outlierTemplateTemp = outlierReplace($outlierTemplateTemp, 'no2', 'i', 'success', 'interpolated', $time, translate('value_is_interpolated'));
				}
                else if ( $outliers['temperature'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateTemp->readTpl('table_outlier_box');
					$outlierTemplateTemp = outlierReplace($outlierTemplateTemp, 'no2', '!', 'error', '', $time, translate('value_could_be_an_outlier'));
                }
				
				$outlierTemplateHum = new Template();
                // mark outliers
				if ( $outliers['humidity'][$time] && $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateHum->readTpl('table_outlier_box');
					$outlierTemplateHum = outlierReplace($outlierTemplateHum, 'hum', 'i', 'success', 'interpolated', $time, translate('value_is_interpolated'));
				}
                else if ( $outliers['humidity'][$time] && ! $interpolateOutliers && $sensitivity != 0 ) {
					$outlierTemplateHum->readTpl('table_outlier_box');
					$outlierTemplateHum = outlierReplace($outlierTemplateHum, 'hum', '!', 'error', '', $time, translate('value_could_be_an_outlier'));
                }
				
				// copy table row and fill in sensor data for one timestamp
				$this->contentTemplate->copyCode('tableRow');
				$this->contentTemplate->tplReplaceOnce('t', date('d.m.Y H:i', $time));
				$this->contentTemplate->tplReplaceOnce('co', $val['co']);
				$this->contentTemplate->tplReplaceOnce('no2', $val['no2']);
				$this->contentTemplate->tplReplaceOnce('temp', $val['temperature']);
				$this->contentTemplate->tplReplaceOnce('hum', $val['humidity']);
                
				$this->contentTemplate->tplReplaceOnce('co_outlier', $outlierTemplateCo->getTpl());
				$this->contentTemplate->tplReplaceOnce('no2_outlier', $outlierTemplateNo2->getTpl());
				$this->contentTemplate->tplReplaceOnce('temp_outlier', $outlierTemplateTemp->getTpl());
				$this->contentTemplate->tplReplaceOnce('hum_outlier', $outlierTemplateHum->getTpl());
			}
			// delete the last row
			$this->contentTemplate->cleanCode('tableRow');
		}
		// handle errors if parsing the xml was not successfull
		else {
			$this->contentTemplate->cleanCode('tableRow');
			$this->contentTemplate->tplReplace('title', '');
			
			$hidden = ' class="hidden"';
            
			$errormessage = '<div class="details errormessage">'.translate('cosm_no_supported_sensor').'</div>';
		}
		
		// replace error message in template and hide details
		$this->contentTemplate->tplReplace('errormessage', $errormessage);
		$this->contentTemplate->tplReplace('hidden', $hidden);
	}
	else {
		$this->contentTemplate->readTpl('error_feedid');
	}
?>