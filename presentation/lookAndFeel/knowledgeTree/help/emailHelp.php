<?php
/**
*  emailHelp.php	
*
*  Creates the Help page 
*  Creates the form and functionaility for emailing bugs to the developers
*
*
* @author Mukhtar Dharsey
* @date 22 January 2003
*/

require_once("../../../../config/dmsDefaults.php");

global $default;
require_once("$default->fileSystemRoot/lib/email/Email.inc");
	
	
if(checkSession()) {

	// include the page template (with navbar)
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");  	
	// when email button sent..send email
	if ($submit) {
		//set up body and link
		
        $hyperlink = $linkToBug;
		$body = $message . " <br>This bug can be found on this page: " . "<a href = ". $hyperlink .">". $hyperlink ."</a>";
			
		//create email object
		$emailLink= new Email();
		//send email
		$success = $emailLink->send($toEmail,$subject,$body);
		
		//if successful ..rerender the page
		if ($success)	{
			$Center = "<br>Email Successfully Sent</br>";
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml($Center); 
			$main->setCentralPayload($oPatternCustom);
			$main->render();
		} else {
			$Center = "<br>Email Unsuccessful</br>";
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml($Center); 
			$main->setCentralPayload($oPatternCustom);
			$main->render();
		}	
	} else {
		//create the necessary HTML for the emailing
		$Center = "
			<br>
			</br>
			<Center><b> Email the Development Team if you have found a Bug</b></center>
			<br>
			</br>
			<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">
			<tr>
			<td>To Email: <TD WIDTH=\"100%\"><INPUT type=\"Text\" name=\"toEmail\" size=\"30\" value = \"dmsHelp@jamwarehouse.com \"></td></td>
			</tr>
			<tr>
			<td>Your Name: <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"fromName\" size=\"30\"></td></td>
			</tr>
			<tr>
			<td>Your Email: <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"fromEmail\" size=\"30\"></td></td>
			</tr>
			<tr>
			<td>Subject:  <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"subject\" size=\"30\" value = \"Bug Found: \" ></td></td>
			</tr>
			<tr>
			<td>Link to Page:  <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"linkToBug\" size=\"60\" ></td></td>
			</tr>
			<tr>
			<td>Bug Description:  <TD WIDTH=\"80%\"><TEXTAREA NAME=\"message\" COLS=\"45\" ROWS=\"5\" WRAP=\"VIRTUAL\"></TEXTAREA></td></td>
			</td>
			</tr>
			<tr>
			
			</tr>
			</table>
			<center><INPUT type=\"Submit\" name=\"submit\" value=\"Send Email\"></center>
			";
		
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml($Center); 
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction($_SERVER["PHP_SELF"]);
			$main->render();

	}
}

?>