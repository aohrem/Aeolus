<?php
	$errormessage = '';
	$successmessage = '';
	$feedid = 'feed ID';
	$pw = 'Passwort';
	$pw_ver = 'Passwort wiederholen';
	
	// find URL of the current page
	$url = $_SERVER['REQUEST_URI'];
	$url_parts = explode('/', $url);
	$this->registerTemplate->tplReplace('url', $url_parts[sizeof($url_parts)-1]);
	
	if ( isset($_POST['reg']) ) {
		$this->reg = mysql_real_escape_string($_POST['reg']);
		$this->mainTemplate->tplReplace('reg_handle', ' class="'.$this->reg.'"');
        
		if ($this->reg == 'true') {
			if (isset($_POST['feedid']) && isset($_POST['password']) && isset($_POST['password_verify'])) {
				$fid = intval($_POST['feedid']);
                
				if ($fid==0) {
					$errormessage = '<span class="error">'.$GLOBALS['translation']['enter_valid_feed_id'].'</span>';
				}
				else if (($_POST['password'] == '' || $_POST['password'] == 'Passwort') || ($_POST['password_verify'] == '' || $_POST['password_verify'] == 'Passwort wiederholen')) {
					$errormessage = '<span class="error">'.$GLOBALS['translation']['fill_in_all_inputs'].'</span>';
				}
				else if ($_POST['password'] != $_POST['password_verify']) {
					$errormessage = '<span class="error">'.$GLOBALS['translation']['passwords_incorrect'].'</span>';
				}
				else {
					$cosmAPI = new CosmAPI();
					$cosmAPI->getEggCoordinates($fid);
					
					$password = sha1($_POST['password']);
					
					$db = new Sql();
					$num_rows = $db->num_rows('SELECT `feed_id` FROM `aeolus`.`egg` WHERE `feed_id`='.$fid);
                    
					if ($num_rows != 0) {
						$errormessage = '<span class="error">'.$GLOBALS['translation']['aqe_already_registered'].'</span>';
					}
					else {
						$db->query('INSERT INTO `aeolus`.`egg` (`feed_id`,`password`) VALUES ('.$fid.', \''.$password.'\')');
						$successmessage = '<span class="success">'.$GLOBALS['translation']['aqe_registered'].'</span>';
					}
				}
			}
			$this->registerTemplate->tplReplace('errormessage', $errormessage);
			$this->registerTemplate->tplReplace('successmessage', $successmessage);
		}
	}
	else {
		$this->reg = '';
		$this->registerTemplate->tplReplace('errormessage', $errormessage);
		$this->registerTemplate->tplReplace('successmessage', $successmessage);
		$this->registerTemplate->tplReplace('reg_handle', '');
	}
?>