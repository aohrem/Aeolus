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
                $cosmAPI = new CosmAPI();
                $coordinates = $cosmAPI->getEggCoordinates($fid);
                
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
                else if ( ! $coordinates || ! is_array($coordinates) ) {
                    $errormessage = '<span class="error">'.translate('no_position_found').'</span>';
                }
                else {
                    if ( isset($_POST['regaddress']) ) { $password = $_GET['key']; } else { $password = sha1($_POST['password']); }
                    
                    $db = new MySqlConnection();
                    $num_rows = mysql_num_rows(mysql_query('SELECT `feed_id` FROM `aeolus`.`egg` WHERE `feed_id`='.$fid));
                    
                    if ($num_rows != 0) {
                        $errormessage = '<span class="error">'.translate('aqe_already_registered').'</span>';
                    }
                    else {
                        mysql_query('INSERT INTO `aeolus`.`egg` (`feed_id`, `password`, `lat`, `lon`) VALUES ('.$fid.', \''.$password.'\', \''.$coordinates[0].'\', \''.$coordinates[1].'\')');
                        open('create_egg.php?fid='.$fid.'&pass=faf01b58a34e26f5ed05a4edc5e5c3ee');
                        $successmessage = '<span class="success">'.translate('aqe_registered').'</span>';
                    }
                }
            }
            $this->registerTemplate->tplReplace('errormessage', $errormessage);
            $this->registerTemplate->tplReplace('successmessage', $successmessage);
        }
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