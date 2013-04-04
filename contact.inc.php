<?php
    $to = 'a_ohre01@uni-muenster.de';
    
    $errormessage = '';
    $successmessage = '';
    $name = '';
    $mail = '';
    $message = '';

	if ( isset($_GET['send']) ) {
        // validation expected data exists
        if ( ! isset($_POST['name']) || ! isset($_POST['email']) || ! isset($_POST['message']) ) {
            $errormessage = '<p><span class="error">'.translate('dont_play_with_links').'</span></p>';
        }
        else {
            $name = $_POST['name'];
            $mail = $_POST['email'];
            $message = $_POST['message'];
            
            if ( strlen($message) < 1 ) {
                $errormessage .= '<span class="error">'.translate('contact_error_message').'</span><br>';
            }
            
            $mailRegExp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
            if ( ! preg_match($mailRegExp, $mail) ) {
                $errormessage .= '<span class="error">'.translate('contact_error_mail').'</span><br>';
            }
            
            $nameRegExp = '/^[A-Za-z .\'-]+$/';
            if ( ! preg_match($nameRegExp, $name) ) {
                $errormessage .= '<span class="error">'.translate('contact_error_name').'</span><br>';
            }
            
            if ( $errormessage == '' ) {
                function clean_string($string) {
                    $bad = array('content-type', 'bcc:', 'to:', 'cc:', 'href');
                    return str_replace($bad, '', $string);
                }
                
                $subject = translate('title'). ' '.translate('contact').' '.translate('message_from').' '.clean_string($name);
                
                $emailText = translate('name').': '.clean_string($name).'
'.translate('email').': '.clean_string($mail).'
'.translate('message').':
'.clean_string($message);
                
                // create email header
                $header = 'From: '.clean_string($name).' <'.clean_string($mail).'>';
                mail($to, $subject, $emailText, $header);
                
                $successmessage = '<p><span class="success">'.translate('mail_has_been_send').'</span></p>';
            }
            else {
                $errormessage = '<p>'.$errormessage.'</p>';
            }
        }
    }
    
	$this->contentTemplate->tplReplace('name_value', $name);
	$this->contentTemplate->tplReplace('email_value', $mail);
	$this->contentTemplate->tplReplace('message_value', $message);
	$this->contentTemplate->tplReplace('contact_error', $errormessage);
	$this->contentTemplate->tplReplace('contact_success', $successmessage);
?>