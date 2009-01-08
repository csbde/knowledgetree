<?php
/**
 * $Id$
 *
 * Contains static functions concerned with sending emails.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
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
