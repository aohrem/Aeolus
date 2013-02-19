<?php
	$errormessage = '';
	$successmessage = '';
	$feedid = '';
	$pw = '';

	if ( isset($_GET['send']) ) {
		$db = new Sql();
		$this->send = mysql_real_escape_string($_GET['send']);
		// first step: check data, verify volition to delete the AQE from our database
		if ($this->send == 'one') {
			// check if fields are filled
			if ( isset($_POST['feedid']) && isset($_POST['pw']) ) {
				$fid = intval($_POST['feedid']);
				// check if the feed ID is valid
				if ( $fid == 0 ) {
					$errormessage = '<span class="error">'.$GLOBALS['translation']['enter_valid_feed_id'].'</span>';
				}
				else {
					$num_rows = $db->num_rows('SELECT * FROM `aeolus`.`egg` WHERE `feed_id`='.$fid);
					// check if the given AQE is registered
					if ( $num_rows == 0 ) {
						$errormessage = '<span class="error">'.$GLOBALS['translation']['aqe_not_registered'].'</span>';
					}
					else {
						$pw = sha1($_POST['pw']);
						$num_rows2 = $db->num_rows('SELECT * FROM `aeolus`.`egg` WHERE `feed_id`='.$fid.' AND `password`=\''.$pw.'\'');
						// check if password is valid
						if ( $num_rows2 == 0 ) {
							$errormessage = '<span class="error">'.$GLOBALS['translation']['wrong_delete_password'].'</span>';
						}
						else {
							$successmessage = '<p><span class="success">'.$GLOBALS['translation']['aqe_delete_data_correct'].'</span><br>
												<a href="index.php?s=delete&amp;send=two&amp;fid='.$fid.'" id="del_true_button">'.$GLOBALS['translation']['yes_please'].'</a></p>';
						}
					}
				}
			}
		}
		// second step: delete AQE from our database
		else if ( $this->send == 'two' ) {
			if ( isset($_GET['fid']) ) {
				$fid = intval($_GET['fid']);
				if ($fid == 0) {
					$errormessage = '<span class="error">'.$GLOBALS['translation']['dont_play_with_links'].'</span>';
				}
				else {
					$db->query('DELETE FROM `aeolus`.`egg` WHERE `feed_id`='.$fid);
					$successmessage = '<span class="success">'.$GLOBALS['translation']['aqe_deleted'].'</span>';
				}
			}
		}
		else {
			$errormessage = '<span class="error">'.$GLOBALS['translation']['dont_play_with_links'].'</span>';
		}
	}
    
	$this->contentTemplate->tplReplace('del_error', $errormessage);
	$this->contentTemplate->tplReplace('del_success', $successmessage);
?>