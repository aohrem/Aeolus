<?php
	function timeframe() {
		$timeframes = array('6h', '24h', '48h', '1w', '1m', '3m');
		$standardTimeframe = '6h';
		
		// get the timeframe to show and set a standard value if not available
		if ( isset($_GET['timeframe']) ) {
			$timeframe = htmlentities(mysql_real_escape_string($_GET['timeframe']));
			
			// iterate timeframes to check if given timeframe is valid
			$timeframeValid = false;
			foreach ( $timeframes as $val ) {
				if ( $timeframe == $val ) {
					$timeframeValid = true;
					break;
				}
			}
			
			// set standard timeframe if given timeframe is not valid
			if ( ! $timeframeValid ) {
				$timeframe = $standardTimeframe;
			}
		}
		else {
			$timeframe = $standardTimeframe;
		}
		
		return $timeframe;		
	}
	
	function tplTimeframe($template, $timeframe) {
		$timeframes = array('6h', '24h', '48h', '1w', '1m', '3m');
		// replace timeframe
		$template->tplReplace('time', $timeframe);
		
		$cssActive = ' class="active"';
		
		// mark active timeframe tabs
		foreach ( $timeframes as $val ) {
			if ( $val == $timeframe ) {
				$template->tplReplace($val.'_active', $cssActive);
			}
			else {
				$template->tplReplace($val.'_active', '');
			}
		}
		
		return $template;
	}
?>