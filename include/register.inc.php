<?php
	$errormessage = '';
	$successmessage = '';
    
    $this->registerTemplate = new Template();
    $this->registerTemplate->readTpl('register');
    $this->registerTemplate->tplReplace('site', $this->site);
	
	// find URL of the current page
	$url = $_SERVER['REQUEST_URI'];
	$url_parts = explode('/', $url);
    $url = $url_parts[sizeof($url_parts)-1];
    $url = htmlentities($url);
	
	if ( isset($_POST['reg']) ) {
		$this->reg = mysql_real_escape_string($_POST['reg']);
		$this->mainTemplate->tplReplace('reg_handle', ' class="'.$this->reg.'"');
        
		if ($this->reg == 'true') {
			if (isset($_POST['feedid']) && isset($_POST['password']) && isset($_POST['password_verify'])) {
				$fid = intval($_POST['feedid']);
				
				if ( ! isset($_POST['reg_address']) ) {
                    $cosmAPI = new CosmAPI();
				    $coordinates = $cosmAPI->getEggCoordinates($fid);
                }
                else {
                    $nominatimAPI = new NominatimAPI();
                    $coordinates = $nominatimAPI->getCoordinates($_POST['address']);
                }
                
                // feed id is incorrect
				if ( $fid == 0 ) {
					$errormessage = '<span class="error">'.translate('enter_valid_feed_id').'</span>';
				}
                // user entered no password
                else if (($_POST['password'] == '' || $_POST['password'] == $GLOBALS['translation']['password']) || ($_POST['password_verify'] == '' || $_POST['password_verify'] == $GLOBALS['translation']['repeat_password'])) {
					$errormessage = '<span class="error">'.translate('fill_in_all_inputs').'</span>';
				}
                // password verification incorrect
				else if ($_POST['password'] != $_POST['password_verify']) {
					$errormessage = '<span class="error">'.translate('passwords_incorrect').'</span>';
				}
                // no coordinates found, user has to enter an adress
                else if ( ! $coordinates || ! is_array($coordinates) ) {
                    if ( ! strpos($url, '?') ) { $url .= '?'; } else { $url .= '&'; }
                    
                    header('Location: '.$url.'regadress=true&feedid='.$fid.'&key='.sha1($_POST['password']));
				}
				else {
					if ( isset($_POST['reg_address']) ) { $password = $_POST['password']; } else { $password = sha1($_POST['password']); }
					
					$db = new MySqlConnection();
					$num_rows = mysql_num_rows(mysql_query('SELECT `feed_id` FROM `aeolus`.`egg` WHERE `feed_id`='.$fid));
                    
					if ($num_rows != 0) {
						$errormessage = '<span class="error">'.translate('aqe_already_registered').'</span>';
					}
					else {
						mysql_query('INSERT INTO `aeolus`.`egg` (`feed_id`, `password`, `lat`, `lon`) VALUES ('.$fid.', \''.$password.'\', \''.$coordinates[0].'\', \''.$coordinates[1].'\')');
                        open('create_egg.php?fid='.$fid);
						$successmessage = '<span class="success">'.translate('aqe_registered').'</span>';
					}
				}
			}
			$this->registerTemplate->tplReplace('errormessage', $errormessage);
			$this->registerTemplate->tplReplace('successmessage', $successmessage);
		}
	}
    else if ( isset($_GET['regadress']) ) {
        $this->reg = $_GET['regadress'];
		$this->mainTemplate->tplReplace('reg_handle', ' class="'.$this->reg.'"');
        
        $feedid = $_GET['feedid'];
        $password = $_GET['key'];
        
        $url = str_replace('regadress=true&amp;feedid='.$feedid.'&amp;key='.$password, '', $url);
        
        $this->registerTemplate->readTpl('register_position');
        $this->registerTemplate->tplReplace('reg_feedid', $feedid);
        $this->registerTemplate->tplReplace('reg_password', $password);
        $this->registerTemplate->tplReplace('reg_password_verify', $password);
    }
	else {
		$this->reg = false;
		$this->registerTemplate->tplReplace('errormessage', $errormessage);
		$this->registerTemplate->tplReplace('successmessage', $successmessage);
		$this->mainTemplate->tplReplace('reg_handle', '');
	}
    
	$this->registerTemplate->tplReplace('url', $url);
    $this->mainTemplate->tplReplace('register', $this->registerTemplate->getTpl());
?>