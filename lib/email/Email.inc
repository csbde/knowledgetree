<?php
/**
 * $Id$
 *
 * Contains static functions concerned with sending emails.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.email 
 */
class Email {

    /**
     * The underlying emailer class
     */
    var $oMailer;

    /**
     * Creates an email class, initialising the underlying mailer class
     * with default system information
     *
     * @param string the sender's email address (optional)
     * @param string the sender's name (optional)     
     */
    function Email($sFromEmail = "", $sFromName = "") {
        global $default;
        // create a new phpmailer object.
        require_once(KT_DIR .  '/thirdparty/phpmailer/class.phpmailer.php');
        $this->oMailer = new phpmailer();
	$this->oMailer->Encoding = 'base64';
	$this->oMailer->CharSet = 'utf-8';
        $this->oMailer->isSMTP();
        $this->oMailer->Host = $default->emailServer;
        $this->oMailer->From = (strlen($sFromEmail) == 0) ? $default->emailFrom : $sFromEmail;
        $this->oMailer->FromName = (strlen($sFromName) == 0) ? $default->emailFromName : $sFromName;
        $this->oMailer->WordWrap = 100;
        $this->oMailer->IsHTML(true);
        $this->oMailer->SetLanguage('en', KT_DIR .  '/thirdparty/phpmailer/language/');
        $this->bEmailDisabled = false;
        
	$oConfig =& KTConfig::getSingleton();        
	$sEmailServer = $oConfig->get('email/emailServer');
	if ($sEmailServer == 'none') {
            $this->bEmailDisabled = true;
        }
        if (empty($sEmailServer)) {
            $this->bEmailDisabled = true;
        }

	$sEmailPort = $oConfig->get('email/emailPort');
	if(!empty($sEmailPort)) {
	    $this->oMailer->Port = (int) $sEmailPort;
	}

	$bEmailAuth = $oConfig->get('email/emailAuthentication');
	if($bEmailAuth) {
	    $sEmailUser = $oConfig->get('email/emailUsername');
	    $sEmailPass = $oConfig->get('email/emailPassword');
	    $this->oMailer->SMTPAuth = true;
	    $this->oMailer->Username = $sEmailUser;
	    $this->oMailer->Password = $sEmailPass;
	}
    }
	
    /**
     * Sends an email to a specified recipient.
     *
     * @param string the recipients email address
     * @param string the subject of the email
     * @param string the body of the email
     * @param string the sender's email address (optional)
     * @param string the sender's name (optional)     
     * @return boolean true on email successfully sent, false otherwise and set $_SESSION["errorMessage"]
     */
    function send($mToEmail, $sSubject, $sBody) {
        if ($this->bEmailDisabled) {
        	return PEAR::raiseError(_kt("Email is not configured."));
        }
        global $default;
        
        if ( (is_string($mToEmail) && (strlen($mToEmail) > 0)) || 
        	 (is_array($mToEmail) && (count($mToEmail) > 0)) ) {        

	        $aEmailAddresses = array();
	        // just one email address, add it
	        if (is_string($mToEmail) && (strpos($mToEmail, ";") === false)) {
	            $this->oMailer->AddAddress($mToEmail);
	        } else {
	        	// if we're passed an array, then use it
	        	if (is_array($mToEmail)) {
	        		$aEmailAddresses = $mToEmail;
	        	// if there are multiple addresses (; separated), explode it	        	
	        	} elseif (strpos($mToEmail, ";") > 0) {
		            $aEmailAddresses = explode(";", $mToEmail);
	        	}
	            for ($i=0; $i<count($aEmailAddresses); $i++) {
	                $this->oMailer->AddAddress($aEmailAddresses[$i]);
	                $default->log->debug("Email.inc adding " . $aEmailAddresses[$i]);
	            }
	        }

	        $this->oMailer->Subject = stripslashes($sSubject);
	        $this->oMailer->Body = stripslashes($sBody);
	
	        //send the email
	        if(!$this->oMailer->Send()) {
	            $default->log->error("Error sending mail to $mToEmail; mailer error code=" . $this->oMailer->ErrorInfo);
	            return PEAR::raiseError(sprintf(_kt("Error sending mail to %s; mailer error code=%s"),$mToEmail,  $this->oMailer->ErrorInfo));
	        } else {
	        	$default->log->info("Successfully sent mail to $mToEmail");
	        }
	        return true;
        } else {
        	// no valid email addresses supplied
        	return PEAR::raiseError(_kt("No valid email addresses supplied"));
        }
    }

