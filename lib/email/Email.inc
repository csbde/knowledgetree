<?php

	
/**
*
* Class Email
*
* Contains static functions concerned with sending emails
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 19 January 2003
* @package lib.email
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
	function sendHyperLink($FromEmail, $FromName, $ToEmail, $Subj, $EmailBody, $hyperlink)
	{
		global $default;
			
		// create a new phpmailer object.
		$emailHyperlink = new phpmailer();
		
		//set up info
		$emailHyperlink->isSMTP();
		$emailHyperlink->Host = $default->owl_mail_server; // SMTP server
		
		//get info from relevant fields.
		$emailHyperlink->From = $FromEmail;
		$emailHyperlink->FromName = $FromName;
		$emailHyperlink->AddAddress($ToEmail);
		$emailHyperlink->Subject = $Subj;
		$emailHyperlink->Body = stripslashes($EmailBody) . ' ' . $hyperlink;
		$emailHyperlink->WordWrap = 100;
		$emailHyperlink->IsHTML(true);
		   
		//send the email       
		if(!$emailHyperlink->Send()) {
		   $_SESSION["errorMessage"] = $lang_err_email . " " . $emailHyperlink->ErrorInfo;
		   return false;	
		}
		return true;
	}
	/**
	* Sends an email ment for administration, 
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
	function sendHelpEmail($FromEmail, $FromName, $ToEmail, $Subj, $EmailBody, $hyperlink)
	{
		global $default;
			
		// create a new phpmailer object.
		$emailHyperlink = new phpmailer();
		
		//set up info
		$emailHyperlink->isSMTP();
		$emailHyperlink->Host = $default->owl_mail_server; // SMTP server
		
		//get info from relevant fields.
		$emailHyperlink->From = $FromEmail;
		$emailHyperlink->FromName = $FromName;
		$emailHyperlink->AddAddress($ToEmail);
		$emailHyperlink->Subject = $Subj  . ' ' . $hyperlink; //only difference from above
		$emailHyperlink->Body = stripslashes($EmailBody) . " <br>This bug can be found on this page: " . "<a href = ". $hyperlink .">" . $hyperlink . "</a>";
		$emailHyperlink->WordWrap = 80;
		$emailHyperlink->IsHTML(true);
		   
		//send the email       
		if(!$emailHyperlink->Send()) {
		   $_SESSION["errorMessage"] = $lang_err_email . " " . $emailHyperlink->ErrorInfo;
		   return false;	
		}
		return true;
	}
}

?>
