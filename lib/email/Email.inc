<?php

/**
* @package lib.email
*
* Class Email
*
* Contains static functions concerned with sending emails
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
*/

Class Email {

	/**
	* Sends an email containing a hyperlink to a specified recipient
	*
	* @param 	The sender's email address
	* @param 	The sender's Name
	* @param 	The recipients email address
	* @param 	The subject heading for the email
	* @param 	The Body of the email
	* @param 	The hyperlink that should be sent
	*
	* @return boolean true on email successfully sent, false otherwise and set $_SESSION["errorMessage"]
	* 
	* @todo check for special characters (including encoding the link correctly???)
	* @todo need to test this on multiple mail clients, not just Outlook
	*/
	function sendHyperLink($FromEmail, $FromName, $ToEmail, $Subject, $EmailBody, $hyperlink)
	{
		global $default;
			
		// create a new phpmailer object.
		$mail = new phpmailer();
		
		//set up info
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host = $default->owl_mail_server; // SMTP server
		
		//get info from relevant fields.
		$mail->From = $FromEmail;
		$mail->FromName = $FromName;
		$mail->AddAddress($ToEmail);
		$mail->Subject = $Subj;
		$mail->Body = $EmailBody  . ' ' . $hyperlink;
		$mail->WordWrap = 100;
		$mail->IsHTML(true);
		   
		//send the email       
		if(!$mail->Send()) {
		   $_SESSION["errorMessage"] = $lang_err_email . " " . $mail->ErrorInfo;
		   return false;	
		}
		return true;
	}
	
}

?>
