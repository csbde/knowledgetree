<?php

require_once("../../../../config/dmsDefaults.php");

global $default;
require_once("$default->owl_fs_root/lib/email/Email.inc");
	
	
if(checkSession())
{


// include the page template (with navbar)
require_once("$default->owl_fs_root/presentation/webPageTemplate.inc");  	
 // when email button sent..send email
if ($submit) 
{
	//set up body and link
	
	$body = "<b> Here's a interesting link: </b>";
	$docID = $fDocumentID;
	//link has to be changed to respective server where everything is stored.
	$link = $default->owl_url . "control.php?action=viewDocument&fDocumentID=" . $docID;
	
	$hyperlink = "<a href = ". $link .">" . $link. "</a>";
	
	//create email object
	$emailLink= new Email();
	//send email
	$success = $emailLink->sendHyperLink($fromEmail,$fromName,$toEmail,$subject,$body, $hyperlink);
	
	//if successful ..rerender the page
	if($success == True)
	{
		$Center = "<br>Email Successfully Sent</br>";
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml($Center); 
		$main->setCentralPayload($oPatternCustom);
		$main->render();
	}
	Else
	{
		$Center = "<br>Email Unsuccessful</br>";
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml($Center); 
		$main->setCentralPayload($oPatternCustom);
		$main->render();
	}
	
		
	
}
//create the necessary HTML for the emailing
$Center = "
	<br>
	</br>
	<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">
	<tr>
	<td>To Email: <TD WIDTH=\"100%\"><INPUT type=\"Text\" name=\"toEmail\" size=\"30\"></td></td>
	</tr>
	<tr>
	<td>Your Name: <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"fromName\" size=\"30\"></td></td>
	</tr>
	<tr>
	<td>Your Email: <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"fromEmail\" size=\"30\"></td></td>
	</tr>
	<tr>
	<td>Subject:  <TD WIDTH=\"80%\"><INPUT type=\"Text\" name=\"subject\" size=\"30\"></td></td>
	</tr>
	<tr>
	<td>
	</td>
	</tr>
	<tr>
	<td><center><TD WIDTH=\"80%\"><INPUT type=\"Submit\" name=\"submit\" value=\"Send Email\"></center></td></td>
	</tr>
	</table>
	";
	
$oPatternCustom = & new PatternCustom();
$oPatternCustom->setHtml($Center); 
$main->setCentralPayload($oPatternCustom);
$main->setFormAction($_SERVER["PHP_SELF"]);
$main->render();




}

?>