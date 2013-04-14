<?php
    // replace this e-mail address with your's
    $to = 'andreas.ohrem@uni-muenster.de';
    
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
            
            // check if message is valid (at least one character)
            if ( strlen($message) < 1 ) {
                $errormessage .= translate('contact_error_message').'<br>';
            }
            
            // check if e-mail address is valid
            $mailRegExp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
            if ( ! preg_match($mailRegExp, $mail) ) {
                $errormessage .= translate('contact_error_mail').'<br>';
            }
            
            // check if name is valid
            $nameRegExp = '/^[A-Za-z .\'-]+$/';
            if ( ! preg_match($nameRegExp, $name) ) {
                $errormessage .= translate('contact_error_name').'<br>';
            }
            
            // if no errors occured, send the e-mail
            if ( $errormessage == '' ) {
                // function for cleaning the e-mail header
                function clean_string($string) {
                    $bad = array('content-type', 'bcc:', 'to:', 'cc:', 'href');
                    return str_replace($bad, '', $string);
                }
                
                // generate subject
                $subject = translate('title'). ' '.translate('contact').' '.translate('message_from').' '.clean_string($name);
                
                // generate e-mail content
                $emailText = translate('name').': '.clean_string($name).'
'.translate('email').': '.clean_string($mail).'
'.translate('message').':
'.clean_string($message);
                
                // create email header and send the mail
                $header = 'From: '.clean_string($name).' <'.clean_string($mail).'>';
                mail($to, $subject, $emailText, $header);
                
                // show success message
                $successmessage = '<p><span class="success">'.translate('mail_has_been_send').'</span></p>';
            }
            // if errors occured, show the error messages
            else {
                $errormessage = '<p><span class="error">'.$errormessage.'</span></p>';
            }
        }
    }
    
    $this->contentTemplate->tplReplace('name_value', $name);
    $this->contentTemplate->tplReplace('email_value', $mail);
    $this->contentTemplate->tplReplace('message_value', $message);
    $this->contentTemplate->tplReplace('contact_error', $errormessage);
    $this->contentTemplate->tplReplace('contact_success', $successmessage);
?>