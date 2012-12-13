<?php
	$errormessage = "";
	$successmessage = "";
	$feedid = "";
	$pw = "";

	if ( isset($_GET['send'])) {
		$db = new Sql();
		$this->send = mysql_real_escape_string($_GET['send']);
		// first step: check data, verify volition to delete the AQE from our database
		if ($this->send == "one") {
			// check if fields are filled
			if ( isset($_POST['feedid']) && isset($_POST['pw']) ) {
				$fid = intval($_POST['feedid']);
				// check if the feed ID is valid
				if ($fid==0) {
					$errormessage = "<span class=\"error\">Bitte eine g&uuml;ltige feed ID eingeben!</span>";
				}
				else {
					$num_rows = $db->num_rows("SELECT * FROM `aeolus`.`egg` WHERE `feed_id`=".$fid);
					// check if the given AQE is registered
					if ($num_rows==0) {
						$errormessage = "<span class=\"error\">Dieses Air Quality Egg ist bei uns nicht registriert!</span>";
					}
					else {
						$pw = sha1($_POST['pw']);
						$num_rows2 = $db->num_rows("SELECT * FROM `aeolus`.`egg` WHERE `feed_id`=".$fid." AND `password`='".$pw."'");
						// check if password is valid
						if ($num_rows2 == 0) {
							$errormessage = "<span class=\"error\">Das angegebene L&ouml;schpasswort ist nicht korrekt!</span>";
						}
						else {
							$successmessage = "<p><span class=\"success\">Die angegebenen Daten sind korrekt, wollen Sie Ihr Air Quality Egg wirklich aus unserer Datenbank l&ouml;schen?</span><br>
												<a href=\"index.php?s=delete&amp;send=two&amp;fid=".$fid."\" id=\"del_true_button\">Ja, bitte!</a></p>";
						}
					}
				}
			}
		}
		// second step: delete AQE from our database
		else if ($this->send == "two") {
			if ( isset($_GET['fid']) ) {
				$fid = intval($_GET['fid']);
				if ($fid == 0) {
					$errormessage = "<span class=\"error\">Mit Links spielt man nicht!</span>";
				}
				else {
					$db->query("DELETE FROM `aeolus`.`egg` WHERE `feed_id`=".$fid);
					$successmessage = "<span class=\"success\">Ihr Air Quality Egg wurde erfolgreich aus unserer Datenbank entfernt!</span>";
				}
			}
		}
		else {
			$errormessage = "<span class=\"error\">Mit Links spielt man nicht!</span>";
		}
	}
	$this->contentTemplate->tplReplace('del_error', $errormessage);
	$this->contentTemplate->tplReplace('del_success', $successmessage);
?>