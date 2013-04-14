<?php
    // this file handles the register popup
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
                
                // check if feed id is incorrect
                if ( $fid == 0 ) {
                    $errormessage = '<span class="error">'.translate('enter_valid_feed_id').'</span>';
                }
                // check if user entered no password
                else if (($_POST['password'] == '' || $_POST['password'] == $GLOBALS['translation']['password']) || ($_POST['password_verify'] == '' || $_POST['password_verify'] == $GLOBALS['translation']['repeat_password'])) {
                    $errormessage = '<span class="error">'.translate('fill_in_all_inputs').'</span>';
                }
                // check if password verification incorrect
                else if ($_POST['password'] != $_POST['password_verify']) {
                    $errormessage = '<span class="error">'.translate('passwords_incorrect').'</span>';
                }
                // check if coordinates in the feed were found
                else if ( ! $coordinates || ! is_array($coordinates) ) {
                    $errormessage = '<span class="error">'.translate('no_position_found').'</span>';
                }
                // register the egg
                else {
                    if ( isset($_POST['regaddress']) ) { $password = $_GET['key']; } else { $password = sha1($_POST['password']); }
                    
                    $db = new MySqlConnection();
                    $num_rows = mysql_num_rows(mysql_query('SELECT `feed_id` FROM `aeolus`.`egg` WHERE `feed_id`='.$fid));
                    
                    // check if there already is one entry with this feed id in the database
                    if ($num_rows != 0) {
                        $errormessage = '<span class="error">'.translate('aqe_already_registered').'</span>';
                    }
                    // write an entry for the egg into the database
                    else {
                        mysql_query('INSERT INTO `aeolus`.`egg` (`feed_id`, `password`, `lat`, `lon`) VALUES ('.$fid.', \''.$password.'\', \''.$coordinates[0].'\', \''.$coordinates[1].'\')');
                        
                        // and call the create_egg.php to get first sensor values for the egg
                        open('create_egg.php?fid='.$fid.'&pass=faf01b58a34e26f5ed05a4edc5e5c3ee');
                        $successmessage = '<span class="success">'.translate('aqe_registered').'</span>';
                    }
                }
            }
            
            // replace error and success messages
            $this->registerTemplate->tplReplace('errormessage', $errormessage);
            $this->registerTemplate->tplReplace('successmessage', $successmessage);
        }
    }
    // registration failed
    else {
        // replace error and success messages
        $this->reg = false;
        $this->registerTemplate->tplReplace('errormessage', $errormessage);
        $this->registerTemplate->tplReplace('successmessage', $successmessage);
        $this->mainTemplate->tplReplace('reg_handle', '');
    }
    
    // replace the register template into the main template
    $this->registerTemplate->tplReplace('url', $url);
    $this->mainTemplate->tplReplace('register', $this->registerTemplate->getTpl());
?>