<?php
/**
* Business logic to email link to a document
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 31 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {	
	if (isset($fDocumentID)) {	
		require_once("$default->owl_fs_root/lib/security/permission.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
		require_once("$default->owl_fs_root/lib/email/Email.inc");
		require_once("$default->owl_fs_root/lib/users/User.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/PhysicalDocumentManager.inc");
		require_once("$default->owl_fs_root/lib/documentmanagement/DocumentTransaction.inc");
		require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
		require_once("$default->owl_fs_root/presentation/Html.inc");
		require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
		require_once("emailUI.inc");
		
		//get the document to send
		$oDocument = Document::get($fDocumentID);
		
		//if the user can view the document, they can email a link to it
		if (Permission::userHasDocumentReadPermission($fDocumentID)) {			
			if (isset($fSendEmail)) {
				//if we're going to send a mail, first make sure the to address is set
				if (isset($fToEmail)) {					
					if (validateEmailAddress($fToEmail)) {						
						//if the to address is valid, send the mail
						global $default;
						$oUser = User::get($_SESSION["userID"]);						
						if (isset($fToName)) {
							$sMessage = "$fToName,\n\nYour colleauge, " . $oUser->getName() . ", wishes you to view the document entitled '" . $oDocument->getName() . "'.\n  Click on the hyperlink below to view it";
						} else {
							$sMessage = "Your colleauge, " . $oUser->getName() . ", wishes you to view the document entitled '" . $oDocument->getName() . "'.\n  Click on the hyperlink below to view it";
						}
						$sHyperlink = "http://" . $_SERVER["SERVER_NAME"] . $default->owl_root_url . "/control.php?action=viewDocument&fDocumentID=" . $fDocumentID;
						//email the hyperlink						
						Email::sendHyperlink($default->owl_email_from, "MRC DMS", $fToEmail, "Document link",  $sMessage, $sHyperlink);
						//go back to the document view page
						redirect("$default->owl_root_url/control.php?action=viewDocument&fDocumentID=$fDocumentID");
					} else {
						//ask the user to enter a valid email address
						require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
						require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
						
						
						$oPatternCustom = & new PatternCustom();
						$oPatternCustom->setHtml(getDocumentEmailPage($oDocument));
						$main->setErrorMessage("The email address you entered was invalid.  Please enter<br> " .
												"an email address of the form someone@somewhere.some postfix");
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fSendEmail=1");
						$main->setCentralPayload($oPatternCustom);
						$main->render();
					}
				}
			} else {
				//ask for an email address
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
					
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getDocumentEmailPage($oDocument));
				//$main->setErrorMessage("Please enter an email address of the form someone@somewhere.some postfix");			
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fSendEmail=1");
				$main->render();
			}
		} else {
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");				
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml("");
				$main->setErrorMessage("You do not have the permission to email a link to this document\n");
				$main->setCentralPayload($oPatternCustom);
				$main->render();
		}
	}
	
}

/** use regex to validate the format of the email address */
function validateEmailAddress($sEmailAddress){ 
	$Result = ereg ("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $sEmailAddress );
	if ($Result) {
		return TRUE;
	} else {
		return FALSE; 
	}
}

?>