    /**
     * Sends an email to a specified recipient.
     *
     * @param string the recipients email address
     * @param string the subject of the email
     * @param string the body of the email
     * @param string the path to the document to attach
     * @param string the name to the document to attach
     * @return boolean true on email successfully sent, false otherwise and set $_SESSION["errorMessage"]
     */
    function sendAttachment($mToEmail, $sSubject, $sBody, $sDocumentPath, $sDocumentName) {
        if ($this->bEmailDisabled) {
        	return PEAR::raiseError(_kt("Email is not configured."));
        }
        global $default;
        
        if ( (is_string($mToEmail) && (strlen($mToEmail) > 0)) || 
        	 (is_array($mToEmail) && (count($mToEmail) > 0)) ) {        

	        // just one email address, add it
	        if (is_string($mToEmail) && (strpos($mToEmail, ";") === false)) {
	            $this->oMailer->AddAddress($mToEmail);
	        } else {
                $aEmailAddresses = array();
	        	// if we're passed an array, then use it
	        	if (is_array($mToEmail)) {
	        		$aEmailAddresses = $mToEmail;
	        	// if there are multiple addresses (; separated), explode it	        	
	        	} elseif (strpos($mToEmail, ";") > 0) {
		            $aEmailAddresses = explode(";", $mToEmail);
	        	}
	            for ($i=0; $i<count($aEmailAddresses); $i++) {
	                $this->oMailer->AddAddress($aEmailAddresses[$i]);
	                $default->log->debug("Email.inc adding " . $aEmailAddresses[$i]);
	            }
	        }

	        $this->oMailer->Subject = stripslashes($sSubject);
	        $this->oMailer->Body = stripslashes($sBody);
	        $this->oMailer->AddAttachment($sDocumentPath, $sDocumentName);
	
	        //send the email
	        if(!$this->oMailer->Send()) {
	            $default->log->error("Error sending mail to $mToEmail; mailer error code=" . $this->oMailer->ErrorInfo);
	            return PEAR::raiseError(sprintf(_kt("Error sending mail to %s; mailer error code=%s"), $mToEmail,  $this->oMailer->ErrorInfo));
	        } else {
	        	$default->log->info("Successfully sent mail to $mToEmail");
	        }
	        return true;
        } else {
        	// no valid email addresses supplied
        	return PEAR::raiseError(_kt("No valid email addresses supplied"));
        }
    }

    /**
    * Sends an email containing a hyperlink to a specified recipient
    *
    * @param  The sender's email address
    * @param  The sender's Name
    * @param  The recipients email address
    * @param  The subject heading for the email
    * @param  The Body of the email
    * @param  The hyperlink that should be sent
    *
    * @return boolean true on email successfully sent, false otherwise and set $_SESSION["errorMessage"]
    * 
    * @todo check for special characters (including encoding the link correctly???)
    * @todo need to test this on multiple mail clients, not just Outlook
    */
    function sendHyperLink($FromEmail, $FromName, $ToEmail, $Subj, $EmailBody, $hyperlink) {
        if ($this->bEmailDisabled) {
        	return PEAR::raiseError(_kt("Email is not configured."));
        }
        global $default;

        //get info from relevant fields.
        $this->oMailer->From = $FromEmail;
        $this->oMailer->FromName = $FromName;
        $this->oMailer->AddAddress($ToEmail);
        $this->oMailer->Subject = stripslashes($Subj);
        $this->oMailer->Body = stripslashes($EmailBody) . ' ' . $hyperlink;

        //send the email
        if(!$this->oMailer->Send()) {
            $_SESSION["errorMessage"] = $lang_err_email . " " . $this->oMailer->ErrorInfo;
            return false;
        }
        return true;
    }
    /**
    * Sends an email ment for administration, 
    *
    * @param  The sender's email address
    * @param  The sender's Name
    * @param  The recipients email address
    * @param  The subject heading for the email
    * @param  The Body of the email
    * @param  The hyperlink that should be sent
    *
    * @return boolean true on email successfully sent, false otherwise and set $_SESSION["errorMessage"]
    * 
    * @todo check for special characters (including encoding the link correctly???)
    * @todo need to test this on multiple mail clients, not just Outlook
    */
    function sendHelpEmail($FromEmail, $FromName, $ToEmail, $Subj, $EmailBody, $hyperlink) {
        if ($this->bEmailDisabled) {
        	return PEAR::raiseError(_kt("Email is not configured."));
        }
        global $default;

        //get info from relevant fields.
        $this->oMailer->From = $FromEmail;
        $this->oMailer->FromName = $FromName;
        $this->oMailer->AddAddress($ToEmail);
        $this->oMailer->Subject = stripslashes($Subj)  . ' ' . $hyperlink; //only difference from above
        $this->oMailer->Body = stripslashes($EmailBody) . " <br>" . _kt("This bug can be found on this page") . ": " . "<a href = ". $hyperlink .">". $hyperlink ."</a>";

        //send the email
        if(!$this->oMailer->Send()) {
            $_SESSION["errorMessage"] = $lang_err_email . " " . $this->oMailer->ErrorInfo;
            return false;
        }
        return true;
    }

    function sendEmail($FromEmail, $FromName, $ToEmail, $Subj, $EmailBody) {
        if ($this->bEmailDisabled) {
        	return PEAR::raiseError(_kt("Email is not configured."));
        }
        global $default;

        //get info from relevant fields.
        $this->oMailer->From = $FromEmail;
        $this->oMailer->FromName = $FromName;
        $this->oMailer->AddAddress($ToEmail);
        $this->oMailer->Subject = stripslashes($Subj);
        $this->oMailer->Body = stripslashes($EmailBody);

        //send the email
        if(!$this->oMailer->Send()) {
            $_SESSION["errorMessage"] = $lang_err_email . " " . $this->oMailer->ErrorInfo;
            return false;
        }
        return true;
    }
}

?>
