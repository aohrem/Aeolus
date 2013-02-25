<?php
if(isset($_POST['email'])) {
     
    // EDIT THE 2 LINES BELOW AS REQUIRED
    $email_to = "s_elka01@uni-muenster.de";
    $email_subject = "Your email subject line";
     
     
    function died($error) {
        // your error code can go here
        echo "Die Mitteilung konnte nicht verschickt werden, weil Sie das Kontaktformular nicht vollst&auml;ndig oder nicht korrekt ausgef&uuml;llt haben. ";
        echo "Folgende Eingaben enthielten Fehler:<br /><br />";
        echo $error."<br /><br />";
        echo "Bitte gehen Sie zur&uuml;ck und f&uuml;llen Sie diese Felder aus.<br /><br />";
        die();
    }
     
    // validation expected data exists
    if(!isset($_POST['first_name']) ||
        !isset($_POST['last_name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['message'])) {
        died('Die Mitteilung konnte nicht verschickt werden, weil Sie das Kontaktformular nicht vollst&auml;ndig oder korrekt ausgef&uuml;llt haben.');      
    }
     
    $first_name = $_POST['first_name']; // required
    $last_name = $_POST['last_name']; // required
    $email_from = $_POST['email']; // required
    $message = $_POST['message']; // required
     
    $error_message = "";
    $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
  if(!preg_match($email_exp,$email_from)) {
    $error_message .= 'Die von Ihnen eingegebene Email-Adresse ist nicht g&uuml;ltig.<br />';
  }
    $string_exp = "/^[A-Za-z .'-]+$/";
  if(!preg_match($string_exp,$first_name)) {
    $error_message .= 'Der von Ihnen eingegebene Vorname ist nicht g&uuml;ltig.<br />';
  }
  if(!preg_match($string_exp,$last_name)) {
    $error_message .= 'Der von Ihnen eingegebene Nachname ist nicht g&uuml;ltig.<br />';
  }
  if(strlen($message) < 1) {
    $error_message .= 'Sie m&uuml;ssen Ihre Mittteilung eingeben.<br />';
  }
  if(strlen($error_message) > 0) {
    died($error_message);
  }
    $email_message = "Form details below.\n\n";
     
    function clean_string($string) {
      $bad = array("content-type","bcc:","to:","cc:","href");
      return str_replace($bad,"",$string);
    }
     
    $email_message .= "Vorname: ".clean_string($first_name)."\n";
    $email_message .= "Nachname: ".clean_string($last_name)."\n";
    $email_message .= "E-Mail-Adresse: ".clean_string($email_from)."\n";
    $email_message .= "Mitteilung: ".clean_string($message)."\n";
     
     
// create email headers
$headers = 'From: '.$email_from."\r\n".
'Reply-To: '.$email_from."\r\n" .
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers); 
?>
 
<!-- include your own success html here -->
 
Vielen Dank f&uuml;r Ihre Mitteilung! Wir werden uns baldm&ouml;glichst bei Ihnen melden.
 
<?php
}
?>